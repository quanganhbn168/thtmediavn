<div class="site-topbar d-none d-lg-block">
    <div class="container d-flex align-items-center justify-content-between py-2">
        <span>{{ $website['welcome'] }}</span>
        <div>
            <a href="{{ route('contact') }}"><i class="bi bi-geo-alt me-1"></i>Hệ thống cửa hàng</a>
            <span class="topbar-separator">|</span>
            @auth<a href="{{ route('account.index') }}">{{ auth()->user()->name }}</a><span class="topbar-separator">|</span><form class="d-inline" action="{{ route('logout') }}" method="post">@csrf<button class="btn btn-link text-reset p-0 border-0 align-baseline">Đăng xuất</button></form>@else<a href="{{ route('register') }}">Đăng ký</a><span class="topbar-separator">|</span><a href="{{ route('login') }}">Đăng nhập</a>@endauth
        </div>
    </div>
</div>

<header class="site-header-main d-none d-lg-block">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-xl-3 col-lg-3">
                <a class="site-logo text-decoration-none" href="{{ route('home') }}" aria-label="Trang chủ {{ $website['name'] }}">
                    @if($siteAssets?->getFirstMediaUrl('logo'))
                        <img src="{{ $siteAssets->getFirstMediaUrl('logo') }}" alt="{{ $website['name'] }}" width="310" height="92">
                    @else
                        <span class="h3 fw-black text-primary mb-0">RHEA SKINLAB</span>
                    @endif
                </a>
            </div>
            <div class="col-xl-5 col-lg-5">
                <form class="header-search" action="{{ route('catalog') }}" method="get" role="search">
                    <label class="visually-hidden" for="desktop-search">Tìm kiếm sản phẩm</label>
                    <input id="desktop-search" class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Bạn muốn tìm gì?">
                    <button class="btn btn-primary" type="submit" aria-label="Tìm kiếm"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div class="col-xl-4 col-lg-4">
                <div class="d-flex align-items-center justify-content-end gap-2">
                    <a class="hotline-box d-flex align-items-center gap-2" href="tel:{{ preg_replace('/[^0-9+]/', '', $website['phone']) }}">
                        <span class="hotline-icon"><i class="bi bi-telephone"></i></span>
                        <span class="d-flex flex-column">
                            <small>Hotline tư vấn</small>
                            <strong>{{ $website['phone'] }}</strong>
                        </span>
                    </a>
                    <a class="header-action" href="{{ route('wishlist') }}">
                        <span class="header-action-icon"><i class="bi bi-heart"></i></span>
                        <span class="header-action-label">Yêu thích</span>
                        <span class="badge-count" data-wishlist-count>{{ $wishlistCount ?? 0 }}</span>
                    </a>
                    <a class="header-action" href="{{ route('cart') }}">
                        <span class="header-action-icon"><i class="bi bi-bag"></i></span>
                        <span class="header-action-label">Giỏ hàng</span>
                        <span class="badge-count" data-cart-count>{{ $cartCount ?? 0 }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="mobile-header d-lg-none">
    <div class="container d-flex align-items-center justify-content-between gap-2">
        <button class="mobile-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="Mở menu">
            <i class="bi bi-list"></i>
        </button>
        <a class="site-logo text-decoration-none" href="{{ route('home') }}">
            @if($siteAssets?->getFirstMediaUrl('logo'))
                <img src="{{ $siteAssets->getFirstMediaUrl('logo') }}" alt="{{ $website['name'] }}" width="310" height="92">
            @else
                <span class="h5 fw-black text-primary mb-0">RHEA SKINLAB</span>
            @endif
        </a>
        <div class="d-flex gap-2">
            <button class="mobile-trigger" type="button" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="Tìm kiếm">
                <i class="bi bi-search"></i>
            </button>
            <a class="mobile-trigger" href="{{ route('cart') }}" aria-label="Giỏ hàng">
                <i class="bi bi-bag"></i>
                <span class="badge-count" data-cart-count>{{ $cartCount ?? 0 }}</span>
            </a>
        </div>
    </div>
</div>

<div class="site-navbar d-none d-lg-block" data-site-navbar>
    <div class="container">
        <nav class="navbar navbar-expand-lg" aria-label="Điều hướng chính">
            <ul class="navbar-nav align-items-stretch w-100">
                <li class="nav-item dropdown position-static">
                    <a class="nav-link navbar-category-trigger" href="{{ route('catalog') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-grid me-2"></i>Danh mục sản phẩm
                    </a>
                    <div class="dropdown-menu mega-menu category-mega-menu" data-mega-menu>
                        @if($megaMenu?->items->isNotEmpty())
                            <div class="mega-category-layout">
                                <div class="mega-category-tabs" role="tablist" aria-label="{{ $megaMenu->getTranslation('name', 'vi') }}">
                                    @foreach($megaMenu->items as $index => $group)
                                        <a class="mega-category-tab {{ $index === 0 ? 'is-active' : '' }}" href="{{ $group->href }}" data-mega-tab="menu-{{ $group->id }}" target="{{ $group->target ?: '_self' }}" @if($group->target === '_blank') rel="noopener" @endif>
                                            <span>@if($group->icon)<i class="{{ $group->icon }} me-2"></i>@endif{{ $group->getTranslation('title', 'vi') }}</span>
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    @endforeach
                                    @foreach($attributeMenuGroups as $attribute)
                                        <a class="mega-category-tab" href="{{ route('catalog') }}" data-mega-tab="attribute-{{ $attribute->id }}">
                                            <span>{{ $attribute->name }}</span><i class="bi bi-chevron-right"></i>
                                        </a>
                                    @endforeach
                                </div>
                                <div class="mega-category-panels">
                                    @foreach($megaMenu->items as $index => $group)
                                        <div class="mega-category-panel {{ $index === 0 ? 'is-active' : '' }}" data-mega-panel="menu-{{ $group->id }}">
                                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                                <a class="mega-panel-title" href="{{ $group->href }}" target="{{ $group->target ?: '_self' }}" @if($group->target === '_blank') rel="noopener" @endif>{{ $group->getTranslation('title', 'vi') }}</a>
                                                <a class="mega-panel-all" href="{{ $group->href }}">Xem tất cả <i class="bi bi-arrow-right"></i></a>
                                            </div>
                                            @if($group->childrenRecursive->isNotEmpty())
                                                <div class="mega-child-grid">
                                                    @foreach($group->childrenRecursive as $child)
                                                        <div class="mega-child-group">
                                                            <a class="mega-child-title" href="{{ $child->href }}" target="{{ $child->target ?: '_self' }}" @if($child->target === '_blank') rel="noopener" @endif>{{ $child->getTranslation('title', 'vi') }}</a>
                                                            @foreach($child->childrenRecursive as $grandchild)
                                                                <a class="mega-link" href="{{ $grandchild->href }}" target="{{ $grandchild->target ?: '_self' }}" @if($grandchild->target === '_blank') rel="noopener" @endif>{{ $grandchild->getTranslation('title', 'vi') }}</a>
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted mb-0">Khám phá toàn bộ nội dung trong {{ mb_strtolower($group->getTranslation('title', 'vi')) }}.</p>
                                            @endif
                                        </div>
                                    @endforeach
                                    @foreach($attributeMenuGroups as $attribute)
                                        <div class="mega-category-panel" data-mega-panel="attribute-{{ $attribute->id }}">
                                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                                <a class="mega-panel-title" href="{{ route('catalog') }}">{{ $attribute->name }}</a>
                                                <a class="mega-panel-all" href="{{ route('catalog') }}">Xem tất cả <i class="bi bi-arrow-right"></i></a>
                                            </div>
                                            <div class="mega-child-grid">
                                                @foreach($attribute->values as $value)
                                                    <div class="mega-child-group"><a class="mega-child-title" href="{{ route('catalog', ['attribute_values' => [$attribute->id => [$value->id]]]) }}">{{ $value->value }}</a></div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @elseif($siteNavigation->isNotEmpty())
                            <div class="mega-category-layout">
                                <div class="mega-category-tabs" role="tablist" aria-label="Danh mục sản phẩm">
                                    @foreach($siteNavigation as $index => $group)
                                        <a class="mega-category-tab {{ $index === 0 ? 'is-active' : '' }}" href="{{ route('content.show', ['domain' => 'danh-muc', 'slug' => $group->slug]) }}" data-mega-tab="category-{{ $group->id }}">
                                            <span>{{ $group->name }}</span><i class="bi bi-chevron-right"></i>
                                        </a>
                                    @endforeach
                                    @if($siteBrands->isNotEmpty())
                                        <a class="mega-category-tab" href="{{ route('catalog') }}" data-mega-tab="special-brands">
                                            <span>Theo thương hiệu</span><i class="bi bi-chevron-right"></i>
                                        </a>
                                    @endif
                                    @foreach($attributeMenuGroups as $attribute)
                                        <a class="mega-category-tab" href="{{ route('catalog') }}" data-mega-tab="attribute-{{ $attribute->id }}">
                                            <span>{{ $attribute->name }}</span><i class="bi bi-chevron-right"></i>
                                        </a>
                                    @endforeach
                                </div>
                                <div class="mega-category-panels">
                                    @foreach($siteNavigation as $index => $group)
                                        <div class="mega-category-panel {{ $index === 0 ? 'is-active' : '' }}" data-mega-panel="category-{{ $group->id }}">
                                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                                <a class="mega-panel-title" href="{{ route('content.show', ['domain' => 'danh-muc', 'slug' => $group->slug]) }}">{{ $group->name }}</a>
                                                <a class="mega-panel-all" href="{{ route('content.show', ['domain' => 'danh-muc', 'slug' => $group->slug]) }}">Xem tất cả <i class="bi bi-arrow-right"></i></a>
                                            </div>
                                            <div class="mega-child-grid">
                                                @forelse($group->children as $item)
                                                    <div class="mega-child-group"><a class="mega-child-title" href="{{ route('content.show', ['domain' => 'danh-muc', 'slug' => $item->slug]) }}">{{ $item->name }}</a></div>
                                                @empty
                                                    <p class="text-muted mb-0">Sản phẩm trong danh mục đang được cập nhật.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($siteBrands->isNotEmpty())
                                        <div class="mega-category-panel" data-mega-panel="special-brands">
                                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                                <a class="mega-panel-title" href="{{ route('catalog') }}">Theo thương hiệu</a>
                                                <a class="mega-panel-all" href="{{ route('catalog') }}">Xem tất cả <i class="bi bi-arrow-right"></i></a>
                                            </div>
                                            <div class="mega-child-grid">
                                                @foreach($siteBrands as $brand)
                                                    <div class="mega-child-group"><a class="mega-child-title" href="{{ route('catalog', ['brand' => $brand->slug]) }}">{{ $brand->name }}</a></div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @foreach($attributeMenuGroups as $attribute)
                                        <div class="mega-category-panel" data-mega-panel="attribute-{{ $attribute->id }}">
                                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                                <a class="mega-panel-title" href="{{ route('catalog') }}">{{ $attribute->name }}</a>
                                                <a class="mega-panel-all" href="{{ route('catalog') }}">Xem tất cả <i class="bi bi-arrow-right"></i></a>
                                            </div>
                                            <div class="mega-child-grid">
                                                @foreach($attribute->values as $value)
                                                    <div class="mega-child-group"><a class="mega-child-title" href="{{ route('catalog', ['attribute_values' => [$attribute->id => [$value->id]]]) }}">{{ $value->value }}</a></div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="p-4 text-muted">Danh mục đang được cập nhật.</div>
                        @endif
                    </div>
                </li>
                @if($headerMenu?->items->isNotEmpty())
                    @foreach($headerMenu->items as $item)
                        @include('partials.menu.header-item', ['item' => $item])
                    @endforeach
                @else
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">Giới thiệu</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('catalog', 'product.show', 'products.by-category') ? 'active' : '' }}" href="{{ route('catalog') }}">Sản phẩm</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}">Tin tức</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Liên hệ</a></li>
                @endif
                @if(request()->routeIs('home'))
                    <li class="nav-item ms-auto"><a class="nav-link text-primary" href="#flash-sale"><i class="bi bi-lightning-charge-fill me-1"></i>Flash Sale</a></li>
                @endif
            </ul>
        </nav>
    </div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title h5" id="mobileMenuLabel">Danh mục</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="mobile-menu-list">
            @if($headerMenu?->items->isNotEmpty())
                @include('partials.menu.mobile-items', ['items' => $headerMenu->items, 'idPrefix' => 'mobile-header'])
            @else
                <a class="nav-link" href="{{ route('home') }}">Trang chủ</a>
                <a class="nav-link" href="{{ route('about') }}">Giới thiệu</a>
                <a class="nav-link" href="{{ route('catalog') }}">Sản phẩm</a>
                <a class="nav-link" href="{{ route('news.index') }}">Tin tức</a>
                <a class="nav-link" href="{{ route('contact') }}">Liên hệ</a>
            @endif

            <div class="mobile-menu-heading">Danh mục sản phẩm</div>
            @if($megaMenu?->items->isNotEmpty())
                @include('partials.menu.mobile-items', ['items' => $megaMenu->items, 'idPrefix' => 'mobile-mega'])
            @else
                @foreach($siteNavigation as $group)
                    <a class="nav-link" href="{{ route('content.show', ['domain' => 'danh-muc', 'slug' => $group->slug]) }}">{{ $group->name }}</a>
                @endforeach
            @endif
        </nav>
        <div class="mt-4 p-3 rounded-4 bg-soft">
            <div class="small text-muted">Tư vấn mua hàng</div>
            <a class="fw-bold text-primary" href="tel:{{ preg_replace('/[^0-9+]/', '', $website['phone']) }}">{{ $website['phone'] }}</a>
        </div>
    </div>
</div>
