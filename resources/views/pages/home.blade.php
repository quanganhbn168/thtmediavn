@extends('layouts.master')

@section('title', $website['seo_title'])
@section('meta_description', 'Sản phẩm chăm sóc da, hóa mỹ phẩm và dược mỹ phẩm chính hãng tại RHEA SKINLAB.')

@section('content')
<section class="hero-section">
    <div class="container">
        <div class="row g-3">
            <div class="col-lg-8">
                <a class="hero-main d-block" href="{{ route('catalog') }}">
                    <img src="{{ asset('assets/images/banners/hero-main.svg') }}" alt="RHEA SKINLAB" width="1400" height="560">
                </a>
            </div>
            <div class="col-lg-4">
                <div class="row g-3 h-100">
                    <div class="col-6 col-lg-12">
                        <a class="hero-side-card d-block" href="{{ route('products.by-category', ['category' => 'serum']) }}">
                            <img src="{{ asset('assets/images/banners/hero-side-1.svg') }}" alt="Routine chăm sóc da" width="680" height="265">
                        </a>
                    </div>
                    <div class="col-6 col-lg-12">
                        <a class="hero-side-card d-block" href="{{ route('products.by-category', ['category' => 'trang-diem']) }}">
                            <img src="{{ asset('assets/images/banners/hero-side-2.svg') }}" alt="Bộ sưu tập trang điểm" width="680" height="265">
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="hero-seo-title">{{ $website['name'] }} ra đời với mong muốn mang đến những sản phẩm làm đẹp {{ mb_strtolower($website['tagline']) }}.</h1>
    </div>
</section>

