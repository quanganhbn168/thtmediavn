<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $website['seo_title'])</title>
    <meta name="description" content="@yield('meta_description', $website['seo_description'] ?: $website['tagline'])">
    @if($siteAssets?->getFirstMediaUrl('favicon'))
        <link rel="icon" href="{{ $siteAssets->getFirstMediaUrl('favicon') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/fonts/be-vietnam-pro/font.css') }}?v={{ filemtime(public_path('assets/fonts/be-vietnam-pro/font.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
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
