@php
    $cleanSeoText = static function (mixed $value): string {
        return trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $value)));
    };
    $absoluteUrl = static function (mixed $value): string {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        return preg_match('/^https?:\/\//i', $value) === 1 ? $value : url($value);
    };
    $siteUrl = rtrim(url('/'), '/');
    $siteName = $cleanSeoText($website['name'] ?? config('app.name', 'Laravel'));
    $companyName = $cleanSeoText($website['company'] ?? '') ?: $siteName;
    $pageTitle = $cleanSeoText($__env->yieldContent('title', $website['seo_title'] ?? $siteName)) ?: $siteName;
    $pageDescription = $cleanSeoText($__env->yieldContent('meta_description', $website['seo_description'] ?? ($website['tagline'] ?? '')));
    $pageKeywords = $cleanSeoText($__env->yieldContent('meta_keywords', $website['seo_keywords'] ?? ''));
    $canonicalUrl = $absoluteUrl($__env->yieldContent('canonical', url()->current()));
    $defaultSeoImage = $siteAssets?->getFirstMediaUrl('seo_image') ?: $siteAssets?->getFirstMediaUrl('logo');
    $seoImage = $absoluteUrl($__env->yieldContent('seo_image', $defaultSeoImage));
    $logoUrl = $absoluteUrl($siteAssets?->getFirstMediaUrl('logo') ?: $defaultSeoImage);
    $locale = str_replace('_', '-', app()->getLocale());
    $ogLocale = str_replace('-', '_', $locale);
    $isPrivatePage = request()->routeIs('cart*', 'checkout*', 'wishlist', 'account.*', 'login*', 'register*');
    $isFacetedPage = request()->hasAny(['q', 'page', 'sort', 'price', 'stock', 'option_values', 'attribute_values']);
    $defaultRobots = $isPrivatePage
        ? 'noindex, nofollow, noarchive'
        : ($isFacetedPage ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1');
    $robots = $cleanSeoText($__env->yieldContent('robots', $defaultRobots)) ?: $defaultRobots;
    $socialLinks = collect($website['social'] ?? [])
        ->filter(fn (mixed $url): bool => filter_var($url, FILTER_VALIDATE_URL) !== false)
        ->values()
        ->all();
    $organizationSchema = array_filter([
        '@type' => 'Organization',
        '@id' => $siteUrl.'#organization',
        'name' => $companyName,
        'url' => $siteUrl,
        'logo' => $logoUrl ?: null,
        'image' => $seoImage ?: null,
        'description' => $cleanSeoText($website['tagline'] ?? '') ?: null,
        'telephone' => $cleanSeoText($website['phone'] ?? '') ?: null,
        'email' => $cleanSeoText($website['email'] ?? '') ?: null,
        'taxID' => $cleanSeoText($website['business_license'] ?? '') ?: null,
        'address' => filled($website['address'] ?? null) ? [
            '@type' => 'PostalAddress',
            'streetAddress' => $cleanSeoText($website['address']),
            'addressCountry' => 'VN',
        ] : null,
        'sameAs' => $socialLinks ?: null,
    ], static fn (mixed $value): bool => $value !== null && $value !== '');
    $websiteSchema = [
        '@type' => 'WebSite',
        '@id' => $siteUrl.'#website',
        'url' => $siteUrl,
        'name' => $siteName,
        'inLanguage' => $locale,
        'publisher' => ['@id' => $siteUrl.'#organization'],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => $siteUrl.'/san-pham?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
    $webPageSchema = array_filter([
        '@type' => 'WebPage',
        '@id' => $canonicalUrl.'#webpage',
        'url' => $canonicalUrl,
        'name' => $pageTitle,
        'description' => $pageDescription ?: null,
        'inLanguage' => $locale,
        'isPartOf' => ['@id' => $siteUrl.'#website'],
        'about' => ['@id' => $siteUrl.'#organization'],
        'primaryImageOfPage' => $seoImage ? ['@type' => 'ImageObject', 'url' => $seoImage] : null,
    ], static fn (mixed $value): bool => $value !== null && $value !== '');
    $baseSchema = ['@context' => 'https://schema.org', '@graph' => [$organizationSchema, $websiteSchema, $webPageSchema]];
@endphp
<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if($pageKeywords !== '')
        <meta name="keywords" content="{{ $pageKeywords }}">
    @endif
    <meta name="author" content="{{ $companyName }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:locale" content="{{ $ogLocale }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if($seoImage !== '')
        <meta property="og:image" content="{{ $seoImage }}">
        <meta property="og:image:alt" content="{{ $pageTitle }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ $seoImage }}">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    @hasSection('seo_published_time')
        <meta property="article:published_time" content="@yield('seo_published_time')">
    @endif
    @hasSection('seo_modified_time')
        <meta property="article:modified_time" content="@yield('seo_modified_time')">
    @endif
    @hasSection('article_section')
        <meta property="article:section" content="@yield('article_section')">
    @endif

    <script type="application/ld+json">{!! json_encode($baseSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @stack('schemas')
    @stack('meta')
    @if($siteAssets?->getFirstMediaUrl('favicon'))
        <link rel="icon" href="{{ $siteAssets->getFirstMediaUrl('favicon') }}">
        <link rel="apple-touch-icon" href="{{ $siteAssets->getFirstMediaUrl('favicon') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/fonts/be-vietnam-pro/font.css') }}?v={{ filemtime(public_path('assets/fonts/be-vietnam-pro/font.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/glightbox/css/glightbox.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    @php($themeVariables = app(\App\Services\ThemePaletteService::class)->currentCssVariables())
    <style id="frontend-theme-variables">
        :root {
            @foreach($themeVariables as $name => $value)
                {{ $name }}: {{ $value }};
            @endforeach
        }
    </style>
    @stack('styles')
</head>
<body>
    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.service-strip')
    @include('partials.footer')
    @include('partials.floating-actions')
    @include('partials.modals')

    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const Toast = window.Swal?.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });

            @if (session('success'))
                Toast?.fire({ icon: 'success', title: "{{ session('success') }}" });
            @endif

            @if (session('error'))
                Toast?.fire({ icon: 'error', title: "{{ session('error') }}" });
            @endif

            @if ($errors->any())
                Toast?.fire({ icon: 'error', title: "{{ $errors->first() }}" });
            @endif
        });
    </script>
    @stack('scripts')
</body>
</html>