<section class="section-space-sm">
    <div class="container">
        <x-section-heading
            title="Danh mục nổi bật"
            :href="route('catalog')"
        />
        <div class="category-scroller">
            @foreach($categories as $category)
                <a class="category-card" href="{{ route('products.by-category', ['category' => $category['slug']]) }}">
                    <span class="category-image">
                        <img src="{{ $category['image'] }}" alt="{{ $category['title'] }}" loading="lazy" width="300" height="300">
                    </span>
                    <span class="category-title">{{ $category['title'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="section-space-sm pt-0" id="flash-sale">
    <div class="container">
        <div class="flash-sale-wrap">
            <div class="flash-sale-head d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                <div>
                    <h2 class="flash-sale-title"><i class="bi bi-lightning-charge-fill"></i> Flash Sale</h2>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="d-none d-sm-inline small fw-bold">Kết thúc sau</span>
                    <div class="countdown" data-countdown @if($flashSale) data-deadline="{{ $flashSale->ends_at->toIso8601String() }}" @endif>
                        <div class="countdown-unit"><strong data-days>00</strong><span>Ngày</span></div>
                        <span class="countdown-separator">:</span>
                        <div class="countdown-unit"><strong data-hours>00</strong><span>Giờ</span></div>
                        <span class="countdown-separator">:</span>
                        <div class="countdown-unit"><strong data-minutes>00</strong><span>Phút</span></div>
                        <span class="countdown-separator">:</span>
                        <div class="countdown-unit"><strong data-seconds>00</strong><span>Giây</span></div>
                    </div>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-5 g-3 flash-products">
                @foreach($flashProducts as $product)
                    <div class="col"><x-product-card :product="$product" :show-sold="true" /></div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="section-space-sm bg-soft">
    <div class="container">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="voucher-card h-100">
                    <div class="voucher-side">FREE</div>
                    <div class="voucher-cut"></div>
                    <div class="voucher-content d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="voucher-code">NHẬP MÃ: FREESHIP</div>
                            <div class="voucher-description">Miễn phí vận chuyển theo điều kiện đơn hàng.</div>
                            <button class="btn btn-link text-primary p-0 mt-1 small fw-bold" type="button" data-bs-toggle="modal" data-bs-target="#voucherConditionModal">Xem điều kiện</button>
                        </div>
                        <button class="btn btn-outline-primary btn-sm flex-shrink-0" type="button" data-copy-code="FREESHIP"><i class="bi bi-copy me-1"></i>Sao chép</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="voucher-card h-100">
                    <div class="voucher-side">-10%</div>
                    <div class="voucher-cut"></div>
                    <div class="voucher-content d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="voucher-code">NHẬP MÃ: MTD10</div>
                            <div class="voucher-description">Giảm 10% cho đơn hàng đầu tiên.</div>
                            <div class="small text-muted mt-1">Tối đa 100.000₫</div>
                        </div>
                        <button class="btn btn-outline-primary btn-sm flex-shrink-0" type="button" data-copy-code="MTD10"><i class="bi bi-copy me-1"></i>Sao chép</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="product-section-shell">
            <div class="product-section-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="product-section-title">Chăm sóc mặt</h2>
                </div>
                <ul class="nav product-tabs" id="faceTabs" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#face-cleansing" type="button" role="tab">Tẩy trang</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#face-sunscreen" type="button" role="tab">Chống nắng</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#face-moisture" type="button" role="tab">Kem dưỡng</button></li>
                </ul>
            </div>
            <div class="product-section-content tab-content">
                @foreach([
                    ['id' => 'face-cleansing', 'items' => $faceProducts->take(5)],
                    ['id' => 'face-sunscreen', 'items' => $faceProducts->slice(1, 5)],
                    ['id' => 'face-moisture', 'items' => $faceProducts->slice(3, 5)],
                ] as $tabIndex => $tab)
                    <div class="tab-pane fade {{ $tabIndex === 0 ? 'show active' : '' }}" id="{{ $tab['id'] }}" role="tabpanel">
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
                            @foreach($tab['items'] as $product)
                                <div class="col"><x-product-card :product="$product" /></div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <div class="text-center mt-4"><a class="btn btn-outline-primary" href="{{ route('products.by-category', ['category' => 'cham-soc-mat']) }}">Xem sản phẩm</a></div>
            </div>
        </div>
    </div>
</section>

<section class="section-space pt-0">
    <div class="container">
        <div class="product-section-shell">
            <div class="product-section-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="product-section-title">Trang điểm</h2>
                </div>
                <ul class="nav product-tabs" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#makeup-lip" type="button" role="tab">Son</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#makeup-base" type="button" role="tab">Nền & cushion</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#makeup-eye" type="button" role="tab">Trang điểm mắt</button></li>
                </ul>
            </div>
            <div class="product-section-content tab-content">
                @foreach([
                    ['id' => 'makeup-lip', 'items' => $makeupProducts->take(5)],
                    ['id' => 'makeup-base', 'items' => $makeupProducts->slice(1, 5)],
                    ['id' => 'makeup-eye', 'items' => $makeupProducts->slice(2, 5)],
                ] as $tabIndex => $tab)
                    <div class="tab-pane fade {{ $tabIndex === 0 ? 'show active' : '' }}" id="{{ $tab['id'] }}" role="tabpanel">
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
                            @foreach($tab['items'] as $product)
                                <div class="col"><x-product-card :product="$product" /></div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <div class="text-center mt-4"><a class="btn btn-outline-primary" href="{{ route('products.by-category', ['category' => 'trang-diem']) }}">Xem sản phẩm</a></div>
            </div>
        </div>
    </div>
</section>

<section class="section-space pt-0 bg-soft">
    <div class="container">
        <div class="product-section-shell">
            <div class="product-section-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="product-section-title">Chăm sóc cơ thể</h2>
                </div>
                <ul class="nav product-tabs" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#body-wash" type="button" role="tab">Sữa tắm</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#body-lotion" type="button" role="tab">Dưỡng thể</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#body-hair" type="button" role="tab">Dầu gội</button></li>
                </ul>
            </div>
            <div class="product-section-content tab-content">
                @foreach([
                    ['id' => 'body-wash', 'items' => $bodyProducts->take(5)],
                    ['id' => 'body-lotion', 'items' => $bodyProducts->slice(1, 5)],
                    ['id' => 'body-hair', 'items' => $bodyProducts->slice(2, 5)],
                ] as $tabIndex => $tab)
                    <div class="tab-pane fade {{ $tabIndex === 0 ? 'show active' : '' }}" id="{{ $tab['id'] }}" role="tabpanel">
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
                            @foreach($tab['items'] as $product)
                                <div class="col"><x-product-card :product="$product" /></div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <div class="text-center mt-4"><a class="btn btn-outline-primary" href="{{ route('products.by-category', ['category' => 'cham-soc-co-the']) }}">Xem sản phẩm</a></div>
            </div>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <x-section-heading
            title="Thương hiệu nổi bật"
        />
        <div class="brand-marquee" role="region" aria-label="Thương hiệu nổi bật">
            <div class="brand-marquee__track">
                @foreach([false, true] as $isDuplicate)
                    <div class="brand-marquee__group" @if($isDuplicate) aria-hidden="true" @endif>
                        @foreach($brands as $brand)
                            @php
                                $brandLogoUrl = filled($brand->logo)
                                    ? (Str::startsWith((string) $brand->logo, ['http://', 'https://'])
                                        ? $brand->logo
                                        : asset(ltrim((string) $brand->logo, '/')))
                                    : null;
                            @endphp
                            <a
                                class="brand-card"
                                href="{{ route('catalog', ['brand' => $brand->slug]) }}"
                                @if($isDuplicate) tabindex="-1" @endif
                            >
                                @if($brandLogoUrl)
                                    <img class="brand-card__logo" src="{{ $brandLogoUrl }}" alt="{{ $brand->name }}" loading="lazy">
                                @else
                                    {{ $brand->name }}
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="section-space bg-soft">
    <div class="container">
        <x-section-heading
            title="Tin nổi bật"
            :href="route('news.index')"
        />
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 g-xl-4">
            @foreach($newsItems as $article)
                <div class="col">
                    <article class="news-card">
                        <a class="news-card-image d-block" href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">
                            <img src="{{ str_starts_with($article['image'], 'http') || str_starts_with($article['image'], '/') ? $article['image'] : asset('assets/images/news/' . $article['image']) }}" alt="{{ $article['title'] }}" loading="lazy" width="800" height="500">
                        </a>
                        <div class="news-card-body">
                            <div class="news-date"><i class="bi bi-calendar3 me-1"></i>{{ $article['date'] }}</div>
                            <h3 class="news-title"><a href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">{{ $article['title'] }}</a></h3>
                            <p class="news-excerpt">{{ $article['excerpt'] }}</p>
                            <a class="news-readmore" href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">Đọc tiếp <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>

<div class="modal fade" id="voucherConditionModal" tabindex="-1" aria-labelledby="voucherConditionTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0">
                <h2 class="modal-title h5 fw-bold" id="voucherConditionTitle">Thông tin voucher</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="p-3 rounded-3 bg-soft">
                    <div><strong>Mã khuyến mãi:</strong> FREESHIP</div>
                    <div class="mt-2"><strong>Điều kiện:</strong> Áp dụng theo cấu hình vận chuyển của cửa hàng. Thay nội dung này bằng chính sách thực tế.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
