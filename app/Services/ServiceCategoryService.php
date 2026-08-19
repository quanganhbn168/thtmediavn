<?php

namespace App\Services;

use App\Models\ServiceCategory;
use Illuminate\Validation\ValidationException;

class ServiceCategoryService
{
    public function create(array $data): ServiceCategory
    {
        return ServiceCategory::query()->create($this->payload($data));
    }

    public function update(ServiceCategory $category, array $data): void
    {
        $category->update($this->payload($data));
    }

    public function delete(ServiceCategory $category): void
    {
        if ($category->services()->exists() || $category->children()->exists()) {
            throw ValidationException::withMessages([
                'ids' => 'Không thể xóa danh mục dịch vụ đang có dịch vụ hoặc danh mục con.',
            ]);
        }

        $category->delete();
    }

    private function payload(array $data): array
    {
        return [
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'] ?? [],
            'description' => $data['description'] ?? [],
            'seo_title' => $data['seo_title'] ?? [],
            'seo_description' => $data['seo_description'] ?? [],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }
}
