<?php

namespace App\Support;

class SchemaMarkup
{
    /**
     * @param  array<string, mixed>  $website
     * @return array<string, mixed>
     */
    public static function homepage(
        array $website,
        ?string $logoUrl = null,
        ?string $imageUrl = null,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?array $rating = null,
    ): array {
        $siteUrl = rtrim(url('/'), '/');
        $siteName = trim((string) ($website['name'] ?? 'THT Media VN')) ?: 'THT Media VN';
        $organizationName = trim((string) ($website['company'] ?? '')) ?: $siteName;
        $organizationDescription = trim((string) ($website['seo_description'] ?? $website['tagline'] ?? ''));
        $pageTitle = trim((string) ($pageTitle ?: ($website['seo_title'] ?? $siteName))) ?: $siteName;
        $pageDescription = trim((string) ($pageDescription ?: $organizationDescription));
        $social = (array) ($website['social'] ?? []);
        $sameAs = array_values(array_filter([
            $social['facebook'] ?? null,
            $social['instagram'] ?? null,
            $social['youtube'] ?? null,
            $social['tiktok'] ?? null,
        ], static fn (mixed $url): bool => is_string($url) && filter_var($url, FILTER_VALIDATE_URL) !== false));

        $organization = array_filter([
            '@type' => 'Organization',
            '@id' => $siteUrl . '#organization',
            'name' => $organizationName,
            'alternateName' => $siteName !== $organizationName ? $siteName : null,
            'url' => $siteUrl . '/',
            'logo' => $logoUrl,
            'image' => $imageUrl,
            'description' => $organizationDescription ?: null,
            'sameAs' => $sameAs ?: null,
            'telephone' => filled($website['phone'] ?? null) ? (string) $website['phone'] : null,
            'email' => filled($website['email'] ?? null) ? (string) $website['email'] : null,
            'address' => filled($website['address'] ?? null) ? (string) $website['address'] : null,
            'taxID' => filled($website['business_license'] ?? null) ? (string) $website['business_license'] : null,
            'aggregateRating' => self::aggregateRating($rating),
        ], static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);

        if (filled($website['phone'] ?? null) || filled($website['email'] ?? null)) {
            $organization['contactPoint'] = array_filter([
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'telephone' => filled($website['phone'] ?? null) ? (string) $website['phone'] : null,
                'email' => filled($website['email'] ?? null) ? (string) $website['email'] : null,
                'areaServed' => 'VN',
                'availableLanguage' => ['vi'],
            ], static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $organization,
                array_filter([
                    '@type' => 'WebSite',
                    '@id' => $siteUrl . '#website',
                    'url' => $siteUrl . '/',
                    'name' => $siteName,
                    'description' => $organizationDescription ?: null,
                    'inLanguage' => 'vi-VN',
                    'publisher' => ['@id' => $siteUrl . '#organization'],
                ], static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []),
                array_filter([
                    '@type' => 'WebPage',
                    '@id' => $siteUrl . '#webpage',
                    'url' => $siteUrl . '/',
                    'name' => $pageTitle,
                    'description' => $pageDescription ?: null,
                    'isPartOf' => ['@id' => $siteUrl . '#website'],
                    'about' => ['@id' => $siteUrl . '#organization'],
                    'mainEntity' => ['@id' => $siteUrl . '#organization'],
                    'primaryImageOfPage' => $imageUrl,
                    'inLanguage' => 'vi-VN',
                ], static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []),
            ],
        ];
    }

    /**
     * @param  array<int, array{label:string, url?:string|null}>  $items
     * @return array<string, mixed>
     */
    public static function breadcrumb(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
                ...($item['url'] ?? null ? ['item' => $item['url']] : []),
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function webPage(string $name, string $description = '', ?string $url = null): array
    {
        $url = $url ?: url()->current();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            '@id' => $url.'#webpage',
            'name' => $name,
            ...($description !== '' ? ['description' => $description] : []),
            'url' => $url,
            'isPartOf' => ['@id' => url('/').'#website'],
            'inLanguage' => self::language(),
        ];
    }

