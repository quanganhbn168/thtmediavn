<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Product::query()->with([
            'category',
            'brand',
            'media',
            'variants' => fn ($query) => $query->where('is_active', true)->orderByDesc('is_default')->orderBy('id'),
        ])->withCount('variants');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        if (($filters['category'] ?? null) !== null && (int) $filters['category'] > 0) {
            $query->where('product_category_id', (int) $filters['category']);
        }

        if (($filters['brand'] ?? null) !== null && (int) $filters['brand'] > 0) {
            $query->where('brand_id', (int) $filters['brand']);
        }

        if (($filters['status'] ?? null) !== null) {
            $query->where('status', (string) $filters['status']);
        }

        $perPage = (int) ($filters['per_page'] ?? 20);
        if (! in_array($perPage, [10, 20, 25, 50], true)) {
            $perPage = 20;
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function categoriesForFilter(): Collection
    {
        return ProductCategory::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id');
    }

    public function brandsForFilter(): Collection
    {
        return Brand::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id');
    }

    public function editorContext(Product $product): array
    {
        $categoryModels = ProductCategory::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'parent_id', 'name', 'is_active', 'sort_order']);
        $activeCategoryModels = $categoryModels->where('is_active', true)->values();

        return [
            'product' => $product->loadMissing('attributeValues'),
            'categories' => $categoryModels,
            'categoryFilterScopes' => $this->categoryFilterScopes($activeCategoryModels),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'),
            'options' => ProductOption::query()->with('values')->where('is_active', true)->orderBy('sort_order')->get(),
            'filterAttributes' => ProductAttribute::query()
                ->with(['values' => fn ($query) => $query->orderBy('sort_order'), 'categories:id'])
                ->where('is_active', true)
                ->whereHas('values')
                ->orderBy('sort_order')
                ->get(),
        ];
    }

    private function categoryFilterScopes(Collection $categories): array
    {
        $byId = $categories->keyBy('id');

        return $categories->mapWithKeys(function (ProductCategory $category) use ($byId): array {
            $ids = [(int) $category->id];
            $parentId = $category->parent_id;
            while ($parentId && $byId->has($parentId)) {
                $ids[] = (int) $parentId;
                $parentId = $byId->get($parentId)->parent_id;
            }

            return [(int) $category->id => $ids];
        })->all();
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $product = Product::create($this->toPayload($data));
            $this->syncDetails($product, $data);

            return $product;
        });
    }

    public function update(Product $product, array $data): void
    {
        DB::transaction(function () use ($product, $data): void {
            $product->update($this->toPayload($data));
            $this->syncDetails($product, $data);
        });
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    private function toPayload(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));

        return [
            'product_category_id' => (int) ($data['product_category_id'] ?? 0),
            'brand_id' => $this->toNullableInt($data['brand_id'] ?? null),
            'name' => $name,
            'slug' => $slug !== '' ? $slug : Str::slug($name),
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'] ?? null,
            'ingredients' => $data['ingredients'] ?? null,
            'usage' => $data['usage'] ?? null,
            'product_notes' => $data['product_notes'] ?? null,
            'status' => $data['status'] ?? 'active',
            'variant_selection_mode' => $data['variant_selection_mode'] ?? 'combination',
            'track_inventory' => (bool) ($data['track_inventory'] ?? false),
            'allow_preorder' => (bool) ($data['allow_preorder'] ?? false),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_home' => (bool) ($data['is_home'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'published_at' => $data['published_at'] ?? null,
        ];
    }

    private function syncDetails(Product $product, array $data): void
    {
        $optionIds = array_slice($this->filterOptionIds((array) ($data['option_ids'] ?? [])), 0, 3);
        $product->options()->sync($optionIds);
        $product->attributeValues()->sync($this->filterOptionIds((array) ($data['attribute_value_ids'] ?? [])));

        $keptVariantIds = [];
        $requestedVariants = (array) ($data['variants'] ?? []);
        $manualVariants = $this->extractManualVariants($requestedVariants);

        if ($manualVariants === []) {
            if ($optionIds === []) {
                $manualVariants = [['name' => 'Mặc định']];
            } else {
                $this->syncAutoVariants($product, $optionIds, $keptVariantIds, $requestedVariants);
            }
        }

        if ($manualVariants !== []) {
            $this->syncManualVariants($product, $manualVariants, $keptVariantIds);
        } else {
            $this->syncAutoVariants($product, $optionIds, $keptVariantIds, $requestedVariants);
        }

        $this->ensureOneDefaultVariant($product, $keptVariantIds);
        $product->variants()
            ->whereNotIn('id', $keptVariantIds)
            ->where(function ($query): void {
                $query->whereHas('orderItems')
                    ->orWhereHas('comboItems')
                    ->orWhereHas('orderItemComboComponents');
            })
            ->update(['is_active' => false, 'is_default' => false]);
        $product->variants()
            ->whereNotIn('id', $keptVariantIds)
            ->whereDoesntHave('orderItems')
            ->whereDoesntHave('comboItems')
            ->whereDoesntHave('orderItemComboComponents')
            ->delete();

        $this->syncImages(
            $product,
            $data['image'] ?? null,
            (bool) ($data['image_remove'] ?? false),
            $data['image_order'] ?? null,
            $data['image_removed_ids'] ?? null,
        );
    }

    private function syncImages(
        Product $product,
        mixed $imageInput,
        bool $removeExisting = false,
        mixed $orderInput = null,
        mixed $removedIdsInput = null,
    ): void {
        if ($removeExisting) {
            $product->clearMediaCollection('product_images');
        } else {
            $removedIds = collect($this->decodeJsonArray($removedIdsInput))
                ->map(static fn ($id): int => (int) $id)
                ->filter()
                ->unique();

            $product->getMedia('product_images')
                ->whereIn('id', $removedIds)
                ->each->delete();
        }

        $product->unsetRelation('media');
        $currentCount = $product->getMedia('product_images')->count();
        $images = array_slice($this->normalizeImageInputs($imageInput), 0, max(0, 9 - $currentCount));
        $newMediaByPath = [];

        foreach ($images as $image) {
            $media = $this->mediaService->syncSingle($product, 'product_images', $image);
            if ($media) {
                $newMediaByPath[$image] = $media->id;
            }
        }

        $product->unsetRelation('media');
        $this->applyImageOrder($product, $orderInput, $newMediaByPath);
    }

    private function applyImageOrder(Product $product, mixed $orderInput, array $newMediaByPath): void
    {
        $media = $product->getMedia('product_images')->keyBy('id');
        $orderedIds = [];

        foreach ($this->decodeJsonArray($orderInput) as $key) {
            $key = (string) $key;
            if (str_starts_with($key, 'existing:')) {
                $id = (int) substr($key, strlen('existing:'));
            } elseif (str_starts_with($key, 'temporary:')) {
                $path = substr($key, strlen('temporary:'));
                $id = (int) ($newMediaByPath[$path] ?? 0);
            } else {
                continue;
            }

            if ($id > 0 && $media->has($id) && ! in_array($id, $orderedIds, true)) {
                $orderedIds[] = $id;
            }
        }

        foreach ($media->keys() as $id) {
            $id = (int) $id;
            if (! in_array($id, $orderedIds, true)) {
                $orderedIds[] = $id;
            }
        }

        foreach ($orderedIds as $index => $id) {
            $media->get($id)?->update(['order_column' => $index + 1]);
        }

        $product->unsetRelation('media');
    }

    private function decodeJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeImageInputs(mixed $imageInput): array
    {
        if (is_array($imageInput)) {
            $items = $imageInput;
        } elseif (is_string($imageInput) && str_contains($imageInput, '|')) {
            $items = explode('|', $imageInput);
        } elseif (is_string($imageInput)) {
            $items = [$imageInput];
        } else {
            return [];
        }

        $items = array_values(array_filter(array_map(static function ($value): ?string {
            $value = trim((string) $value);

            return $value === '' ? null : $value;
        }, $items), static fn (?string $value): bool => ! blank($value)));

        return array_values(array_unique($items));
    }

    private function filterOptionIds(array $optionIds): array
    {
        $ids = array_map('intval', $optionIds);

        return array_values(array_filter(array_unique($ids), static fn (int $id): bool => $id > 0));
    }

    private function extractManualVariants(array $variants): array
    {
        $manual = [];
        foreach ($variants as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (! $this->isManualVariantRow($row)) {
                continue;
            }
            $manual[] = $row;
        }

        return $manual;
    }

    private function isManualVariantRow(array $row): bool
    {
        if (isset($row['id']) && (string) $row['id'] !== '') {
            return true;
        }

        if (! empty($this->extractValueIds($row['value_ids'] ?? []))) {
            return true;
        }

        foreach (['name', 'sku', 'barcode', 'price', 'compare_price', 'stock', 'weight'] as $field) {
            if ((string) ($row[$field] ?? '') !== '') {
                return true;
            }
        }

        return false;
    }

    private function syncManualVariants(Product $product, array $rows, array &$keptVariantIds): void
    {
        $baseSku = $this->buildAutoVariantSkuBase($product);
        foreach ($rows as $index => $row) {
            $valueIds = $this->extractValueIds($row['value_ids'] ?? []);
            $sku = $this->nullIfBlank((string) ($row['sku'] ?? null));
            if ($sku === null) {
                $sku = $this->buildAutoVariantSku($baseSku, $valueIds, $index);
            }

            $variant = $product->variants()->updateOrCreate(
                ['id' => $this->toNullableInt($row['id'] ?? null)],
                [
                    'name' => $this->nullIfBlank((string) ($row['name'] ?? null)),
                    'sku' => $sku,
                    'barcode' => $this->nullIfBlank((string) ($row['barcode'] ?? null)),
                    'price' => $this->toNullableDecimal($row['price'] ?? null),
                    'compare_price' => $this->toNullableDecimal($row['compare_price'] ?? null),
                    'stock' => (int) ($this->toNullableInt($row['stock'] ?? null) ?? 0),
                    'weight' => $this->toNullableInt($row['weight'] ?? null),
                    'is_default' => (bool) ($row['is_default'] ?? false),
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ]
            );
            $variant->values()->sync($valueIds);
            $keptVariantIds[] = $variant->id;
        }
    }

    private function syncAutoVariants(Product $product, array $optionIds, array &$keptVariantIds, array $requestedVariants = []): void
    {
        $groups = $this->getOptionValueGroups($optionIds);
        $combinations = $this->buildCombinations($groups);
        if ($combinations === []) {
            $combinations = [[]];
        }

        $seed = $this->extractAutoVariantSeed($requestedVariants);
        $existingBySignature = $this->getExistingVariantSignatures($product);
        $valueLabelById = $this->getValueLabelById($optionIds);
        $baseSku = $this->buildAutoVariantSkuBase($product, $seed['sku']);
        $basePrice = $seed['price'];
        $baseComparePrice = $seed['compare_price'];
        $baseStock = $seed['stock'];
        $baseWeight = $seed['weight'];
        $baseName = $seed['name'];

        foreach ($combinations as $index => $valueIds) {
            $valueIds = $this->extractValueIds($valueIds);
            $signature = $this->variantSignature($valueIds);

            $payload = [
                'name' => $valueIds === [] ? $baseName : $this->buildAutoVariantName($valueIds, $valueLabelById),
                'sku' => $this->buildAutoVariantSku($baseSku, $valueIds, $index),
                'barcode' => null,
                'price' => $basePrice,
                'compare_price' => $baseComparePrice,
                'stock' => $baseStock,
                'weight' => $baseWeight,
                'is_default' => $index === 0,
                'is_active' => true,
            ];

            if (isset($existingBySignature[$signature])) {
                $variant = $existingBySignature[$signature];
                $variant->update($payload);
                unset($existingBySignature[$signature]);
            } else {
                $variant = $product->variants()->create($payload);
            }

            $variant->values()->sync($valueIds);
            $keptVariantIds[] = $variant->id;
        }
    }

    private function getOptionValueGroups(array $optionIds): array
    {
        if ($optionIds === []) {
            return [];
        }

        $positions = array_flip($optionIds);
        $options = ProductOption::with('values')
            ->whereIn('id', $optionIds)
            ->get()
            ->sortBy(fn (ProductOption $option): int => $positions[$option->id] ?? PHP_INT_MAX)
            ->values();

        $groups = [];
        foreach ($options as $option) {
            $valueIds = array_values($option->values->pluck('id')->filter(static fn ($id) => (int) $id > 0)->all());
            if ($valueIds === []) {
                continue;
            }
            $groups[] = $valueIds;
        }

        return $groups;
    }

    private function getValueLabelById(array $optionIds): array
    {
        if ($optionIds === []) {
            return [];
        }

        return ProductOptionValue::query()
            ->whereIn('product_option_id', $optionIds)
            ->pluck('value', 'id')
            ->all();
    }

    private function getExistingVariantSignatures(Product $product): array
    {
        $existing = [];
        $product->loadMissing('variants.values');
        foreach ($product->variants as $variant) {
            $existing[$this->variantSignature($variant->values->pluck('id')->all())] = $variant;
        }

        return $existing;
    }

    private function buildCombinations(array $optionValueGroups): array
    {
        if ($optionValueGroups === []) {
            return [[]];
        }

        $result = [[]];
        foreach ($optionValueGroups as $group) {
            $next = [];
            foreach ($result as $current) {
                foreach ($group as $valueId) {
                    $next[] = array_merge($current, [$valueId]);
                }
            }
            $result = $next;
        }

        return $result;
    }

    private function buildAutoVariantName(array $valueIds, array $valueLabelById): string
    {
        $labels = [];
        foreach ($valueIds as $valueId) {
            if (isset($valueLabelById[$valueId])) {
                $labels[] = $valueLabelById[$valueId];
            }
        }

        return $labels === [] ? 'Mặc định' : implode(' / ', $labels);
    }

    private function extractAutoVariantSeed(array $requestedVariants): array
    {
        $default = [
            'name' => 'Mặc định',
            'sku' => null,
            'price' => 0,
            'compare_price' => null,
            'stock' => 0,
            'weight' => null,
        ];

        foreach ($requestedVariants as $row) {
            if (! is_array($row)) {
                continue;
            }

            if (! array_key_exists('name', $row) && ! array_key_exists('sku', $row) && ! array_key_exists('price', $row)
                && ! array_key_exists('compare_price', $row) && ! array_key_exists('stock', $row) && ! array_key_exists('weight', $row)) {
                continue;
            }

            $default['name'] = $this->nullIfBlank((string) ($row['name'] ?? null)) ?: $default['name'];
            $default['sku'] = $this->nullIfBlank((string) ($row['sku'] ?? null)) ?: null;
            $default['price'] = $this->toNullableDecimal($row['price'] ?? null) ?? 0;
            $default['compare_price'] = $this->toNullableDecimal($row['compare_price'] ?? null);
            $default['stock'] = (int) ($this->toNullableInt($row['stock'] ?? null) ?? 0);
            $default['weight'] = $this->toNullableInt($row['weight'] ?? null);
            break;
        }

        return $default;
    }

    private function variantSignature(array $valueIds): string
    {
        if ($valueIds === []) {
            return '';
        }

        $valueIds = array_map('intval', $valueIds);
        sort($valueIds, SORT_NUMERIC);
        $valueIds = array_values(array_unique($valueIds));

        return implode('-', $valueIds);
    }

    private function buildAutoVariantSkuBase(Product $product, ?string $seedSku = null): string
    {
        $base = trim((string) ($seedSku ?: $product->slug ?: $product->name));
        $base = Str::upper(Str::substr(Str::slug($base, '-'), 0, 36));
        if ($base === '') {
            $base = 'SP-'.$product->id;
        }

        return $base;
    }

    private function buildAutoVariantSku(string $baseSku, array $valueIds, int $index): string
    {
        $suffix = $valueIds === [] ? "DEFAULT-{$index}" : implode('-', array_map('strval', $valueIds));
        $sku = $baseSku.'-'.$suffix;
        if (strlen($sku) > 100) {
            return $baseSku.'-'.strtoupper(Str::substr(sha1($sku), 0, 8));
        }

        return $sku;
    }

    private function ensureOneDefaultVariant(Product $product, array $keptVariantIds): void
    {
        if ($keptVariantIds === []) {
            return;
        }

        $defaultId = $product->variants()
            ->whereIn('id', $keptVariantIds)
            ->where('is_default', true)
            ->orderBy('id')
            ->value('id');

        if (! $defaultId) {
            $defaultId = $keptVariantIds[0];
        }

        $product->variants()->whereIn('id', $keptVariantIds)->update(['is_default' => false]);
        $product->variants()->whereKey($defaultId)->update(['is_default' => true]);
    }

    private function extractValueIds(array $raw): array
    {
        $ids = array_map('intval', $raw);

        return array_values(array_unique(array_filter($ids, static fn (int $id): bool => $id > 0)));
    }

    private function nullIfBlank(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function toNullableDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric((string) $value)) {
            return null;
        }

        return (float) $value;
    }
}
