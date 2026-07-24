<?php

namespace App\Services;

use App\Models\Language;
use App\Models\PostCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostCategoryService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = PostCategory::query()->with('parent')->withCount('posts');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $languageCodes = Language::getActiveLanguages()->pluck('code');
            if ($languageCodes->isEmpty()) {
                $languageCodes = collect(['vi', 'en']);
            }

            $query->where(function ($builder) use ($languageCodes, $search) {
                foreach ($languageCodes as $code) {
                    $builder->orWhere("name->{$code}", 'like', "%{$search}%");
                }
            });
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->orderBy('sort_order')
            ->orderBy('id')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    /**
     * Tạo mới danh mục bài viết
     */
    public function create(array $data): PostCategory
    {
        return PostCategory::create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'] ?? [],
            'description' => $data['description'] ?? [],
            'seo_title' => $data['seo_title'] ?? [],
            'seo_description' => $data['seo_description'] ?? [],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_home' => isset($data['is_home']) && $data['is_home'] == 1,
            'is_active' => isset($data['is_active']) && $data['is_active'] == 1,
        ]);
    }

    /**
     * Cập nhật danh mục bài viết
     */
    public function update(PostCategory $category, array $data): void
    {
        $category->update([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'] ?? [],
            'description' => $data['description'] ?? [],
            'seo_title' => $data['seo_title'] ?? [],
            'seo_description' => $data['seo_description'] ?? [],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_home' => isset($data['is_home']) && $data['is_home'] == 1,
            'is_active' => isset($data['is_active']) && $data['is_active'] == 1,
        ]);
    }

    /**
     * Xóa danh mục bài viết
     */
    public function delete(PostCategory $category): void
    {
        $category->delete();
    }
}
