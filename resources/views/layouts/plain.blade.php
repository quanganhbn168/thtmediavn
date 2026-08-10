<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive">

    <title>@yield('title', config('app.name', 'THT MEDIA VN'))</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />

    <!--begin::Fonts-->
    <link rel="stylesheet" href="{{ asset('vendor/fontsource-source-sans-3/index.css') }}" />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="{{ asset('vendor/overlayscrollbars/overlayscrollbars.min.css') }}" />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte4/css/adminlte.min.css') }}" />
    <!--end::Required Plugin(AdminLTE)-->

    <!--begin::Third Party Plugin(SweetAlert2)-->
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}" />
    <!--end::Third Party Plugin(SweetAlert2)-->

    <!-- Stack for additional stylesheets -->
    @stack('css')
  </head>
  <!--end::Head-->

  <!--begin::Body-->
  <body class="@yield('body-class', 'layout-fixed sidebar-expand-lg bg-body-tertiary')">
    
    @yield('body')

    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="{{ asset('vendor/overlayscrollbars/overlayscrollbars.browser.es6.min.js') }}"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Required Plugin(Bootstrap 5 Bundle - Includes Popper)-->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!--end::Required Plugin(Bootstrap 5)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('vendor/adminlte4/js/adminlte.min.js') }}"></script>
    <!--end::Required Plugin(AdminLTE)-->

    <!--begin::Third Party Plugin(SweetAlert2)-->
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <!--end::Third Party Plugin(SweetAlert2)-->

    <!--begin::Color Mode Toggle-->
    <script>
      (() => {
        'use strict';

        const STORAGE_KEY = 'lte-theme';

        const getStoredTheme = () => localStorage.getItem(STORAGE_KEY);
        const setStoredTheme = (theme) => localStorage.setItem(STORAGE_KEY, theme);

        const prefersDark = () => globalThis.matchMedia('(prefers-color-scheme: dark)').matches;

        const getPreferredTheme = () => {
          const stored = getStoredTheme();
          if (stored) return stored;
          return prefersDark() ? 'dark' : 'light';
        };

        const setTheme = (theme) => {
          const resolved = theme === 'auto' ? (prefersDark() ? 'dark' : 'light') : theme;
          document.documentElement.setAttribute('data-bs-theme', resolved);
        };

        setTheme(getPreferredTheme());

        const showActiveTheme = (theme) => {
          document.querySelectorAll('[data-bs-theme-value]').forEach((el) => {
            el.classList.remove('active');
            el.setAttribute('aria-pressed', 'false');
            const check = el.querySelector('.bi-check-lg');
            if (check) check.classList.add('d-none');
          });
          const active = document.querySelector(`[data-bs-theme-value="${theme}"]`);
          if (active) {
            active.classList.add('active');
            active.setAttribute('aria-pressed', 'true');
            const check = active.querySelector('.bi-check-lg');
            if (check) check.classList.remove('d-none');
          }
          document.querySelectorAll('[data-lte-theme-icon]').forEach((icon) => {
            icon.classList.toggle('d-none', icon.dataset.lteThemeIcon !== theme);
          });
        };

        globalThis.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
          const stored = getStoredTheme();
          if (!stored || stored === 'auto') setTheme(getPreferredTheme());
        });

        document.addEventListener('DOMContentLoaded', () => {
          showActiveTheme(getPreferredTheme());
          document.querySelectorAll('[data-bs-theme-value]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
              const theme = toggle.getAttribute('data-bs-theme-value');
              setStoredTheme(theme);
              setTheme(theme);
              showActiveTheme(theme);
            });
          });
        });
      })();
    </script>
    <!--end::Color Mode Toggle-->

    <!-- Stack for additional scripts -->
    @stack('js')

    <!-- SweetAlert2 Toast Notifications -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
          }
        });

        @if (session('success'))
          Toast.fire({
            icon: 'success',
            title: "{{ session('success') }}"
          });
        @endif

        @if (session('error'))
          Toast.fire({
            icon: 'error',
            title: "{{ session('error') }}"
          });
        @endif

        @if ($errors->any())
          Toast.fire({
            icon: 'error',
            title: "{{ $errors->first() }}"
          });
        @endif
      });
    </script>
  </body>
</html>
