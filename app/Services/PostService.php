<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PostService
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Post::query()->with('category');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $languageCodes = Language::getActiveLanguages()->pluck('code');
            if ($languageCodes->isEmpty()) {
                $languageCodes = collect(['vi', 'en']);
            }

            $query->where(function ($builder) use ($languageCodes, $search) {
                foreach ($languageCodes as $code) {
                    $builder->orWhere("name->{$code}", 'like', "%{$search}%")
                        ->orWhere("summary->{$code}", 'like', "%{$search}%");
                }
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('post_category_id', $filters['category_id']);
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function categories(): Collection
    {
        return PostCategory::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Tạo mới bài viết
     */
    public function create(array $data): Post
    {
        $post = new Post;
        $post->setSlugOverride($data['slug'] ?? null);
        $post->image_id = $data['image_id'] ?? null;
        $post->post_category_id = $data['post_category_id'];
        $post->name = $data['name'] ?? [];
        $post->summary = $data['summary'] ?? [];
        $post->content = $data['content'] ?? [];
        $post->seo_title = $data['seo_title'] ?? [];
        $post->seo_description = $data['seo_description'] ?? [];
        $post->seo_keywords = $data['seo_keywords'] ?? [];
        $post->is_featured = isset($data['is_featured']) && $data['is_featured'] == 1;
        $post->is_active = isset($data['is_active']) && $data['is_active'] == 1;
        $post->published_at = $data['published_at'] ?? now();
        $post->save();

        if (array_key_exists('image', $data)) {
            $this->mediaService->syncSingle($post, 'post_image', $data['image'] ?? null);
        }

        return $post;
    }

    /**
     * Cập nhật bài viết
     */
    public function update(Post $post, array $data): void
    {
        $post->setSlugOverride($data['slug'] ?? null);
        $post->image_id = $data['image_id'] ?? null;
        $post->post_category_id = $data['post_category_id'];
        $post->name = $data['name'] ?? [];
        $post->summary = $data['summary'] ?? [];
        $post->content = $data['content'] ?? [];
        $post->seo_title = $data['seo_title'] ?? [];
        $post->seo_description = $data['seo_description'] ?? [];
        $post->seo_keywords = $data['seo_keywords'] ?? [];
        $post->is_featured = isset($data['is_featured']) && $data['is_featured'] == 1;
        $post->is_active = isset($data['is_active']) && $data['is_active'] == 1;
        $post->published_at = $data['published_at'] ?? null;
        $post->save();

        if (array_key_exists('image', $data)) {
            $this->mediaService->syncSingle(
                $post,
                'post_image',
                $data['image'] ?? null,
                (bool) ($data['image_remove'] ?? false),
            );
        }
    }

    /**
     * Xóa bài viết
     */
    public function delete(Post $post): void
    {
        $post->clearMediaCollection('post_image');
        $post->delete();
    }
}
