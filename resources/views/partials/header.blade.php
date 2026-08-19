<div class="site-topbar hidden lg:block">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between py-2">
        <span>{{ $website['welcome'] }}</span>
        <div class="flex gap-3">
            @if($website['email'])<a href="mailto:{{ $website['email'] }}"><i class="fa-solid fa-envelope mr-1"></i>{{ $website['email'] }}</a>@endif
            @php($topbarPhones = $website['phones'] ?? [])
            @if(empty($topbarPhones) && $website['phone'])
                @php($topbarPhones = [['number' => $website['phone']]])
            @endif
            @foreach($topbarPhones as $topbarPhone)
                @if(filled($topbarPhone['number'] ?? null))<a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $topbarPhone['number']) }}"><i class="fa-solid fa-phone mr-1"></i>{{ $topbarPhone['number'] }}</a>@endif
            @endforeach
        </div>
    </div>
</div>

<div class="site-header-shell" data-site-header>
    <header class="site-header-main">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-2">
            <div class="flex items-center justify-between gap-3">
                <a class="site-logo no-underline" href="{{ route('home') }}" aria-label="Trang chủ {{ $website['name'] }}">
                    @if($siteAssets?->getFirstMediaUrl('logo'))
                        <img src="{{ $siteAssets->getFirstMediaUrl('logo') }}" alt="{{ $website['name'] }}" width="310" height="92">
                    @else
                        <span class="text-2xl font-extrabold text-primary">THT MEDIA VN</span>
                    @endif
                </a>
                <button class="mobile-trigger lg:hidden" type="button" data-mobile-menu-open aria-controls="mobileMenu" aria-label="Mở menu"><i class="fa-solid fa-bars"></i></button>
                <div class="site-navbar hidden lg:block" data-site-navbar>
                    <nav class="site-navbar__nav" aria-label="Điều hướng chính">
                        <ul class="site-navbar__list flex items-stretch justify-end">
                            @if($headerMenu?->items->isNotEmpty())
                                @foreach($headerMenu->items as $item)
                                    @include('partials.menu.header-item', ['item' => $item])
                                @endforeach
                            @else
                                <li class="site-navbar__item"><a class="site-navbar__link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Trang chủ</a></li>
                                <li class="site-navbar__item"><a class="site-navbar__link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">Giới thiệu</a></li>
                                <li class="site-navbar__item site-navbar__item--mega"><a class="site-navbar__link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}" aria-haspopup="true">Dịch vụ <i class="fa-solid fa-chevron-down ml-1 text-xs" aria-hidden="true"></i></a>@include('partials.menu.service-mega-menu')</li>
                                <li class="site-navbar__item"><a class="site-navbar__link {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}">Dự án</a></li>
                                <li class="site-navbar__item"><a class="site-navbar__link" href="{{ route('pricing') }}">Bảng giá</a></li>
                                <li class="site-navbar__item"><a class="site-navbar__link {{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}">Tin tức</a></li>
                                <li class="site-navbar__item"><a class="site-navbar__link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Liên hệ</a></li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>
</div>

<div class="mobile-drawer" id="mobileMenu" data-mobile-menu>
    <div class="mobile-drawer__backdrop" data-mobile-menu-backdrop></div>
    <div class="mobile-drawer__panel" data-mobile-menu-panel aria-hidden="true" aria-labelledby="mobileMenuLabel" tabindex="-1">
        <div class="mobile-drawer__header">
            <a class="mobile-drawer__brand" id="mobileMenuLabel" href="{{ route('home') }}" aria-label="Trang chủ {{ $website['name'] }}">
                @if($siteAssets?->getFirstMediaUrl('logo'))
                    <img src="{{ $siteAssets->getFirstMediaUrl('logo') }}" alt="{{ $website['name'] }}" width="310" height="92">
                @else
                    <span>THT MEDIA VN</span>
                @endif
            </a>
            <button type="button" class="mobile-drawer__close" data-mobile-menu-close aria-label="Đóng"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="mobile-drawer__body">
            <nav class="mobile-menu-list">
                @if($headerMenu?->items->isNotEmpty())
                    @include('partials.menu.mobile-items', ['items' => $headerMenu->items, 'idPrefix' => 'mobile-header'])
                @else
                    <a class="mobile-menu-list__link" href="{{ route('home') }}">Trang chủ</a>
                    <a class="mobile-menu-list__link" href="{{ route('about') }}">Giới thiệu</a>
                    <a class="mobile-menu-list__link" href="{{ route('services.index') }}">Dịch vụ</a>
                    <a class="mobile-menu-list__link" href="{{ route('projects.index') }}">Dự án</a>
                    <a class="mobile-menu-list__link" href="{{ route('pricing') }}">Bảng giá</a>
                    <a class="mobile-menu-list__link" href="{{ route('news.index') }}">Tin tức</a>
                    <a class="mobile-menu-list__link" href="{{ route('contact') }}">Liên hệ</a>
                @endif
            </nav>
            @php($mobilePhones = $website['phones'] ?? [])
            @if(empty($mobilePhones) && filled($website['phone'] ?? null))
                @php($mobilePhones = [['label' => 'Hotline chính', 'number' => $website['phone']]])
            @endif
            @php($mobilePhoneItems = collect($mobilePhones)->filter(fn (mixed $phone): bool => is_array($phone) && filled($phone['number'] ?? null))->values())
            @if($mobilePhoneItems->isNotEmpty())
                <div class="mobile-drawer__phones mt-6 rounded-2xl bg-soft p-4">
                    <div class="text-sm text-muted">Điện thoại</div>
                    <div class="mobile-drawer__phone-list">
                    @foreach($mobilePhoneItems as $mobilePhone)
                        @if(!$loop->first)<span class="mobile-drawer__phone-separator" aria-hidden="true"> - </span>@endif
                        <a class="font-bold text-primary" href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $mobilePhone['number']) }}">{{ $mobilePhone['number'] }}</a>
                    @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
