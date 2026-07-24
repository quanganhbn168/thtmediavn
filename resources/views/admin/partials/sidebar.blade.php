@php
  $adminUser = auth()->user();
  $normalizeResourceName = static function (string $routeName): string {
      if (str_ends_with($routeName, '.index')) {
          return \Illuminate\Support\Str::replaceLast('.index', '', $routeName);
      }

      return $routeName;
  };
  $canSeeRoute = static function (?string $routeName) use ($adminUser): bool {
      if (! $routeName || $routeName === '#' || ! Route::has($routeName)) {
          return false;
      }

      $permissions = \App\Support\AdminPermission::requiredForRouteName($routeName);

      return $permissions !== null && \App\Support\AdminPermission::can($adminUser, $permissions);
  };

  $visibleMenu = [];
  $pendingHeader = null;
  $seenRouteLinks = [];
  $seenChildRoutes = [];
  $adminAssets = \App\Models\SiteAsset::current();
  $adminLogo = $adminAssets->getFirstMediaUrl('logo');

  foreach (config('sidebar.menu', []) as $menuItem) {
      if (($menuItem['type'] ?? 'link') === 'header') {
          $pendingHeader = $menuItem;
          continue;
      }

      if (! empty($menuItem['children'])) {
          $normalizedChildren = [];
          $menuItem['children'] = array_values(array_filter(
              $menuItem['children'],
              function (array $child) use ($canSeeRoute, &$seenChildRoutes, &$normalizedChildren): bool {
                  $childRoute = $child['route'] ?? null;
                  if (! $canSeeRoute($childRoute)) {
                      return false;
                  }
                  if ($childRoute && isset($seenChildRoutes[$childRoute])) {
                      return false;
                  }
                  if ($childRoute) {
                      $seenChildRoutes[$childRoute] = true;
                      $normalizedChildren[] = $child;
                  }
                  return true;
              },
          ));
          $menuItem['children'] = $normalizedChildren;
          $isVisible = $menuItem['children'] !== [];
      } else {
          $routeName = $menuItem['route'] ?? null;
          if (isset($seenRouteLinks[$routeName]) && $routeName) {
              $isVisible = false;
          } else {
              $isVisible = $canSeeRoute($routeName);
              if ($isVisible && $routeName) {
                  $seenRouteLinks[$routeName] = true;
              }
          }
      }

      if (! $isVisible) {
          continue;
      }

      if ($pendingHeader) {
          $visibleMenu[] = $pendingHeader;
          $pendingHeader = null;
      }

      $visibleMenu[] = $menuItem;
  }
@endphp

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <!--begin::Sidebar Brand-->
  <div class="sidebar-brand">
    <!--begin::Brand Link-->
    <a href="{{ route('admin.dashboard') }}" class="brand-link admin-brand-link" aria-label="Trang quản trị">
      @if($adminLogo)
        <!--begin::Brand Image-->
        <img
          src="{{ $adminLogo }}"
          alt="Logo quản trị"
          class="brand-image admin-brand-logo"
        />
        <!--end::Brand Image-->
      @else
        <span class="admin-brand-fallback" aria-hidden="true"><i class="bi bi-grid-1x2-fill"></i></span>
      @endif
    </a>
    <!--end::Brand Link-->
  </div>
  <!--end::Sidebar Brand-->

  <!--begin::Sidebar Wrapper-->
  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <!--begin::Sidebar Menu-->
      <ul
        class="nav sidebar-menu flex-column"
        data-lte-toggle="treeview"
        role="navigation"
        aria-label="Main navigation"
        data-accordion="false"
        id="navigation"
      >
        @foreach($visibleMenu as $item)
          @if(($item['type'] ?? 'link') === 'header')
            <li class="nav-header">{{ $item['label'] }}</li>
          @elseif(($item['type'] ?? 'link') === 'link')
              @if(isset($item['children']) && is_array($item['children']))
              @php
                $hasActiveChild = false;
                foreach($item['children'] as $child) {
                    if(isset($child['route']) && $child['route'] !== '#') {
                        $routeName = $child['route'];
                        if (str_ends_with($routeName, '.index')) {
                            $routeName = $normalizeResourceName($routeName) . '.*';
                        }

                        if (request()->routeIs($routeName)) {
                            $hasActiveChild = true;
                            break;
                        }
                    }
                }
              @endphp
              <li class="nav-item {{ $hasActiveChild ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $hasActiveChild ? 'active' : '' }}">
                  <i class="nav-icon {{ $item['icon'] ?? 'bi bi-circle' }}"></i>
                  <p>
                    {{ $item['label'] }}
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  @foreach($item['children'] as $child)
                    @php
                        $childHref = (isset($child['route']) && $child['route'] !== '#') && Route::has($child['route']) ? route($child['route']) : '#';
                      $isChildActive = false;
                      if (isset($child['route']) && $child['route'] !== '#') {
                          $childRoute = $child['route'];
                          if (str_ends_with($childRoute, '.index')) {
                              $resourceName = $normalizeResourceName($childRoute);
                              $isChildActive = request()->routeIs($resourceName . '.*');
                          } else {
                              $isChildActive = request()->routeIs($childRoute);
                          }
                      }
                    @endphp
                    <li class="nav-item">
                      <a href="{{ $childHref }}" class="nav-link {{ $isChildActive ? 'active' : '' }}">
                        <i class="nav-icon {{ $child['icon'] ?? 'bi bi-circle' }}"></i>
                        <p>{{ $child['label'] }}</p>
                      </a>
                    </li>
                  @endforeach
                </ul>
              </li>
            @else
              @php
                $href = (isset($item['route']) && $item['route'] !== '#') && Route::has($item['route']) ? route($item['route']) : '#';
                $isActive = false;
                if (isset($item['route']) && $item['route'] !== '#') {
                    $isActive = request()->routeIs($item['route']);
                    if (!$isActive && str_ends_with($item['route'], '.index')) {
                        $resourceName = $normalizeResourceName($item['route']);
                        $isActive = request()->routeIs($resourceName . '.*');
                    }
                }
              @endphp
              <li class="nav-item">
                <a href="{{ $href }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                  <i class="nav-icon {{ $item['icon'] ?? 'bi bi-circle' }}"></i>
                  <p>{{ $item['label'] }}</p>
                </a>
              </li>
            @endif
          @endif
        @endforeach
      </ul>
      <!--end::Sidebar Menu-->
    </nav>
  </div>
  <!--end::Sidebar Wrapper-->
</aside>
