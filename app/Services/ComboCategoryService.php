<?php

namespace App\Services;

use App\Models\ComboCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ComboCategoryService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = ComboCategory::query()->withCount('combos');
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(fn ($builder) => $builder->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
        }
        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->orderBy('sort_order')->orderBy('name')->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();
    }

    public function create(array $data): ComboCategory
    {
        return ComboCategory::create($this->payload($data));
    }

    public function update(ComboCategory $category, array $data): void
    {
        $category->update($this->payload($data, $category));
    }

    public function delete(ComboCategory $category): void
    {
        $category->delete();
    }

    private function payload(array $data, ?ComboCategory $current = null): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));

        return [
            'name' => $name,
            'slug' => $slug !== '' ? $slug : ($current?->slug ?: Str::slug($name)),
            'description' => filled($data['description'] ?? null) ? $data['description'] : null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}
