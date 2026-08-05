<?php

namespace App\Services;

use App\Models\Combo;
use App\Models\ComboCategory;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComboService
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Combo::query()->with(['category', 'media', 'items']);
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(fn ($builder) => $builder->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
        }
        if (filled($filters['category'] ?? null)) {
            $query->where('combo_category_id', (int) $filters['category']);
        }
        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true)->where('status', 'active');
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where(fn ($builder) => $builder->where('is_active', false)->orWhere('status', '!=', 'active'));
        }

        return $query->orderBy('sort_order')->latest('id')->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();
    }

    public function categoriesForFilter()
    {
        return ComboCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->pluck('name', 'id');
    }

    public function formContext(Combo $combo): array
    {
        return [
            'combo' => $combo->loadMissing(['items.product.variants', 'items.variant', 'media']),
            'categories' => ComboCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'componentProducts' => Product::query()
                ->where('is_active', true)
                ->where('status', 'active')
                ->with(['activeVariants' => fn ($query) => $query->orderByDesc('is_default')->orderBy('id')])
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    public function create(array $data): Combo
    {
        return DB::transaction(function () use ($data): Combo {
            $combo = Combo::create($this->payload($data));
            $this->syncItems($combo, $data['items'] ?? []);
            $this->syncImage($combo, $data);

            return $combo;
        });
    }

    public function update(Combo $combo, array $data): void
    {
        DB::transaction(function () use ($combo, $data): void {
            $combo->update($this->payload($data, $combo));
            $this->syncItems($combo, $data['items'] ?? []);
            $this->syncImage($combo, $data);
        });
    }

    public function delete(Combo $combo): void
    {
        $combo->delete();
    }

    private function payload(array $data, ?Combo $current = null): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));

        return [
            'combo_category_id' => $this->toNullableInt($data['combo_category_id'] ?? null),
            'name' => $name,
            'slug' => $slug !== '' ? $slug : ($current?->slug ?: Str::slug($name)),
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => (float) ($data['price'] ?? 0),
            'compare_price' => filled($data['compare_price'] ?? null) ? (float) $data['compare_price'] : null,
            'status' => $data['status'] ?? 'active',
            'allow_preorder' => (bool) ($data['allow_preorder'] ?? false),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'published_at' => $data['published_at'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }

    private function syncItems(Combo $combo, array $rows): void
    {
        $combo->items()->delete();
        foreach (array_values($rows) as $index => $row) {
            $combo->items()->create([
                'product_id' => (int) $row['product_id'],
                'product_variant_id' => $this->toNullableInt($row['product_variant_id'] ?? null),
                'quantity' => (int) $row['quantity'],
                'sort_order' => (int) ($row['sort_order'] ?? $index),
            ]);
        }
    }

    private function syncImage(Combo $combo, array $data): void
    {
        $removedIds = collect(json_decode((string) ($data['image_removed_ids'] ?? '[]'), true) ?: [])
            ->map(fn ($id): int => (int) $id)
            ->filter();
        $existingIds = $combo->getMedia('combo_images')->pluck('id');
        if ((bool) ($data['image_remove'] ?? false) || $existingIds->intersect($removedIds)->isNotEmpty()) {
            $combo->clearMediaCollection('combo_images');
            return;
        }

        $temporaryPath = collect(explode('|', (string) ($data['image'] ?? '')))->map(fn ($path) => trim($path))->filter()->first();
        if ($temporaryPath === null) {
            return;
        }

        $combo->clearMediaCollection('combo_images');
        $this->mediaService->syncSingle($combo, 'combo_images', $temporaryPath);
    }

    private function toNullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
