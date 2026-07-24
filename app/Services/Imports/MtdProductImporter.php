<?php

namespace App\Services\Imports;

use App\Models\Brand;
use App\Models\MtdProductSource;
use App\Models\MtdVariantSource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MtdProductImporter
{
    public function __construct(private readonly MtdHtmlSanitizer $sanitizer) {}

    public function import(string $path, array $options = []): array
    {
        $path = realpath($path) ?: $path;
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException("Không đọc được file dữ liệu MTD: {$path}");
        }

        $products = json_decode((string) file_get_contents($path), true);
        if (! is_array($products)) {
            throw new RuntimeException('File products.json không chứa JSON hợp lệ.');
        }

        $only = collect(explode(',', (string) ($options['only'] ?? '')))
            ->map(fn ($slug) => trim($slug))
            ->filter()
            ->values();
        if ($only->isNotEmpty()) {
            $products = array_values(array_filter(
                $products,
                fn ($product) => is_array($product) && $only->contains((string) ($product['slug'] ?? '')),
            ));
        }

        $limit = max(0, (int) ($options['limit'] ?? 0));
        if ($limit > 0) {
            $products = array_slice($products, 0, $limit);
        }

        $report = [
            'total' => count($products),
            'eligible' => 0,
            'created' => 0,
            'updated' => 0,
            'adopted' => 0,
            'conflicts' => 0,
            'invalid_pages' => 0,
            'missing_price' => 0,
            'images_added' => 0,
            'images_replaced' => 0,
            'images_removed' => 0,
            'errors' => 0,
            'warnings' => [],
        ];

        foreach ($products as $payload) {
            if (! is_array($payload) || $this->isInvalidCollectionPage($payload)) {
                $report['invalid_pages']++;

                continue;
            }

            $slug = trim((string) ($payload['slug'] ?? ''));
            if ($slug === '' || ! $this->hasUsablePrice($payload)) {
                $report['missing_price']++;
                $this->warn($report, ($slug ?: '(không có slug)').': thiếu giá hợp lệ.');

                continue;
            }

            $report['eligible']++;

            if ((bool) ($options['dry_run'] ?? false)) {
                $this->analyze($payload, $options, $report);

                continue;
            }

            if (
                ! (bool) ($options['adopt_existing'] ?? false)
                && ! MtdProductSource::query()->where('external_id', $slug)->exists()
                && Product::withTrashed()->where('slug', $slug)->exists()
            ) {
                $report['conflicts']++;
                $this->warn($report, "{$slug}: đã có sản phẩm cùng slug; cần --adopt-existing.");

                continue;
            }

            try {
                [$product, $adopted] = DB::transaction(function () use ($payload, $options, &$report) {
                    return $this->persistProduct($payload, $options, $report);
                });

                if ((bool) ($options['with_images'] ?? false) && ! $adopted) {
                    $this->syncImages($product, $payload, $path, $report);
                }
            } catch (Throwable $exception) {
                $report['errors']++;
                $this->warn($report, "{$slug}: {$exception->getMessage()}");
            }
        }

        return $report;
    }

    private function analyze(array $payload, array $options, array &$report): void
    {
        $slug = (string) $payload['slug'];
        $source = MtdProductSource::query()->where('external_id', $slug)->first();
        if ($source) {
            $report['updated']++;

            return;
        }

        $existing = Product::withTrashed()->where('slug', $slug)->first();
        if ($existing) {
            if ((bool) ($options['adopt_existing'] ?? false)) {
                $report['adopted']++;
            } else {
                $report['conflicts']++;
                $this->warn($report, "{$slug}: đã có sản phẩm cùng slug; cần --adopt-existing.");
            }

            return;
        }

        $report['created']++;
    }

    private function persistProduct(array $payload, array $options, array &$report): array
    {
        $externalId = (string) $payload['slug'];
        $source = MtdProductSource::query()->with('product')->where('external_id', $externalId)->first();
        $adopted = false;
        $created = false;

        if ($source) {
            $product = $source->product;
            if (! $product || $product->trashed()) {
                throw new RuntimeException('Sản phẩm đã liên kết nguồn không còn hoạt động.');
            }
            $adopted = (bool) $source->is_adopted;
            $report['updated']++;
        } else {
            $product = Product::withTrashed()->where('slug', $externalId)->first();
            if ($product) {
                if (! (bool) ($options['adopt_existing'] ?? false)) {
                    $report['conflicts']++;
                    throw new RuntimeException('Đã có sản phẩm cùng slug; cần --adopt-existing.');
                }
                if ($product->trashed()) {
                    throw new RuntimeException('Slug đang thuộc một sản phẩm đã xóa mềm.');
                }
                $adopted = true;
                $report['adopted']++;
            } else {
                $product = Product::create($this->newProductPayload($payload));
                $created = true;
                $report['created']++;
            }

            $source = new MtdProductSource([
                'external_id' => $externalId,
                'product_id' => $product->id,
                'is_adopted' => $adopted,
            ]);
        }

        if (! $created && ! $adopted && (bool) ($options['refresh_content'] ?? false)) {
            $product->update($this->refreshableProductPayload($payload));
        }

        $source->fill([
            'product_id' => $product->id,
            'source_url' => $payload['canonical_url'] ?? $payload['source_url'] ?? null,
            'payload_hash' => $this->payloadHash($payload),
            'source_stock_status' => $payload['stock_status'] ?? null,
            'scraped_at' => $this->dateOrNull($payload['scraped_at'] ?? null),
            'last_synced_at' => now(),
        ])->save();

        $this->syncVariants($product, $source, $payload, $adopted, $report);

        return [$product, (bool) $source->is_adopted];
    }

    private function newProductPayload(array $payload): array
    {
        $description = $this->sanitizer->sanitize($payload['description_html'] ?? null)
            ?: '<p>Thông tin sản phẩm đang được cập nhật.</p>';

        return [
            'product_category_id' => $this->resolveCategory($payload)->id,
            'brand_id' => $this->resolveBrand($payload['brand'] ?? null)?->id,
            'name' => trim((string) $payload['name']),
            'slug' => (string) $payload['slug'],
            'summary' => $this->nullIfBlank($payload['meta_description'] ?? null),
            'description' => $description,
            'status' => 'draft',
            'variant_selection_mode' => count((array) ($payload['variants'] ?? [])) > 1 ? 'options' : 'combination',
            'track_inventory' => true,
            'allow_preorder' => false,
            'is_featured' => false,
            'is_home' => false,
            'is_active' => false,
            'seo_title' => trim((string) $payload['name']),
            'seo_description' => $this->nullIfBlank($payload['meta_description'] ?? null),
            'published_at' => null,
        ];
    }

    private function refreshableProductPayload(array $payload): array
    {
        return [
            'name' => trim((string) $payload['name']),
            'summary' => $this->nullIfBlank($payload['meta_description'] ?? null),
            'description' => $this->sanitizer->sanitize($payload['description_html'] ?? null)
                ?: '<p>Thông tin sản phẩm đang được cập nhật.</p>',
            'seo_description' => $this->nullIfBlank($payload['meta_description'] ?? null),
        ];
    }

    private function syncVariants(
        Product $product,
        MtdProductSource $source,
        array $payload,
        bool $adopted,
        array &$report,
    ): void {
        $variants = array_values(array_filter((array) ($payload['variants'] ?? []), 'is_array'));
        if ($variants === []) {
            $variants = [[
                'name' => 'Default Title',
                'sku' => $payload['sku'] ?? null,
                'price' => $payload['price'] ?? null,
                'compare_at_price' => $payload['compare_at_price'] ?? null,
                'available' => ($payload['stock_status'] ?? null) !== 'out_of_stock',
                'is_default' => true,
            ]];
        }

        $adoptionCandidates = $adopted
            ? $product->variants()->whereDoesntHave('mtdSource')->orderBy('id')->get()->values()
            : collect();
        $defaultVariantId = null;

        foreach ($variants as $index => $variantPayload) {
            $externalId = $this->variantExternalId((string) $payload['slug'], $variantPayload, $index);
            $variantSource = MtdVariantSource::query()
                ->where('mtd_product_source_id', $source->id)
                ->where('external_id', $externalId)
                ->first();

            if ($variantSource) {
                $variant = $variantSource->variant;
                if (! $variant) {
                    throw new RuntimeException("Biến thể nguồn {$externalId} bị mất liên kết.");
                }
                $this->updateImportedVariant($variant, $variantPayload, $payload);
            } elseif ($adopted) {
                $variant = $adoptionCandidates->get($index);
                if (! $variant) {
                    $this->warn($report, "{$payload['slug']}: không tìm thấy biến thể cũ để liên kết {$externalId}.");

                    continue;
                }
            } else {
                $variant = $product->variants()->create(
                    $this->newVariantPayload($variantPayload, $payload, $index),
                );
            }

            $sourceSku = $variantPayload['sku']
                ?? (count($variants) === 1 ? ($payload['sku'] ?? null) : null);

            MtdVariantSource::query()->updateOrCreate(
                [
                    'mtd_product_source_id' => $source->id,
                    'external_id' => $externalId,
                ],
                [
                    'product_variant_id' => $variant->id,
                    'source_sku' => $this->nullIfBlank($sourceSku),
                    'source_available' => array_key_exists('available', $variantPayload)
                        ? (bool) $variantPayload['available']
                        : null,
                    'payload_hash' => $this->payloadHash($variantPayload),
                    'last_synced_at' => now(),
                ],
            );

            if ((bool) ($variantPayload['is_default'] ?? false) || $defaultVariantId === null) {
                $defaultVariantId = $variant->id;
            }
        }

        if (! $adopted && $defaultVariantId !== null) {
            $product->variants()->update(['is_default' => false]);
            $product->variants()->whereKey($defaultVariantId)->update(['is_default' => true]);
        }
    }

    private function newVariantPayload(array $variant, array $product, int $index): array
    {
        $sourceSku = $variant['sku']
            ?? (count((array) ($product['variants'] ?? [])) <= 1 ? ($product['sku'] ?? null) : null);
        [$sku, $barcode] = $this->identifiers($sourceSku);
        $price = (float) ($variant['price'] ?? $product['price'] ?? 0);
        $compare = (float) ($variant['compare_at_price'] ?? $product['compare_at_price'] ?? 0);

        return [
            'name' => $this->variantName($variant['name'] ?? null),
            'sku' => $this->uniqueSku($sku),
            'barcode' => $barcode,
            'price' => $price,
            'compare_price' => $compare > $price ? $compare : null,
            'stock' => 0,
            'weight' => null,
            'is_default' => (bool) ($variant['is_default'] ?? $index === 0),
            'is_active' => true,
        ];
    }

    private function updateImportedVariant(ProductVariant $variant, array $source, array $product): void
    {
        $price = (float) ($source['price'] ?? $product['price'] ?? 0);
        $compare = (float) ($source['compare_at_price'] ?? $product['compare_at_price'] ?? 0);
        $sourceSku = $source['sku'] ?? null;
        [$sku, $barcode] = $this->identifiers($sourceSku);

        $variant->fill([
            'name' => $this->variantName($source['name'] ?? null),
            'price' => $price,
            'compare_price' => $compare > $price ? $compare : null,
        ]);
        if (! $variant->sku && $sku) {
            $variant->sku = $this->uniqueSku($sku, $variant->id);
        }
        if (! $variant->barcode && $barcode) {
            $variant->barcode = $barcode;
        }
        $variant->save();
    }

    private function syncImages(Product $product, array $payload, string $jsonPath, array &$report): void
    {
        $relativePaths = collect((array) ($payload['local_images'] ?? []))
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->take((int) config('mtd_import.max_images', 12))
            ->values();
        $sourceUrls = array_values((array) ($payload['images'] ?? []));
        $crawlerRoot = realpath(dirname(dirname($jsonPath)));
        if ($crawlerRoot === false) {
            throw new RuntimeException('Không xác định được thư mục gốc crawler để nhập ảnh.');
        }

        $existing = $product->getMedia('product_images')
            ->filter(fn ($media) => $media->getCustomProperty('import_source') === 'mtd')
            ->keyBy(fn ($media) => (string) $media->getCustomProperty('source_path'));
        $seen = [];

        foreach ($relativePaths as $index => $relativePath) {
            $relativePath = str_replace('\\', '/', trim($relativePath));
            $fullPath = realpath($crawlerRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
            if ($fullPath === false || ! is_file($fullPath) || ! $this->isInside($fullPath, $crawlerRoot)) {
                $this->warn($report, "{$payload['slug']}: thiếu hoặc sai đường dẫn ảnh {$relativePath}.");

                continue;
            }

            $hash = hash_file('sha256', $fullPath);
            $seen[] = $relativePath;
            $current = $existing->get($relativePath);
            if ($current && hash_equals((string) $current->getCustomProperty('source_hash'), $hash)) {
                continue;
            }

            if ($current) {
                $current->delete();
                $report['images_replaced']++;
            }

            $product->addMedia($fullPath)
                ->preservingOriginal()
                ->withCustomProperties([
                    'import_source' => 'mtd',
                    'source_path' => $relativePath,
                    'source_url' => $sourceUrls[$index] ?? null,
                    'source_hash' => $hash,
                ])
                ->toMediaCollection('product_images', 'public_media');
            $report['images_added']++;
        }

        foreach ($existing as $sourcePath => $media) {
            if (! in_array($sourcePath, $seen, true)) {
                $media->delete();
                $report['images_removed']++;
            }
        }
    }

    private function resolveCategory(array $payload): ProductCategory
    {
        $type = Str::slug((string) ($payload['product_type'] ?? ''));
        $name = Str::lower((string) ($payload['name'] ?? ''));
        $target = config("mtd_import.category_map.{$type}");

        if ($type === 'dau-goi-sua-tam' && str_contains($name, 'sữa tắm')) {
            $target = 'sua-tam';
        }
        if ($type === 'tdc' && str_contains($name, 'mặt')) {
            $target = 'cham-soc-mat';
        }

        $target = $target ?: config('mtd_import.fallback_category', 'khac');

        return ProductCategory::query()->where('slug', $target)->first()
            ?: ProductCategory::query()->where('slug', config('mtd_import.fallback_category', 'khac'))->firstOrFail();
    }

    private function resolveBrand(mixed $rawBrand): ?Brand
    {
        $name = trim((string) $rawBrand);
        if ($name === '') {
            return null;
        }

        $aliasKey = Str::slug($name);
        $name = (string) (config("mtd_import.brand_aliases.{$aliasKey}") ?: $name);
        $slug = Str::slug($name);

        return Brand::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'sort_order' => 0, 'is_featured' => false, 'is_home' => false, 'is_active' => true],
        );
    }

    private function isInvalidCollectionPage(array $payload): bool
    {
        return blank($payload['brand'] ?? null)
            && blank($payload['sku'] ?? null)
            && blank($payload['price'] ?? null)
            && empty(array_filter((array) ($payload['local_images'] ?? [])));
    }

    private function hasUsablePrice(array $payload): bool
    {
        if ((float) ($payload['price'] ?? 0) > 0) {
            return true;
        }

        foreach ((array) ($payload['variants'] ?? []) as $variant) {
            if (is_array($variant) && (float) ($variant['price'] ?? 0) > 0) {
                return true;
            }
        }

        return false;
    }

    private function identifiers(mixed $sourceSku): array
    {
        $value = trim((string) $sourceSku);
        if ($value === '' || Str::contains(Str::lower($value), ['đang cập nhật', 'dang cap nhat'])) {
            return [null, null];
        }

        if (preg_match('/^\d{8,14}$/', $value)) {
            return [null, $value];
        }

        return [mb_substr($value, 0, 100), null];
    }

    private function uniqueSku(?string $sku, ?int $exceptId = null): ?string
    {
        if ($sku === null) {
            return null;
        }

        $query = ProductVariant::query()->where('sku', $sku);
        if ($exceptId) {
            $query->whereKeyNot($exceptId);
        }

        return $query->exists() ? null : $sku;
    }

    private function variantExternalId(string $productSlug, array $variant, int $index): string
    {
        $sourceId = trim((string) ($variant['source_variant_id'] ?? ''));
        if ($sourceId !== '') {
            return mb_substr($sourceId, 0, 255);
        }

        return 'fallback-'.hash('sha256', $productSlug.'|'.$index.'|'.($variant['name'] ?? '').'|'.($variant['sku'] ?? ''));
    }

    private function variantName(mixed $name): string
    {
        $name = trim((string) $name);

        return $name === '' || strcasecmp($name, 'Default Title') === 0 ? 'Mặc định' : mb_substr($name, 0, 255);
    }

    private function payloadHash(array $payload): string
    {
        return hash('sha256', (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function dateOrNull(mixed $value): ?Carbon
    {
        try {
            return filled($value) ? Carbon::parse((string) $value) : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function nullIfBlank(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function isInside(string $path, string $root): bool
    {
        $path = strtolower(str_replace('\\', '/', $path));
        $root = rtrim(strtolower(str_replace('\\', '/', $root)), '/').'/';

        return str_starts_with($path, $root);
    }

    private function warn(array &$report, string $message): void
    {
        if (count($report['warnings']) < 100) {
            $report['warnings'][] = $message;
        }
    }
}
