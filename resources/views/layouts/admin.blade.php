<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive">

    <title>@yield('page-title', 'Dashboard') | {{ config('app.name', 'RHEA SKINLAB') }}</title>

    <!-- Favicon quản trị dùng chung nhận diện đã cấu hình -->
    @php($adminFavicon = \App\Models\SiteAsset::current()->getFirstMediaUrl('favicon') ?: asset('favicon.ico'))
    <link rel="icon" href="{{ $adminFavicon }}" />

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="{{ asset('vendor/overlayscrollbars/overlayscrollbars.min.css') }}" />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="{{ asset('vendor/tom-select/css/tom-select.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/dropzone/dropzone.min.css') }}" />

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte4/css/adminlte.min.css') }}" />
    <!--end::Required Plugin(AdminLTE)-->

    <!--begin::Third Party Plugin(SweetAlert2)-->
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}" />
    <!--end::Third Party Plugin(SweetAlert2)-->

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}" />
    <script defer src="{{ asset('vendor/alpinejs-mask.min.js') }}"></script>
    <script defer src="{{ asset('vendor/alpinejs.min.js') }}"></script>
    <script src="{{ asset('vendor/sortable/sortable.min.js') }}"></script>
    <script src="{{ asset('vendor/tom-select/js/tom-select.complete.min.js') }}" defer></script>
    <script src="{{ asset('js/admin.js') }}" defer></script>
    <!-- Stack for additional stylesheets -->
    @stack('css')
  </head>
  <!--end::Head-->

  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      @include('admin.partials.header')
      <!--end::Header-->

      <!--begin::Sidebar-->
      @include('admin.partials.sidebar')
      <!--end::Sidebar-->

      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6">
                <h3 class="mb-0">@yield('page-title', 'Dashboard')</h3>
              </div>
              <div class="col-sm-6">
                <!-- Breadcrumbs block -->
                @yield('breadcrumbs')
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->

        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            @yield('content')
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

      <!--begin::Footer-->
      @include('admin.partials.footer')
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->

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

    <script src="{{ asset('vendor/dropzone/dropzone.min.js') }}"></script>
    <script>Dropzone.autoDiscover = false;</script>

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

    <!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        const isMobile = window.innerWidth <= 992;
        let scrollElement = sidebarWrapper;

        if (
          sidebarWrapper &&
          OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
          !isMobile
        ) {
          const osInstance = OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });

          if (osInstance) {
            scrollElement = osInstance.elements().scrollOffsetElement;
          }
        }

        if (scrollElement) {
          // Khôi phục vị trí cuộn từ sessionStorage
          const savedScroll = sessionStorage.getItem('sidebar-scroll');
          if (savedScroll) {
            scrollElement.scrollTop = parseInt(savedScroll, 10);
            // Gán thêm trong timeout phòng trường hợp trình duyệt mất thời gian tính toán layout
            setTimeout(() => {
              scrollElement.scrollTop = parseInt(savedScroll, 10);
            }, 50);
          }

          // Lắng nghe sự kiện cuộn để lưu lại vị trí hiện tại
          scrollElement.addEventListener('scroll', function () {
            sessionStorage.setItem('sidebar-scroll', scrollElement.scrollTop);
          });
        }
      });

      // Hàm hiển thị xem trước ảnh (preview) trực tiếp khi chọn file
      window.previewImage = function (input, previewId) {
        if (input.files && input.files[0]) {
          const reader = new FileReader();
          reader.onload = function (e) {
            const preview = document.getElementById(previewId);
            if (preview) {
              preview.src = e.target.result;
              // Nếu phần tử chứa đang ẩn thì hiện lên
              const parent = preview.parentElement;
              if (parent && parent.classList.contains('d-none')) {
                parent.classList.remove('d-none');
              }
            }
          };
          reader.readAsDataURL(input.files[0]);
        }
      };
    </script>
    <!--end::OverlayScrollbars Configure-->

    <!-- Global AJAX Toggle Switch Handler -->
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        document.body.addEventListener('change', function (e) {
          const checkbox = e.target.closest('.toggle-field-switch');
          if (!checkbox) return;

          const model = checkbox.getAttribute('data-model');
          const id = checkbox.getAttribute('data-id');
          const field = checkbox.getAttribute('data-field');
          const isChecked = checkbox.checked;

          // Vô hiệu hóa checkbox tạm thời để tránh click liên tục
          checkbox.disabled = true;

          // Khởi tạo Toast bằng SweetAlert2
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

          // Gửi request bằng Fetch API thay vì jQuery
          fetch("{{ route('admin.common.toggle-field') }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              model: model,
              id: id,
              field: field
            })
          })
            .then(response => {
              if (!response.ok) {
                throw new Error('Mã phản hồi lỗi: ' + response.status);
              }
              return response.json();
            })
            .then(response => {
              checkbox.disabled = false;
              if (response.success) {
                Toast.fire({
                  icon: 'success',
                  title: response.message
                });
                checkbox.checked = response.value;
              } else {
                checkbox.checked = !isChecked; // Khôi phục lại trạng thái cũ
                Toast.fire({
                  icon: 'error',
                  title: response.message || 'Có lỗi xảy ra.'
                });
              }
            })
            .catch(error => {
              checkbox.disabled = false;
              checkbox.checked = !isChecked; // Khôi phục lại trạng thái cũ
              Toast.fire({
                icon: 'error',
                title: 'Không thể kết nối máy chủ hoặc lỗi cập nhật.'
              });
            });
        });
      });
      // Hỗ trợ lưu nhanh bằng phím tắt Ctrl + S (hoặc Cmd + S trên Mac) cho các Form Admin
      document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
          // Form được khai báo tường minh trên từng trang, tương tự cách một
          // Filament Action trỏ tới đúng form thay vì đoán theo thứ tự DOM.
          const mainForm = document.getElementById('admin-save-form');

          if (!mainForm) return;

          e.preventDefault();

          const saveAndCreateButton = mainForm.querySelector(
            '[type="submit"][name="submit_action"][value="save_and_create"]'
          );
          const regularSaveButton = Array.from(
            mainForm.querySelectorAll('button[type="submit"], input[type="submit"]')
          ).find((button) => button !== saveAndCreateButton);

          // Ctrl + Shift + S chỉ dùng "Lưu & Tạo mới" khi form có hỗ trợ.
          // Các form khác vẫn được lưu bình thường để phím tắt luôn an toàn.
          const submitButton = e.shiftKey && saveAndCreateButton
            ? saveAndCreateButton
            : (regularSaveButton || saveAndCreateButton);

          if (submitButton) mainForm.requestSubmit(submitButton);
        }
      });
    </script>
  </body>
  <!--end::Body-->
</html>
