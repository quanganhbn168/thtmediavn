@extends('layouts.master')

@section('title', $website['seo_title'])
@section('meta_description', $website['seo_description'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
@endpush

@section('content')
@php
    $heroItems = $heroSlider?->items
        ?->filter(fn ($item) => filled($item->getFirstMediaUrl('slide_image')))
        ->values() ?? collect();
    $heroFallbackImage = $siteAssets?->getFirstMediaUrl('default_promotion_banner');
    $homepageSections = collect($homepageSettings?->homepage_sections ?? ['categories', 'flash_sale', 'featured_products', 'brands', 'testimonials', 'posts']);
    $homeCtaImage = $homeCta?->getFirstMediaUrl('slide_image');
    $homeCtaTitle = $homeCta?->getTranslation('title', 'vi');
    $homeCtaSubTitle = $homeCta?->getTranslation('sub_title', 'vi');
    $homeCtaButtons = collect($homeCta?->buttons ?? [])
        ->filter(fn ($button) => filled(data_get($button, 'text.vi')) && filled(data_get($button, 'link')))
        ->values();
    $homeAdviceLead = $homePosts->first();
    $homeAdviceSlides = $homePosts->slice(1)->values()->chunk(3);
@endphp
<section class="home-hero" aria-label="Nội dung nổi bật">
    @if($heroItems->isNotEmpty())
        <div class="swiper home-hero-swiper" data-home-hero-swiper data-slide-count="{{ $heroItems->count() }}">
            <div class="swiper-wrapper">
                @foreach($heroItems as $index => $item)
                    @php
                        $title = $item->getTranslation('title', 'vi');
                        $subTitle = $item->getTranslation('sub_title', 'vi');
                        $buttons = collect($item->buttons ?? [])->filter(fn ($button) => filled(data_get($button, 'text.vi')) && filled(data_get($button, 'link')));
                    @endphp
                    <div class="swiper-slide">
                        <img
                            class="home-hero-image"
                            src="{{ $item->getFirstMediaUrl('slide_image') }}"
                            alt="{{ $title ?: $website['name'] }}"
                            width="1920"
                            height="720"
                            @if($index === 0) fetchpriority="high" @else loading="lazy" @endif
                        >
                        @if($title || $subTitle || $buttons->isNotEmpty())
                            <div class="home-hero-shade"></div>
                            <div class="home-hero-caption">
                                @if($title)<h2>{{ $title }}</h2>@endif
                                @if($subTitle)<p>{{ $subTitle }}</p>@endif
                                @if($buttons->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($buttons as $buttonIndex => $button)
                                            @php
                                                $link = data_get($button, 'link');
                                                $isExternal = str_starts_with($link, 'http://') || str_starts_with($link, 'https://');
                                                $href = $isExternal || str_starts_with($link, '#') ? $link : url($link);
                                            @endphp
                                            <a class="btn {{ $buttonIndex === 0 ? 'btn-primary' : 'btn-light' }}" href="{{ $href }}" @if($isExternal) target="_blank" rel="noopener" @endif>
                                                {{ data_get($button, 'text.vi') }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($heroItems->count() > 1)
                <div class="swiper-pagination"></div>
                <button class="home-hero-prev" type="button" aria-label="Slide trước"><i class="bi bi-chevron-left"></i></button>
                <button class="home-hero-next" type="button" aria-label="Slide sau"><i class="bi bi-chevron-right"></i></button>
            @endif
        </div>
    @else
        <div class="home-hero-fallback">
            @if($heroFallbackImage)
                <img class="home-hero-image" src="{{ $heroFallbackImage }}" alt="{{ $website['name'] }}" width="1920" height="720" fetchpriority="high">
            @endif
            <div class="home-hero-shade"></div>
            <div class="home-hero-caption">
                <h1>{{ $website['name'] }}</h1>
                <p>{{ $website['tagline'] }}</p>
                <a class="btn btn-primary" href="{{ route('catalog') }}">Khám phá sản phẩm</a>
            </div>
        </div>
    @endif
</section>

@if($coreValues->isNotEmpty())
<section class="home-values-section" aria-label="Giá trị cốt lõi">
    <div class="container">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-5 g-3">
            @foreach($coreValues as $value)
                <div class="col">
                    <article class="home-value-card">
                        <span class="home-value-icon" aria-hidden="true"><i class="bi {{ $value['icon'] }}"></i></span>
                        <div>
                            <h3>{{ $value['title'] }}</h3>
                            <p>{{ $value['description'] }}</p>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($homepageSections->contains('categories') && $categories->isNotEmpty())
<section class="section-space-sm home-categories-section">
    <div class="container">
        <div class="home-categories-panel">
            <x-section-heading
                :title="data_get($homepageSettings?->homepage_section_titles, 'categories.vi', 'Danh mục sản phẩm')"
                :href="route('catalog')"
            />
            <div class="category-scroller">
                @foreach($categories as $category)
                    <a class="category-card" href="{{ route('content.show', ['domain' => 'danh-muc', 'slug' => $category['slug']]) }}">
                        <span class="category-image">
                            <img src="{{ $category['image'] }}" alt="{{ $category['title'] }}" loading="lazy" width="300" height="300">
                        </span>
                        <span class="category-title">{{ $category['title'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

@if($homepageSections->contains('featured_products') && $featuredProducts->isNotEmpty())
<section class="section-space-sm pt-0 home-featured-products">
    <div class="container">
        <x-section-heading
            :title="data_get($homepageSettings?->homepage_section_titles, 'featured_products.vi', 'Sản phẩm nổi bật')"
            :href="route('catalog')"
        />
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
            @foreach($featuredProducts as $product)
                <div class="col"><x-product-card :product="$product" /></div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($homepageSections->contains('flash_sale') && $flashSale && $flashProducts->isNotEmpty())
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
            <div class="swiper flash-sale-swiper" data-flash-sale-swiper data-slide-count="{{ $flashProducts->count() }}">
                <div class="swiper-wrapper">
                    @foreach($flashProducts as $product)
                        <div class="swiper-slide"><x-product-card :product="$product" :show-sold="true" /></div>
                    @endforeach
                </div>
                @if($flashProducts->count() > 1)
                    <button class="flash-sale-prev" type="button" aria-label="Sản phẩm trước"><i class="bi bi-chevron-left"></i></button>
                    <button class="flash-sale-next" type="button" aria-label="Sản phẩm sau"><i class="bi bi-chevron-right"></i></button>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

@if($activeCoupons->isNotEmpty())
<section class="section-space-sm bg-soft">
    <div class="container">
        <div class="row g-3">
            @foreach($activeCoupons as $coupon)
            <div class="col-lg-6">
                <div class="voucher-card h-100">
                    <div class="voucher-side">{{ $coupon->type === 'free_shipping' ? 'FREE' : ($coupon->type === 'percent' ? '-'.rtrim(rtrim(number_format((float) $coupon->value, 2, '.', ''), '0'), '.').'%' : '-'.number_format((float) $coupon->value / 1000).'K') }}</div>
                    <div class="voucher-cut"></div>
                    <div class="voucher-content d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="voucher-code">NHẬP MÃ: {{ $coupon->code }}</div>
                            <div class="voucher-description">{{ $coupon->name }}</div>
                            @if((float) $coupon->minimum_order > 0)<div class="small text-muted mt-1">Đơn tối thiểu {{ number_format((float) $coupon->minimum_order, 0, ',', '.') }}₫</div>@endif
                        </div>
                        <button class="btn btn-outline-primary btn-sm flex-shrink-0" type="button" data-copy-code="{{ $coupon->code }}"><i class="bi bi-copy me-1"></i>Sao chép</button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($homeCtaImage)
<section class="section-space-sm pt-0 home-promotion-section" aria-label="Chương trình nổi bật">
    <div class="container">
        <article class="home-promotion-cta">
            <img class="home-promotion-cta__image" src="{{ $homeCtaImage }}" alt="{{ $homeCtaTitle ?: $website['name'] }}" loading="lazy" width="1920" height="700">
            <div class="home-promotion-cta__shade"></div>
            @if($homeCtaTitle || $homeCtaSubTitle || $homeCtaButtons->isNotEmpty())
                <div class="home-promotion-cta__content">
                    <span class="home-promotion-cta__eyebrow"><i class="bi bi-stars"></i> Dành riêng cho bạn</span>
                    @if($homeCtaTitle)<h2>{{ $homeCtaTitle }}</h2>@endif
                    @if($homeCtaSubTitle)<p>{{ $homeCtaSubTitle }}</p>@endif
                    @if($homeCtaButtons->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($homeCtaButtons as $buttonIndex => $button)
                                @php
                                    $link = data_get($button, 'link');
                                    $isExternal = str_starts_with($link, 'http://') || str_starts_with($link, 'https://');
                                    $href = $isExternal || str_starts_with($link, '#') ? $link : url($link);
                                @endphp
                                <a class="btn {{ $buttonIndex === 0 ? 'btn-primary' : 'btn-light' }}" href="{{ $href }}" @if($isExternal) target="_blank" rel="noopener" @endif>{{ data_get($button, 'text.vi') }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </article>
    </div>
</section>
@endif

@foreach($homeProductSections as $categorySection)
    <section class="section-space {{ $categorySection['sectionClass'] }}">
        <div class="container">
            <div class="product-section-shell">
                <div class="product-section-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <h2 class="product-section-title">{{ $categorySection['title'] }}</h2>
                    <ul class="nav product-tabs" id="{{ $categorySection['id'] }}Tabs" role="tablist">
                        @foreach($categorySection['tabs'] as $tabIndex => $tab)
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link {{ $tabIndex === 0 ? 'active' : '' }}"
                                    data-bs-toggle="tab"
                                    data-bs-target="#{{ $tab['id'] }}"
                                    type="button"
                                    role="tab"
                                >{{ $tab['name'] }}</button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="product-section-content tab-content">
                    @foreach($categorySection['tabs'] as $tabIndex => $tab)
                        <div class="tab-pane fade {{ $tabIndex === 0 ? 'show active' : '' }}" id="{{ $tab['id'] }}" role="tabpanel">
                            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
                                @foreach($tab['products'] as $product)
                                    <div class="col"><x-product-card :product="$product" /></div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <div class="text-center mt-4"><a class="btn btn-outline-primary" href="{{ route('content.show', ['domain' => 'danh-muc', 'slug' => $categorySection['slug']]) }}">Xem sản phẩm</a></div>
                </div>
            </div>
        </div>
    </section>
@endforeach

@if($homepageSections->contains('brands') && $brands->isNotEmpty())
<section class="section-space">
    <div class="container">
        <x-section-heading
            :title="data_get($homepageSettings?->homepage_section_titles, 'brands.vi', 'Thương hiệu phân phối')"
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
@endif

@if($homepageSections->contains('testimonials') && $testimonials->isNotEmpty())
<section class="section-space pt-0 home-testimonials-section" aria-label="Phản hồi khách hàng">
    <div class="container">
        <x-section-heading :title="data_get($homepageSettings?->homepage_section_titles, 'testimonials.vi', 'Khách hàng nói gì về chúng tôi')" />
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 g-lg-4">
            @foreach($testimonials as $testimonial)
                <div class="col">
                    <article class="home-testimonial-card h-100">
                        @if($video = $testimonial->getFirstMedia('testimonial_video'))
                            <video class="home-testimonial-video mb-3" controls preload="metadata" playsinline>
                                <source src="{{ $video->getUrl() }}" type="{{ $video->mime_type }}">
                                Trình duyệt không hỗ trợ phát video.
                            </video>
                        @endif
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                            <div class="home-testimonial-stars" aria-label="{{ $testimonial->rating }} trên 5 sao">{{ str_repeat('★', $testimonial->rating) }}<span>{{ str_repeat('☆', 5 - $testimonial->rating) }}</span></div>
                            <i class="bi bi-quote home-testimonial-quote" aria-hidden="true"></i>
                        </div>
                        <blockquote>“{{ $testimonial->content }}”</blockquote>
                        <footer class="home-testimonial-author">
                            @if($avatar = $testimonial->getFirstMediaUrl('testimonial_avatar'))<img class="home-testimonial-avatar-image" src="{{ $avatar }}" alt="{{ $testimonial->name }}" loading="lazy">@else<span class="home-testimonial-avatar">{{ Str::upper(Str::substr($testimonial->name, 0, 1)) }}</span>@endif
                            <span>
                                <strong>{{ $testimonial->name }}</strong>
                                <small>{{ $testimonial->label ?: 'Khách hàng Rhea Skinlab' }}</small>
                            </span>
                        </footer>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($homepageSections->contains('posts') && $homeAdviceLead)
<section class="section-space bg-soft home-advice-section">
    <div class="container">
        <x-section-heading
            :title="data_get($homepageSettings?->homepage_section_titles, 'posts.vi', 'Blog')"
            :href="route('news.index')"
        />
        <div class="home-advice-layout {{ $homeAdviceSlides->isEmpty() ? 'home-advice-layout--single' : '' }}">
            <article class="home-advice-feature">
                <a class="home-advice-feature__image" href="{{ route('content.show', ['domain' => $homeAdviceLead['domain'], 'slug' => $homeAdviceLead['slug']]) }}">
                    <img src="{{ $homeAdviceLead['image'] }}" alt="{{ $homeAdviceLead['title'] }}" loading="lazy" width="1200" height="800">
                </a>
                <div class="home-advice-feature__overlay"></div>
                <div class="home-advice-feature__content">
                    @if($homeAdviceLead['category'])<span class="home-advice-feature__category">{{ $homeAdviceLead['category'] }}</span>@endif
                    <div class="home-advice-feature__date"><i class="bi bi-calendar3"></i>{{ $homeAdviceLead['date'] }}</div>
                    <h3><a href="{{ route('content.show', ['domain' => $homeAdviceLead['domain'], 'slug' => $homeAdviceLead['slug']]) }}">{{ $homeAdviceLead['title'] }}</a></h3>
                    @if($homeAdviceLead['excerpt'])<p>{{ $homeAdviceLead['excerpt'] }}</p>@endif
                    <a class="home-advice-feature__link" href="{{ route('content.show', ['domain' => $homeAdviceLead['domain'], 'slug' => $homeAdviceLead['slug']]) }}">Đọc bài viết <i class="bi bi-arrow-right"></i></a>
                </div>
            </article>

            @if($homeAdviceSlides->isNotEmpty())
                <div class="home-advice-side">
                    <div class="swiper home-advice-swiper" data-home-advice-swiper data-slide-count="{{ $homeAdviceSlides->count() }}">
                        <div class="swiper-wrapper">
                            @foreach($homeAdviceSlides as $articles)
                                <div class="swiper-slide">
                                    <div class="home-advice-list">
                                        @foreach($articles as $article)
                                            <article class="home-advice-item">
                                                <a class="home-advice-item__image" href="{{ route('content.show', ['domain' => $article['domain'], 'slug' => $article['slug']]) }}">
                                                    <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}" loading="lazy" width="360" height="240">
                                                </a>
                                                <div class="home-advice-item__content">
                                                    <div class="home-advice-item__meta">
                                                        @if($article['category'])<span>{{ $article['category'] }}</span>@endif
                                                        <span>{{ $article['date'] }}</span>
                                                    </div>
                                                    <h3><a href="{{ route('content.show', ['domain' => $article['domain'], 'slug' => $article['slug']]) }}">{{ $article['title'] }}</a></h3>
                                                    @if($article['excerpt'])<p>{{ $article['excerpt'] }}</p>@endif
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($homeAdviceSlides->count() > 1)
                            <div class="home-advice-pagination"></div>
                            <button class="home-advice-prev" type="button" aria-label="Nhóm bài viết trước"><i class="bi bi-arrow-left"></i></button>
                            <button class="home-advice-next" type="button" aria-label="Nhóm bài viết sau"><i class="bi bi-arrow-right"></i></button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endif

<section class="section-space pt-0 home-lead-section" id="tu-van">
    <div class="container">
        <div class="home-lead-card">
            <div class="row g-4 g-xl-5 align-items-center">
                <div class="col-lg-5">
                    <span class="home-lead-eyebrow"><i class="bi bi-chat-heart-fill"></i> Tư vấn 1:1</span>
                    <h2>Chưa biết sản phẩm nào hợp với da?</h2>
                    <p>Để lại thông tin, đội ngũ Rhea Skinlab sẽ hỗ trợ chọn sản phẩm và routine phù hợp với nhu cầu của bạn.</p>
                    <ul class="home-lead-benefits list-unstyled mb-0">
                        <li><i class="bi bi-check2-circle"></i> Gợi ý dựa trên tình trạng da và nhu cầu thực tế</li>
                        <li><i class="bi bi-check2-circle"></i> Không mất phí tư vấn</li>
                        <li><i class="bi bi-check2-circle"></i> Phản hồi trong giờ làm việc</li>
                    </ul>
                </div>
                <div class="col-lg-7">
                    <div class="home-lead-form-wrap">
                        <h3>Nhận tư vấn nhanh</h3>
                        @if(session('success'))<div class="alert alert-success py-2 mb-3">{{ session('success') }}</div>@endif
                        @if($errors->any())<div class="alert alert-danger py-2 mb-3">{{ $errors->first() }}</div>@endif
                        <form class="row g-3" action="{{ route('contact.submit') }}" method="post">
                            @csrf
                            <input class="d-none" name="website" tabindex="-1" autocomplete="off">
                            <input type="hidden" name="message" value="Đăng ký nhận tư vấn từ form trang chủ.">
                            <div class="col-md-6"><label class="form-label" for="homeLeadName">Họ và tên</label><input class="form-control" id="homeLeadName" name="name" value="{{ old('name') }}" autocomplete="name" required></div>
                            <div class="col-md-6"><label class="form-label" for="homeLeadPhone">Số điện thoại</label><input class="form-control" id="homeLeadPhone" name="phone" value="{{ old('phone') }}" type="tel" autocomplete="tel" required></div>
                            <div class="col-md-6"><label class="form-label" for="homeLeadEmail">Email</label><input class="form-control" id="homeLeadEmail" name="email" value="{{ old('email') }}" type="email" autocomplete="email" required></div>
                            <div class="col-md-6"><label class="form-label" for="homeLeadSubject">Bạn cần hỗ trợ gì?</label><select class="form-select" id="homeLeadSubject" name="subject"><option value="Tư vấn chọn sản phẩm" @selected(old('subject', 'Tư vấn chọn sản phẩm') === 'Tư vấn chọn sản phẩm')>Tư vấn chọn sản phẩm</option><option value="Tư vấn routine chăm da" @selected(old('subject') === 'Tư vấn routine chăm da')>Tư vấn routine chăm da</option><option value="Nhận ưu đãi mới" @selected(old('subject') === 'Nhận ưu đãi mới')>Nhận ưu đãi mới</option><option value="Hỗ trợ đơn hàng" @selected(old('subject') === 'Hỗ trợ đơn hàng')>Hỗ trợ đơn hàng</option></select></div>
                            <div class="col-12 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 pt-1">
                                <label class="form-check small text-muted mb-0"><input class="form-check-input" type="checkbox" required> <span class="form-check-label">Tôi đồng ý để Rhea Skinlab liên hệ tư vấn.</span></label>
                                <button class="btn btn-primary flex-shrink-0 px-4" type="submit"><i class="bi bi-send me-2"></i>Gửi thông tin</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
