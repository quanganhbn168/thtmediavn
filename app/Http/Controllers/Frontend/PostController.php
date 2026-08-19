<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Post;
use App\Support\ArticleContent;
use App\Support\SchemaMarkup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends FrontendController
{
    public function index(?Request $request = null, ?string $category = null): View
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
            ->with(['category', 'image'])
            ->get()
            ->map(fn (Post $post) => $this->presentPost($post));

        return view('frontend.posts.index', ['newsItems' => $newsItems]);
    }

    public function show(string $slug): View
    {
        $articleModel = Post::query()
            ->with('slugs')
            ->with(['category', 'image'])
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
            ->with(['category', 'image'])
            ->latest('published_at')
            ->take(5)
            ->get()
            ->map(fn (Post $post) => $this->presentPost($post));

        $article = $this->presentPost($articleModel);
        $preparedContent = ArticleContent::prepare($article['content']);
        $article['content'] = $preparedContent['content'];
        $article['toc'] = $preparedContent['toc'];
        $article['url'] = route('content.show', [
            'domain' => $article['domain'],
            'slug' => $article['slug'],
        ]);

        return view('frontend.posts.detail', [
            'article' => $article,
            'relatedNews' => $relatedNews,
            'commentable' => $articleModel,
            'comments' => $articleModel->comments()->with('replies')->get(),
            'articleSchema' => SchemaMarkup::article([
                'headline' => $article['title'],
                'description' => $article['seo_description'] ?: $article['excerpt'],
                'url' => $article['url'],
                'image' => [$article['image']],
                'datePublished' => $article['published_at'],
                'dateModified' => $article['modified_at'],
                'publisher' => config('app.name', 'THT Media VN'),
            ]),
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
            'image' => $post->image?->url ?: $post->getFirstMediaUrl('post_image') ?: asset('assets/images/no-image.svg'),
            'excerpt' => $post->getTranslation('summary', 'vi'),
            'content' => $post->getTranslation('content', 'vi'),
            'seo_title' => $post->getTranslation('seo_title', 'vi'),
            'seo_description' => $post->getTranslation('seo_description', 'vi'),
            'seo_keywords' => $post->getTranslation('seo_keywords', 'vi'),
            'published_at' => ($post->published_at ?: $post->created_at)?->toIso8601String(),
            'modified_at' => $post->updated_at?->toIso8601String(),
        ];
    }
}
