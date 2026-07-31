<?php

namespace App\Services;

use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductAttributeService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = ProductAttribute::query()->with(['values' => fn ($query) => $query->orderBy('sort_order')])->withCount('values');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"));
        }

        return $query->orderBy('sort_order')->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();
    }

    public function formContext(ProductAttribute $attribute): array
    {
        return [
            'attribute' => $attribute->loadMissing([
                'values' => fn ($query) => $query->orderBy('sort_order'),
                'categories',
            ]),
            'categories' => ProductCategory::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ];
    }

    public function create(array $data): ProductAttribute
    {
        return DB::transaction(function () use ($data): ProductAttribute {
            $attribute = ProductAttribute::create($this->payload($data));
            $this->syncValues($attribute, (string) ($data['values_text'] ?? ''));
            $attribute->categories()->sync($this->categoryIds($data));

            return $attribute;
        });
    }

    public function update(ProductAttribute $attribute, array $data): void
    {
        DB::transaction(function () use ($attribute, $data): void {
            $attribute->update($this->payload($data));
            $this->syncValues($attribute, (string) ($data['values_text'] ?? ''));
            $attribute->categories()->sync($this->categoryIds($data));
        });
    }

    public function delete(ProductAttribute $attribute): void
    {
        if ($attribute->values()->whereHas('products')->exists()) {
            throw ValidationException::withMessages([
                'ids' => 'Không thể xóa thuộc tính đang được gắn với sản phẩm.',
            ]);
        }

        $attribute->delete();
    }

    private function payload(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));

        return [
            'name' => $name,
            'slug' => $slug !== '' ? $slug : Str::slug($name),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'show_in_product_menu' => (bool) ($data['show_in_product_menu'] ?? false),
        ];
    }

    private function syncValues(ProductAttribute $attribute, string $valuesText): void
    {
        $values = collect(preg_split('/\R/u', $valuesText) ?: [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values();

        $slugs = [];
        foreach ($values as $index => $value) {
            $slug = Str::slug($value);
            if ($slug === '' || isset($slugs[$slug])) {
                throw ValidationException::withMessages([
                    'values_text' => $slug === '' ? 'Có giá trị không hợp lệ.' : 'Các giá trị không được trùng nhau.',
                ]);
            }
            $slugs[$slug] = true;

            $attribute->values()->updateOrCreate(
                ['slug' => $slug],
                ['value' => $value, 'sort_order' => $index],
            );
        }

        $obsoleteValues = $attribute->values()->whereNotIn('slug', array_keys($slugs));
        $obsoleteValues->whereDoesntHave('products')->delete();
    }

    private function categoryIds(array $data): array
    {
        return collect($data['category_ids'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
