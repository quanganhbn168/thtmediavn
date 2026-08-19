<?php

namespace App\Http\Controllers\Frontend;

use App\Models\CompanyContent;
use App\Support\ArticleContent;
use App\Support\SchemaMarkup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class CompanyContentController extends FrontendController
{
    public function show(string $slug): View
    {
        $content = CompanyContent::query()
            ->visibleOnSite()
            ->with(['image', 'banner', 'shareImage', 'slugs'])
            ->where(function (Builder $query) use ($slug): void {
                $query
                    ->where('slug', $slug)
                    ->orWhereHas('slugs', fn (Builder $slugQuery) => $slugQuery
                        ->where('slug', $slug)
                        ->where('locale', app()->getLocale()));
            })
            ->firstOrFail();

        $title = $content->getTranslation('title', 'vi');
        $summary = $content->getTranslation('summary', 'vi');
        $body = ArticleContent::prepare($content->getTranslation('content', 'vi'));
        $image = $content->image?->url ?: $content->getFirstMediaUrl('company_image');
        $banner = $content->banner?->url ?: $content->getFirstMediaUrl('company_banner');
        $shareImage = $content->shareImage?->url ?: $content->getFirstMediaUrl('share_image');
        $routeSlug = $content->routeSlug(app()->getLocale());

        return view('frontend.about.content', [
            'companyContent' => $content,
            'contentTitle' => $title,
            'contentSummary' => $summary,
            'contentBody' => $body['content'],
            'contentToc' => $body['toc'],
            'contentBanner' => $banner,
            'contentImage' => $image,
            'contentShareImage' => $shareImage,
            'contentSchema' => SchemaMarkup::article([
                'headline' => $title,
                'description' => $content->getTranslation('seo_description', 'vi') ?: $summary,
                'url' => route('about.content.show', ['slug' => $routeSlug]),
                'image' => array_values(array_filter([$shareImage, $banner, $image])),
                'datePublished' => ($content->published_at ?: $content->created_at)?->toIso8601String(),
                'dateModified' => $content->updated_at?->toIso8601String(),
                'publisher' => config('app.name', 'THT Media VN'),
            ]),
        ]);
    }
}
