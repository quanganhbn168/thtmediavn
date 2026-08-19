<?php

namespace App\Services;

use App\Models\ProjectCategory;
use Illuminate\Validation\ValidationException;

class ProjectCategoryService
{
    public function create(array $data): ProjectCategory
    {
        return ProjectCategory::query()->create($this->payload($data));
    }

    public function update(ProjectCategory $category, array $data): void
    {
        $category->update($this->payload($data));
    }

    public function delete(ProjectCategory $category): void
    {
        if ($category->projects()->exists() || $category->children()->exists()) {
            throw ValidationException::withMessages([
                'ids' => 'Không thể xóa danh mục dự án đang có dự án hoặc danh mục con.',
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
