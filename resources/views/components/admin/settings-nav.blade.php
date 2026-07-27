@php
    $items = [
        'admin.settings.general' => ['icon' => 'bi-sliders', 'label' => 'Cài đặt chung'],
        'admin.settings.contact' => ['icon' => 'bi-telephone', 'label' => 'Liên hệ'],
        'admin.settings.contact-channels.index' => ['icon' => 'bi-person-lines-fill', 'label' => 'Danh bạ'],
        'admin.settings.payment' => ['icon' => 'bi-qr-code', 'label' => 'Thanh toán'],
        'admin.settings.seo' => ['icon' => 'bi-search', 'label' => 'SEO'],
        'admin.settings.homepage' => ['icon' => 'bi-house', 'label' => 'Trang chủ'],
        'admin.settings.menu' => ['icon' => 'bi-menu-button-wide', 'label' => 'Menu'],
        'admin.settings.about' => ['icon' => 'bi-info-circle', 'label' => 'Giới thiệu'],
        'admin.settings.media' => ['icon' => 'bi-images', 'label' => 'Media'],
    ];
@endphp

<nav class="admin-settings-nav mb-3" aria-label="Điều hướng cài đặt">
    @foreach($items as $routeName => $item)
        @php $isActive = request()->routeIs($routeName) || ($routeName === 'admin.settings.contact-channels.index' && request()->routeIs('admin.settings.contact-channels.*')); @endphp
        <a href="{{ route($routeName) }}"
           class="admin-settings-nav__item {{ $isActive ? 'is-active' : '' }}"
           @if($isActive) aria-current="page" @endif>
            <i class="bi {{ $item['icon'] }}"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
