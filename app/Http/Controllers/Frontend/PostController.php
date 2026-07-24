<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends FrontendController
{
    public function index(Request $request = null, string $category = null): View
    {
        $categorySlug = trim((string) ($category ?? ''));

        $query = Post::query()
            ->where('is_active', true)
            ->visibleOnSite()
            ->whereHas('slugs', fn (Builder $query) => $query->where('locale', app()->getLocale()));

        if ($categorySlug !== '') {
            $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $categorySlug));
        }

        $newsItems = $query
            ->latest('published_at')
            ->with('category')
            ->get()
            ->map(fn (Post $post) => $this->presentPost($post));

        return view('frontend.posts.index', ['newsItems' => $newsItems]);
    }

    public function show(string $slug): View
    {
        $articleModel = Post::query()
            ->with('slugs')
            ->with('category')
            ->where('is_active', true)
            ->visibleOnSite()
            ->whereHas('slugs', fn (Builder $query) => $query->where('slug', $slug)->where('locale', app()->getLocale()))
            ->first();

        abort_if(! $articleModel, 404);

        $relatedNews = Post::query()
            ->where('is_active', true)
            ->visibleOnSite()
            ->whereKeyNot($articleModel->id)
            ->with('slugs')
            ->with('category')
            ->latest('published_at')
            ->take(5)
            ->get()
            ->map(fn (Post $post) => $this->presentPost($post));

        $article = $this->presentPost($articleModel);

        return view('frontend.posts.detail', [
            'article' => $article,
            'relatedNews' => $relatedNews,
        ]);
    }

    private function presentPost(Post $post): array
    {
        return [
            'domain' => $post->category?->slug ?: 'tin-tuc',
            'category_name' => $post->category?->getTranslation('name', 'vi'),
            'category_slug' => $post->category?->slug,
            'slug' => $post->slug ?: 'bai-viet-'.$post->id,
            'title' => $post->getTranslation('name', 'vi'),
            'date' => ($post->published_at ?: $post->created_at)->format('d.m.Y'),
            'image' => $post->getFirstMediaUrl('post_image') ?: asset('images/no-image.png'),
            'excerpt' => $post->getTranslation('summary', 'vi'),
            'content' => $post->getTranslation('content', 'vi'),
        ];
    }
}
