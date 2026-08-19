<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Collection;

abstract class FrontendController extends Controller
{
    protected function news(int $limit = 20): Collection
    {
        return $this->newsQuery()->take($limit)->get()->map(fn (Post $post) => $this->presentNewsCard($post));
    }

    protected function homeNews(int $limit = 13): Collection
    {
        return $this->newsQuery()
            ->whereHas('category', fn ($query) => $query
                ->where('is_active', true)
                ->where('is_home', true))
            ->orderByDesc('is_featured')
            ->take($limit)
            ->get()
            ->map(fn (Post $post) => $this->presentNewsCard($post));
    }

    private function newsQuery()
    {
        return Post::query()
            ->where('is_active', true)
            ->visibleOnSite()
            ->with(['category.slugs', 'image', 'slugs'])
            ->latest('published_at');
    }

    private function presentNewsCard(Post $post): array
    {
        return [
            'domain' => $post->category?->slug ?: 'tin-tuc',
            'category' => $post->category?->getTranslation('name', 'vi'),
            'category_slug' => $post->category?->slug,
            'slug' => $post->slug ?: 'bai-viet-'.$post->id,
            'title' => $post->getTranslation('name', 'vi'),
            'date' => ($post->published_at ?: $post->created_at)->format('d.m.Y'),
            'image' => $post->image?->url ?: $post->getFirstMediaUrl('post_image') ?: asset('assets/images/no-image.svg'),
            'excerpt' => $post->getTranslation('summary', 'vi'),
            'content' => $post->getTranslation('content', 'vi'),
        ];
    }
}
