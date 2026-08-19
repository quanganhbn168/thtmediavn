<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $website['seo_title'] ?? $website['name'] ?? config('app.name', 'Laravel'))</title>
    <meta name="description" content="@yield('meta_description', $website['seo_description'] ?? $website['tagline'] ?? '')">
    <meta name="keywords" content="@yield('meta_keywords', $website['seo_keywords'] ?? '')">
    <meta name="author" content="{{ $website['company'] ?? $website['name'] ?? config('app.name', 'Laravel') }}">
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $website['name'] ?? config('app.name', 'Laravel') }}">
    <meta property="og:title" content="@yield('title', $website['seo_title'] ?? $website['name'] ?? config('app.name', 'Laravel'))">
    <meta property="og:description" content="@yield('meta_description', $website['seo_description'] ?? $website['tagline'] ?? '')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('seo_image', $siteAssets?->getFirstMediaUrl('seo_image') ?: $siteAssets?->getFirstMediaUrl('logo'))">
    <meta property="og:image:alt" content="@yield('title', $website['seo_title'] ?? $website['name'] ?? config('app.name', 'Laravel'))">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $website['seo_title'] ?? $website['name'] ?? config('app.name', 'Laravel'))">
    <meta name="twitter:description" content="@yield('meta_description', $website['seo_description'] ?? $website['tagline'] ?? '')">
    <meta name="twitter:image" content="@yield('seo_image', $siteAssets?->getFirstMediaUrl('seo_image') ?: $siteAssets?->getFirstMediaUrl('logo'))">

    <link rel="icon" href="{{ $siteAssets?->getFirstMediaUrl('favicon') }}">
    <link rel="apple-touch-icon" href="{{ $siteAssets?->getFirstMediaUrl('favicon') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('structured_data')
    @if($trackingSettings?->head_code)
        {!! $trackingSettings->head_code !!}
    @endif
    @if($trackingSettings?->google_analytics_code)
        {!! $trackingSettings->google_analytics_code !!}
    @endif
    @if($trackingSettings?->meta_pixel_code)
        {!! $trackingSettings->meta_pixel_code !!}
    @endif
    @stack('styles')
</head>
<body>
    @if($trackingSettings?->body_open_code)
        {!! $trackingSettings->body_open_code !!}
    @endif
    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.floating-actions')
    @include('partials.popup')
    @stack('overlays')

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
                Toast?.fire({ icon: 'success', title: @js(session('success')) });
            @endif

            @if (session('error'))
                Toast?.fire({ icon: 'error', title: @js(session('error')) });
            @endif

            @if ($errors->any())
                Toast?.fire({ icon: 'error', title: @js($errors->first()) });
            @endif
        });
    </script>
    @stack('scripts')
    @if($trackingSettings?->body_close_code)
        {!! $trackingSettings->body_close_code !!}
    @endif
</body>
</html>
