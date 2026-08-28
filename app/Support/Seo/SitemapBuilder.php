<?php

namespace App\Support\Seo;

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use DateTimeInterface;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapBuilder
{
    public function build(): Sitemap
    {
        $sitemap = Sitemap::create();

        $this->addNativePages($sitemap);
        $this->addNativeContent($sitemap);

        return $sitemap;
    }

    private function addNativePages(Sitemap $sitemap): void
    {
        foreach ([
            ['home', Url::CHANGE_FREQUENCY_DAILY, 1.0],
            ['about', Url::CHANGE_FREQUENCY_MONTHLY, 0.8],
            ['services.index', Url::CHANGE_FREQUENCY_WEEKLY, 0.9],
            ['projects.index', Url::CHANGE_FREQUENCY_WEEKLY, 0.9],
            ['clients.index', Url::CHANGE_FREQUENCY_MONTHLY, 0.7],
            ['news.index', Url::CHANGE_FREQUENCY_WEEKLY, 0.7],
            ['contact', Url::CHANGE_FREQUENCY_MONTHLY, 0.6],
        ] as [$route, $frequency, $priority]) {
            $this->addUrl($sitemap, $this->routeUrl($route), null, $frequency, $priority);
        }
    }

    private function addNativeContent(Sitemap $sitemap): void
    {
        $locale = (string) config('app.locale', 'vi');

        Service::query()
            ->where('is_active', true)
            ->with('slugs')
            ->get()
            ->each(function (Service $service) use ($sitemap, $locale): void {
                $this->addUrl(
                    $sitemap,
                    $this->routeWithSlug('services.show', $service->getSlug($locale)),
                    $service->updated_at ?: $service->created_at,
                    Url::CHANGE_FREQUENCY_MONTHLY,
                    0.8,
                );
            });

        ServiceCategory::query()
            ->where('is_active', true)
            ->with('slugs')
            ->get()
            ->each(function (ServiceCategory $category) use ($sitemap, $locale): void {
                $this->addUrl(
                    $sitemap,
                    $this->routeWithSlug('services.show', $category->getSlug($locale)),
                    $category->updated_at ?: $category->created_at,
                    Url::CHANGE_FREQUENCY_MONTHLY,
                    0.7,
                );
            });

        Project::query()
            ->visibleOnSite()
            ->with('slugs')
            ->get()
            ->each(function (Project $project) use ($sitemap, $locale): void {
                $this->addUrl(
                    $sitemap,
                    $this->routeWithSlug('projects.show', $project->getSlug($locale)),
                    $project->updated_at ?: $project->created_at,
                    Url::CHANGE_FREQUENCY_MONTHLY,
                    0.8,
                );
            });

        ProjectCategory::query()
            ->where('is_active', true)
            ->with('slugs')
            ->get()
            ->each(function (ProjectCategory $category) use ($sitemap, $locale): void {
                $this->addUrl(
                    $sitemap,
                    $this->routeWithSlug('projects.show', $category->getSlug($locale)),
                    $category->updated_at ?: $category->created_at,
                    Url::CHANGE_FREQUENCY_MONTHLY,
                    0.7,
                );
            });

        Post::query()
            ->where('is_active', true)
            ->visibleOnSite()
            ->with(['category.slugs', 'slugs'])
            ->get()
            ->each(function (Post $post) use ($sitemap, $locale): void {
                $this->addUrl(
                    $sitemap,
                    $this->routeWithParameters('content.show', [
                        'domain' => $post->category?->slug ?: 'tin-tuc',
                        'slug' => $post->getSlug($locale),
                    ]),
                    $post->updated_at ?: $post->created_at,
                    Url::CHANGE_FREQUENCY_MONTHLY,
                    0.6,
                );
            });

        Page::query()
            ->where('is_active', true)
            ->visibleOnSite()
            ->with('slugs')
            ->get()
            ->each(function (Page $page) use ($sitemap, $locale): void {
                $this->addUrl(
                    $sitemap,
                    $this->routeWithParameters('content.show', [
                        'domain' => 'trang',
                        'slug' => $page->getSlug($locale),
                    ]),
                    $page->updated_at ?: $page->created_at,
                    Url::CHANGE_FREQUENCY_MONTHLY,
                    0.6,
                );
            });
    }

    private function addUrl(
        Sitemap $sitemap,
        ?string $url,
        ?DateTimeInterface $lastModified,
        string $frequency,
        float $priority,
    ): void {
        if (! filled($url)) {
            return;
        }

        $tag = Url::create($url)
            ->setChangeFrequency($frequency)
            ->setPriority($priority);

        if ($lastModified instanceof DateTimeInterface) {
            $tag->setLastModificationDate($lastModified);
        }

        $sitemap->add($tag);
    }

    private function routeWithSlug(string $route, ?string $slug): ?string
    {
        return filled($slug) ? $this->routeUrl($route, ['slug' => $slug]) : null;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function routeWithParameters(string $route, array $parameters): ?string
    {
        return filled($parameters['slug'] ?? null) ? $this->routeUrl($route, $parameters) : null;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function routeUrl(string $route, array $parameters = []): string
    {
        $baseUrl = rtrim((string) config('app.url'), '/');
        $path = route($route, $parameters, false);

        return $path === '/'
            ? $baseUrl.'/'
            : $baseUrl.'/'.ltrim($path, '/');
    }
}
