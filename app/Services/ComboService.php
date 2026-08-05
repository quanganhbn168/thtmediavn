<?php

namespace App\Services;

use App\Models\Combo;
use App\Models\ComboCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComboService
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Combo::query()->with(['category', 'media'])->withCount('items');
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

    public function editorContext(Combo $combo): array
    {
        return [
            'combo' => $combo->loadMissing(['items.product.variants', 'items.variant', 'media']),
            'categories' => ComboCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ];
    }

    public function create(array $data): Combo
    {
        return DB::transaction(function () use ($data): Combo {
            $combo = Combo::create($this->payload($data));
            $this->syncImage($combo, $data);

            return $combo;
        });
    }

    public function update(Combo $combo, array $data): void
    {
        DB::transaction(function () use ($combo, $data): void {
            $combo->update($this->payload($data, $combo));
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
            'ingredients' => $data['ingredients'] ?? null,
            'usage' => $data['usage'] ?? null,
            'product_notes' => $data['product_notes'] ?? null,
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

    private function syncImage(Combo $combo, array $data): void
    {
        $removedIds = collect($this->decodeJsonArray($data['image_removed_ids'] ?? null))
            ->map(fn ($id): int => (int) $id)
            ->filter();

        if ((bool) ($data['image_remove'] ?? false)) {
            $combo->clearMediaCollection('combo_images');
        }

        $combo->getMedia('combo_images')
            ->whereIn('id', $removedIds)
            ->each->delete();

        $combo->unsetRelation('media');
        $temporaryPaths = collect(explode('|', (string) ($data['image'] ?? '')))
            ->map(fn ($path): string => trim($path))
            ->filter()
            ->unique()
            ->values();
        $newMediaByPath = [];

        foreach ($temporaryPaths as $temporaryPath) {
            $media = $this->mediaService->syncSingle($combo, 'combo_images', $temporaryPath);
            if ($media) {
                $newMediaByPath[$temporaryPath] = $media->id;
            }
        }

        $combo->unsetRelation('media');
        $media = $combo->getMedia('combo_images')->keyBy('id');
        $orderedIds = [];

        foreach ($this->decodeJsonArray($data['image_order'] ?? null) as $key) {
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

    private function toNullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