    /**
     * @param  array<int, array{name:string, url:string}>  $items
     * @return array<string, mixed>
     */
    public static function collection(string $name, string $description, array $items = [], ?string $url = null): array
    {
        $url = $url ?: url()->current();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            '@id' => $url.'#webpage',
            'name' => $name,
            ...($description !== '' ? ['description' => $description] : []),
            'url' => $url,
            'isPartOf' => ['@id' => url('/').'#website'],
            'inLanguage' => self::language(),
            'mainEntity' => [
                '@type' => 'ItemList',
                '@id' => $url.'#item-list',
                'itemListElement' => collect($items)->values()->map(fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'url' => $item['url'],
                ])->all(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function article(array $data): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            '@id' => $data['url'].'#article',
            'headline' => $data['headline'],
            'description' => $data['description'] ?? null,
            'url' => $data['url'],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $data['url']],
            'image' => array_values(array_filter((array) ($data['image'] ?? []))),
            'datePublished' => $data['datePublished'] ?? null,
            'dateModified' => $data['dateModified'] ?? null,
            'author' => [
                '@type' => 'Organization',
                'name' => $data['author'] ?? 'THT Media VN',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $data['publisher'] ?? 'THT Media VN',
                'url' => url('/'),
            ],
            'inLanguage' => self::language(),
        ], static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
    }

    /**
     * @return array<string, mixed>
     */
    public static function service(string $name, string $description, string $url, ?string $image = null, ?array $rating = null): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            '@id' => $url.'#service',
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'image' => $image,
            'provider' => [
                '@type' => 'Organization',
                'name' => 'THT Media VN',
                'url' => url('/'),
            ],
            'aggregateRating' => self::aggregateRating($rating),
            'inLanguage' => self::language(),
        ], static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function project(array $data): array
    {
        $url = (string) $data['url'];
        $projectId = $url.'#project';
        $description = trim((string) ($data['description'] ?? ''));
        $image = trim((string) ($data['image'] ?? ''));
        $client = trim((string) ($data['client'] ?? ''));
        $services = array_values(array_filter((array) ($data['services'] ?? []), static fn (mixed $service): bool => is_string($service) && trim($service) !== ''));
        $graph = [
            array_filter([
                '@type' => 'WebPage',
                '@id' => $url.'#webpage',
                'url' => $url,
                'name' => $data['name'],
                'description' => $description,
                'isPartOf' => ['@id' => url('/').'#website'],
                'mainEntity' => ['@id' => $projectId],
                'inLanguage' => self::language(),
            ], static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []),
            array_filter([
                '@type' => 'CreativeWork',
                '@id' => $projectId,
                'name' => $data['name'],
                'description' => $description,
                'url' => $url,
                'image' => $image ?: null,
                'about' => $client !== '' ? ['@type' => 'Organization', 'name' => $client] : null,
                'creator' => ['@type' => 'Organization', 'name' => 'THT Media VN', 'url' => url('/')],
                'keywords' => $services ?: null,
                'aggregateRating' => self::aggregateRating($data['rating'] ?? null),
                'mainEntityOfPage' => ['@id' => $url.'#webpage'],
            ], static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []),
        ];

        if (filled($data['video'] ?? null)) {
            $graph[] = array_filter([
                '@type' => 'VideoObject',
                '@id' => $url.'#video',
                'name' => $data['name'].' — video dự án',
                'description' => $description,
                'url' => $data['video'],
                'thumbnailUrl' => $image ?: null,
                'isPartOf' => ['@id' => $projectId],
            ], static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $plans
     * @return array<string, mixed>
     */
    public static function pricing(string $name, string $description, string $url, array $plans = []): array
    {
        $offers = collect($plans)->values()->map(function (array $plan) use ($url): array {
            return array_filter([
                '@type' => 'Offer',
                'name' => $plan['name'],
                'description' => $plan['summary'] ?? null,
                'url' => $url.'#pricing-plan-'.$plan['id'],
                'itemOffered' => [
                    '@type' => 'Service',
                    'name' => $plan['name'],
                ],
            ], static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
        })->all();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            '@id' => $url.'#webpage',
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'isPartOf' => ['@id' => url('/').'#website'],
            'inLanguage' => self::language(),
            'mainEntity' => [
                '@type' => 'OfferCatalog',
                '@id' => $url.'#offer-catalog',
                'name' => $name,
                'itemListElement' => $offers,
            ],
        ];
    }

    private static function language(): string
    {
        $locale = str_replace('_', '-', (string) app()->getLocale());

        return $locale === 'vi' ? 'vi-VN' : $locale;
    }

    /**
     * @param  array<string, mixed>|null  $rating
     * @return array<string, mixed>|null
     */
    private static function aggregateRating(?array $rating): ?array
    {
        $ratingValue = (float) ($rating['ratingValue'] ?? 0);
        $ratingCount = (int) ($rating['ratingCount'] ?? 0);

        if ($ratingCount < 1 || $ratingValue < 1 || $ratingValue > 5) {
            return null;
        }

        return [
            '@type' => 'AggregateRating',
            'ratingValue' => round($ratingValue, 1),
            'ratingCount' => $ratingCount,
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }
}
