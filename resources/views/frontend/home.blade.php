@extends('layouts.master')

@section('title', $homepageTitle)
@section('meta_description', $homepageDescription)
@section('canonical', url('/'))
@section('og_type', 'website')

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($homepageSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.AOS) {
                window.AOS.init({
                    duration: 700,
                    easing: 'ease-out-cubic',
                    once: true,
                    offset: 80,
                });
            }

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const parallaxImage = document.querySelector('[data-home-parallax]');

            if (!reduceMotion && parallaxImage) {
                const moveParallax = () => {
                    const rect = parallaxImage.closest('.home-hero')?.getBoundingClientRect();
                    if (!rect) return;
                    const progress = Math.max(-1, Math.min(1, (window.innerHeight / 2 - (rect.top + rect.height / 2)) / rect.height));
                    parallaxImage.style.transform = `translate3d(0, ${progress * 26}px, 0) scale(1.06)`;
                };

                window.addEventListener('scroll', moveParallax, { passive: true });
                moveParallax();
            }

            document.querySelectorAll('[data-home-counter]').forEach((counter) => {
                const target = Number(counter.dataset.homeCounter || 0);
                const prefix = counter.dataset.homePrefix || '';
                const suffix = counter.dataset.homeSuffix || '';
                const update = (value) => { counter.textContent = `${prefix}${Math.round(value)}${suffix}`; };
                const observer = new IntersectionObserver((entries, instance) => {
                    if (!entries[0].isIntersecting) return;
                    const start = performance.now();
                    const duration = 900;
                    const animate = (now) => {
                        const progress = Math.min((now - start) / duration, 1);
                        update(target * (1 - Math.pow(1 - progress, 3)));
                        if (progress < 1) window.requestAnimationFrame(animate);
                    };
                    window.requestAnimationFrame(animate);
                    instance.disconnect();
                }, { threshold: .65 });
                observer.observe(counter);
            });
        });
    </script>
@endpush

@section('content')
<div class="home-page">
    <section class="home-hero" id="home" aria-label="THT Media">
        @if($heroSlider?->items?->isNotEmpty())
            <div class="swiper home-hero-swiper" data-home-hero-swiper data-slide-count="{{ $heroSlider->items->count() }}">
                <div class="swiper-wrapper">
                    @foreach($heroSlider->items as $slide)
                        @php
                            $buttons = is_array($slide->buttons) ? $slide->buttons : [];
                            $buttonOne = $buttons[0] ?? [];
                            $buttonTwo = $buttons[1] ?? [];
                            $slideTitle = trim((string) $slide->getTranslation('title', 'vi'));
                            $slideSubtitle = trim((string) $slide->getTranslation('sub_title', 'vi'));
                            $buttonOneText = trim((string) data_get($buttonOne, 'text.vi'));
                            $buttonTwoText = trim((string) data_get($buttonTwo, 'text.vi'));
                            $buttonOneLink = trim((string) data_get($buttonOne, 'link'));
                            $buttonTwoLink = trim((string) data_get($buttonTwo, 'link'));
                            $hasSlideContent = $slideTitle !== ''
                                || $slideSubtitle !== ''
                                || ($buttonOneText !== '' && $buttonOneLink !== '')
                                || ($buttonTwoText !== '' && $buttonTwoLink !== '');
                            $slideImage = $slide->getFirstMediaUrl('slide_image') ?: asset('assets/images/home-demo/hero.jpg');
                        @endphp
                        <div class="swiper-slide">
                            <img src="{{ $slideImage }}" alt="" fetchpriority="high">
                            @if($hasSlideContent)
                                <div class="home-hero__content">
                                    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                                        <div class="grid items-center gap-12 lg:grid-cols-12">
                                            <div class="lg:col-span-8">
                                                @if($slideTitle !== '')<h2 class="font-display text-3xl font-semibold leading-tight tracking-tight text-white sm:text-4xl lg:text-5xl">{{ $slideTitle }}</h2>@endif
                                                @if($slideSubtitle !== '')<p class="home-hero__lead text-base leading-relaxed text-white/80 sm:text-lg">{{ $slideSubtitle }}</p>@endif
                                                @if(($buttonOneText !== '' && $buttonOneLink !== '') || ($buttonTwoText !== '' && $buttonTwoLink !== ''))
                                                    <div class="flex flex-wrap gap-3">
                                                        @if($buttonOneText !== '' && $buttonOneLink !== '')<a class="inline-flex min-h-14 items-center justify-center gap-2 rounded-full border border-transparent bg-secondary px-6 py-4 text-base font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-orange-700 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary max-md:min-h-10 max-md:px-4 max-md:py-2 max-md:text-xs" href="{{ $buttonOneLink }}">{{ $buttonOneText }} <i class="fa-solid fa-arrow-up-right-from-square ml-2"></i></a>@endif
                                                        @if($buttonTwoText !== '' && $buttonTwoLink !== '')<a class="inline-flex min-h-14 items-center justify-center gap-2 rounded-full border border-white/60 bg-transparent px-6 py-4 text-base font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:border-white hover:bg-white hover:text-ink hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white max-md:min-h-10 max-md:px-4 max-md:py-2 max-md:text-xs" href="{{ $buttonTwoLink }}">{{ $buttonTwoText }} <i class="fa-solid fa-arrow-up-right-from-square ml-2"></i></a>@endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if($heroSlider->items->count() > 1)
                    <div class="swiper-pagination"></div>
                    <button class="home-hero-prev" type="button" aria-label="Slide trước"><i class="fa-solid fa-arrow-left"></i></button>
                    <button class="home-hero-next" type="button" aria-label="Slide tiếp theo"><i class="fa-solid fa-arrow-right"></i></button>
                @endif
            </div>
        @else
            <div class="home-hero__media" aria-hidden="true">
                <img src="{{ asset('assets/images/home-demo/hero.jpg') }}" alt="" data-home-parallax fetchpriority="high" width="1920" height="1080">
            </div>
            <div class="home-hero__content">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid items-center gap-12 lg:grid-cols-12">
                        <div class="lg:col-span-8" data-aos="fade-up">
                            <h2 class="font-display text-3xl font-semibold leading-tight tracking-tight text-white sm:text-4xl lg:text-5xl">Biến mục tiêu truyền thông thành sản phẩm có thể triển khai.</h2>
                            <p class="home-hero__lead text-base leading-relaxed text-white/80 sm:text-lg">Từ chiến lược, hình ảnh đến video và sự kiện, THT Media giúp doanh nghiệp có một đội ngũ đồng hành xuyên suốt.</p>
                            <div class="flex flex-wrap gap-3">
                                <a class="inline-flex min-h-14 items-center justify-center gap-2 rounded-full border border-white/60 bg-transparent px-6 py-4 text-base font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:border-white hover:bg-white hover:text-ink hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white max-md:min-h-10 max-md:px-4 max-md:py-2 max-md:text-xs glightbox" href="https://www.youtube.com/watch?v=aqz-KE-bpKQ" data-type="video">Xem showreel <i class="fa-solid fa-circle-play ml-2"></i></a>
                            </div>
                        </div>
                        <div class="lg:col-span-4 hidden lg:block" data-aos="fade-left" data-aos-delay="180">
                            <div class="rounded-2xl overflow-hidden border border-white/25 shadow-lg">
                                <img src="{{ asset('assets/images/home-demo/camera.jpg') }}" alt="Hậu trường sản xuất nội dung tại THT Media" class="object-cover">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>

    @if($homepageStats->isNotEmpty())
        <section class="home-proof" aria-label="Năng lực nổi bật">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="home-proof__inner">
                    <div class="grid grid-cols-2 lg:grid-cols-4 text-center">
                        @foreach($homepageStats as $stat)
                            @php
                                $statValue = (string) data_get($stat, 'value', '0');
                                $statSuffix = (string) data_get($stat, 'suffix', '');
                                $statIcon = (string) data_get($stat, 'icon', 'fa-solid fa-chart-line');
                            @endphp
                            <div class="home-proof__item"><i class="{{ $statIcon }} home-proof__icon" aria-hidden="true"></i><div class="home-proof__content"><strong @if(is_numeric($statValue)) data-home-counter="{{ $statValue }}" data-home-suffix="{{ $statSuffix }}" @endif>{{ is_numeric($statValue) ? '0'.$statSuffix : $statValue }}</strong><span>{{ data_get($stat, 'label') }}</span></div></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="section-space" id="home-about">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-12">
                <div class="lg:col-span-6" data-aos="fade-right">
                    <span class="section-eyebrow">Về chúng tôi</span>
                    <h1 class="section-title">{{ $homepageCompanyName }}</h1>
                    @if($homepageAboutTitle && $homepageAboutTitle !== $homepageCompanyName)<p class="section-lead">{{ $homepageAboutTitle }}</p>@endif
                    <p class="section-lead">{{ $homepageAboutText }}</p>
                    @if($homepageAboutSupportingText)<p class="mt-3 text-muted">{{ $homepageAboutSupportingText }}</p>@endif
                    <div class="home-about__actions flex flex-wrap gap-3 mt-4">
                        <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-secondary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:bg-orange-700 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary" href="{{ route('about') }}">Xem chi tiết</a>
                        <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-primary bg-transparent px-5 py-3 text-sm font-bold leading-tight text-primary shadow-sm transition duration-200 hover:bg-primary-soft hover:text-primary hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" href="#">Xem hồ sơ năng lực</a>
                    </div>
                    @if($homepageSections->contains('reasons') && $homepageReasons->isNotEmpty())
                        <div class="home-about__reasons" aria-labelledby="home-about-reasons-title">
                            <h3 class="sr-only" id="home-about-reasons-title">Vì sao chọn chúng tôi</h3>
                            <div class="home-reasons__grid">
                                @foreach($homepageReasons as $reason)
                                    <article class="home-reasons__item" data-aos="fade-up" @if($loop->index > 0) data-aos-delay="{{ min($loop->index * 80, 240) }}" @endif>
                                        <i class="{{ $homepageReasonIcons[$loop->index] ?? 'fa-solid fa-check' }} home-reasons__icon" aria-hidden="true"></i>
                                        <p>{{ $reason }}</p>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="lg:col-span-6" data-aos="fade-left">
                    <div class="home-intro__visual">
                        <img src="{{ $homepageAboutImage }}" alt="Đội ngũ THT Media" loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space home-services" id="home-services">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="section-heading-row" data-aos="fade-up">
                <div><span class="section-eyebrow">Hệ sinh thái</span><h2 class="section-title section-title--small">Dịch vụ truyền thông - media toàn diện</h2></div>
                <a class="home-link" href="{{ route('services.index') }}">Xem tất cả dịch vụ <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            @if($homeServiceCategories->isNotEmpty())
                <div class="home-service-bento">
                    @foreach($homeServiceCategories as $category)
                        @php
                            $categoryName = $category->getTranslation('name', 'vi');
                            $categorySlug = $category->getSlug('vi');
                            $categoryUrl = $categorySlug ? route('services.show', ['slug' => $categorySlug]) : route('services.index');
                            $categoryDescription = trim(strip_tags((string) $category->getTranslation('description', 'vi')));
                            $categoryServices = $category->services
                                ->map(fn ($service): string => $service->getTranslation('name', 'vi'))
                                ->filter()
                                ->take(3);
                            $categoryImage = $category->services->first()?->thumbnail?->url
                                ?: $category->services->first()?->getFirstMediaUrl('thumbnail')
                                ?: asset('assets/images/home-demo/factory.jpg');
                            $cardClass = 'home-service-bento__card'.($loop->first ? ' home-service-bento__card--featured' : '').($loop->index === 5 ? ' home-service-bento__card--wide' : '');
                        @endphp
                        <article class="{{ $cardClass }}" data-aos="fade-up" @if($loop->index > 0) data-aos-delay="{{ min($loop->index * 80, 240) }}" @endif>
                            @if($loop->first)
                                <div class="home-service-bento__visual"><img src="{{ $categoryImage }}" alt="{{ $categoryName }}" loading="lazy"></div>
                            @endif
                            <div class="home-service-bento__body">
                                <h3><a href="{{ $categoryUrl }}">{{ $categoryName }}</a></h3>
                                @if($categoryDescription)<p>{{ $categoryDescription }}</p>@endif
                                @if($categoryServices->isNotEmpty())
                                    <ul>
                                        @foreach($categoryServices as $serviceName)
                                            <li>{{ $serviceName }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                <a class="home-service-bento__link" href="{{ $categoryUrl }}">Xem danh mục <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="section-space home-projects" id="home-projects">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="section-heading-row" data-aos="fade-up"><div><span class="section-eyebrow">Dự án</span><h2 class="section-title section-title--small">Những gì THT Media đã biến thành sản phẩm.</h2></div><a class="home-link" href="{{ route('projects.index') }}">Xem portfolio <i class="fa-solid fa-arrow-right"></i></a></div>
            @if($homeProjects->isNotEmpty())
                <div class="home-projects__filters" role="tablist" aria-label="Lọc dự án THT Media">
                    <button class="home-projects__filter is-active" type="button" role="tab" aria-selected="true" data-project-filter="all">Tất cả</button>
                    @foreach($projectFilters as $projectFilter)
                        <button class="home-projects__filter" type="button" role="tab" aria-selected="false" data-project-filter="{{ $projectFilter['slug'] }}">{{ $projectFilter['label'] }}</button>
                    @endforeach
                </div>
                <div class="home-projects__views">
                    @foreach($projectGroups as $filter => $projects)
                        <div class="home-projects__view {{ $filter === 'all' ? 'is-active' : '' }}" data-project-view="{{ $filter }}">
                            <div class="swiper home-project-swiper" data-project-swiper>
                                <div class="swiper-wrapper">
                                    @foreach($projects as $project)
                                        @php
                                            $projectName = $project->getTranslation('name', 'vi');
                                            $projectCategory = $project->category?->getTranslation('name', 'vi') ?: 'Dự án';
                                            $projectClient = $project->client?->getTranslation('name', 'vi') ?: ($project->industry ?: 'THT Media');
                                            $projectImage = $project->cover?->url ?: $project->shareImage?->url ?: $project->getFirstMediaUrl('cover') ?: $project->getFirstMediaUrl('share_image') ?: asset('assets/images/home-demo/factory.jpg');
                                            $projectUrl = $project->getSlug('vi') ? route('projects.show', ['slug' => $project->getSlug('vi')]) : route('projects.index');
                                        @endphp
                                        <div class="swiper-slide">
                                            <a class="home-project-card" href="{{ $projectUrl }}" style="--project-image: url('{{ $projectImage }}')" aria-label="Xem chi tiết dự án {{ $projectName }}">
                                                <div class="home-project-card__body"><small>{{ $projectCategory }} <span>{{ $project->completed_year ?: '—' }}</span></small><h3>{{ $projectName }}</h3><div class="home-project-card__meta"><span>{{ $projectClient }}</span><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></div></div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="home-projects__footer"><div class="swiper-pagination home-projects__progress" data-project-progress></div><div class="home-projects__navigation"><button type="button" class="home-projects__arrow" data-project-prev aria-label="Dự án trước"><i class="fa-solid fa-arrow-left"></i></button><button type="button" class="home-projects__arrow" data-project-next aria-label="Dự án tiếp theo"><i class="fa-solid fa-arrow-right"></i></button></div></div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state" data-aos="fade-up"><i class="fa-solid fa-folder-open" aria-hidden="true"></i><h3>Danh mục dự án đang được cập nhật</h3><p>Các dự án được bật trong quản trị sẽ tự động xuất hiện tại đây.</p><a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" href="{{ route('projects.index') }}">Xem trang dự án</a></div>
            @endif
        </div>
    </section>

    <section class="section-space home-process" id="home-process">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="home-process__layout">
                <aside class="home-process__aside" data-aos="fade-right">
                    <span class="section-eyebrow">Quy trình</span>
                    <h2 class="section-title section-title--small">Từ brief đến bàn giao, mọi bước đều có người phụ trách.</h2>
                    <p class="section-lead">Một quy trình rõ ràng giúp anh/chị dễ theo dõi tiến độ, quyết định nhanh và luôn biết sản phẩm tiếp theo là gì.</p>
                    <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary mt-4" href="#home-contact">Bắt đầu trao đổi</a>
                    <div class="home-process__note"><span>Một đầu mối xuyên suốt từ ý tưởng đến sản phẩm hoàn thiện.</span></div>
                </aside>
                <div class="home-process__flow" data-aos="fade-left" aria-label="Các bước triển khai dự án">
                    <article class="home-process__item"><div class="home-process__item-marker"><strong>01</strong></div><div><h3>Nhận brief</h3><p>Làm rõ mục tiêu, đối tượng, bối cảnh và thời gian.</p></div></article>
                    <article class="home-process__item"><div class="home-process__item-marker"><strong>02</strong></div><div><h3>Tư vấn hướng triển khai</h3><p>Chọn hướng triển khai phù hợp với nguồn lực.</p></div></article>
                    <article class="home-process__item"><div class="home-process__item-marker"><strong>03</strong></div><div><h3>Concept & kịch bản</h3><p>Chốt ý tưởng, phạm vi và sản phẩm bàn giao.</p></div></article>
                    <article class="home-process__item"><div class="home-process__item-marker"><strong>04</strong></div><div><h3>Sản xuất</h3><p>Quay, chụp, thiết kế và hậu kỳ theo kế hoạch.</p></div></article>
                    <article class="home-process__item"><div class="home-process__item-marker"><strong>05</strong></div><div><h3>Bàn giao</h3><p>Hoàn thiện, nghiệm thu và đồng hành sau dự án.</p></div></article>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space home-capacity home-difference" id="home-capacity" aria-labelledby="home-difference-title">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="home-difference__panel">
                <img class="home-difference__watermark" src="{{ asset('assets/images/logo.svg') }}" alt="" aria-hidden="true">
                <h2 class="sr-only" id="home-difference-title">Điểm khác biệt của THT Media</h2>
                <div class="home-difference__rows">
                    <article class="home-difference__row">
                        <div class="home-difference__row-copy">
                            <strong class="home-difference__number">01</strong>
                            <div><h3>8 năm kinh nghiệm chuyên sâu</h3><p>Đã thực hiện hàng ngàn dự án phim doanh nghiệp, truyền thông, am hiểu thị trường và các nền tảng truyền thông.</p></div>
                        </div>
                        <div class="home-difference__image"><img src="{{ asset('assets/images/home-demo/team.jpg') }}" alt="Đội ngũ THT Media trao đổi dự án" loading="lazy"></div>
                    </article>
                    <article class="home-difference__row">
                        <div class="home-difference__row-copy">
                            <strong class="home-difference__number">02</strong>
                            <div><h3>Nhân sự in-house 100%</h3><p>Kiểm soát chặt chẽ quy trình, chất lượng và tiến độ mà không phụ thuộc bất kỳ bên trung gian nào.</p></div>
                        </div>
                        <div class="home-difference__image"><img src="{{ asset('assets/images/home-demo/classroom.jpg') }}" alt="Nhân sự THT Media trong buổi làm việc" loading="lazy"></div>
                    </article>
                    <article class="home-difference__row">
                        <div class="home-difference__row-copy">
                            <strong class="home-difference__number">03</strong>
                            <div><h3>Trang thiết bị hiện đại</h3><p>Sở hữu hệ thống máy quay, máy ảnh, thiết bị ánh sáng, flycam chuẩn sản xuất chuyên nghiệp.</p></div>
                        </div>
                        <div class="home-difference__image"><img src="{{ asset('assets/images/home-demo/factory.jpg') }}" alt="Thiết bị và không gian sản xuất nội dung" loading="lazy"></div>
                    </article>
                    <article class="home-difference__row">
                        <div class="home-difference__row-copy">
                            <strong class="home-difference__number">04</strong>
                            <div><h3>Linh hoạt &amp; tối ưu</h3><p>Cung cấp linh hoạt các gói dịch vụ trọn gói hoặc dịch vụ theo từng nhu cầu và ngân sách riêng của từng khách hàng.</p></div>
                        </div>
                        <div class="home-difference__image"><img src="{{ asset('assets/images/home-demo/event.jpg') }}" alt="THT Media triển khai truyền thông tại sự kiện" loading="lazy"></div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="section-space" id="home-clients">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="section-heading-row" data-aos="fade-up"><div><span class="section-eyebrow">Khách hàng & đối tác</span></div></div>
            @if($useClientMarquee)
                <div class="home-client-marquees mb-5" data-aos="fade-up" aria-label="Logo khách hàng và đối tác">
                    @foreach($clientMarqueeRows as $rowIndex => $clientMarqueeRow)
                        <div class="home-client-marquee">
                            <div class="home-client-marquee__track {{ $rowIndex % 2 === 1 ? 'home-client-marquee__track--reverse' : '' }}">
                                <div class="home-client-marquee__set">
                                    @foreach($clientMarqueeRow as $client)
                                        <a class="home-client-marquee__item" href="{{ $client->website_url ?: route('clients.index') }}" @if($client->website_url) target="_blank" rel="noopener" @endif aria-label="{{ $client->getTranslation('name', 'vi') }}">
                                            <img src="{{ $client->getFirstMediaUrl('logo') }}" alt="{{ $client->getTranslation('name', 'vi') }}" loading="lazy">
                                        </a>
                                    @endforeach
                                </div>
                                <div class="home-client-marquee__set" aria-hidden="true">
                                    @foreach($clientMarqueeRow as $client)
                                        <div class="home-client-marquee__item">
                                            <img src="{{ $client->getFirstMediaUrl('logo') }}" alt="" loading="lazy">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="home-client-list mb-5" data-aos="fade-up">
                    @forelse($featuredClients as $client)
                        <a class="home-client-list__item" href="{{ $client->website_url ?: route('clients.index') }}" @if($client->website_url) target="_blank" rel="noopener" @endif aria-label="{{ $client->getTranslation('name', 'vi') }}">
                            @if($client->getFirstMediaUrl('logo'))<img src="{{ $client->getFirstMediaUrl('logo') }}" alt="{{ $client->getTranslation('name', 'vi') }}" loading="lazy">@else<span>{{ $client->getTranslation('name', 'vi') }}</span>@endif
                        </a>
                    @empty
                        <div class="empty-state home-client-list__empty"><i class="fa-solid fa-building" aria-hidden="true"></i><p>Khách hàng và đối tác đang được cập nhật trong quản trị.</p></div>
                    @endforelse
                </div>
            @endif
            <div class="grid gap-6 md:grid-cols-3">
                @forelse($testimonials as $index => $testimonial)
                    <div data-aos="fade-up" @if($index > 0) data-aos-delay="{{ $index * 80 }}" @endif><article class="home-testimonial">@if($testimonial->getFirstMediaUrl('testimonial_avatar'))<img class="home-testimonial__avatar" src="{{ $testimonial->getFirstMediaUrl('testimonial_avatar') }}" alt="{{ $testimonial->name }}" loading="lazy">@endif<div class="home-testimonial__stars" aria-label="{{ $testimonial->rating }} trên 5 sao">{{ str_repeat('★', $testimonial->rating) }}</div><blockquote>“{{ strip_tags($testimonial->content) }}”</blockquote><footer><strong>{{ $testimonial->name }}</strong>@if($testimonial->label)<small>{{ $testimonial->label }}</small>@endif</footer></article></div>
                @empty
                    <div class="md:col-span-3"><div class="empty-state"><i class="fa-solid fa-comment-dots" aria-hidden="true"></i><h3>Phản hồi khách hàng đang được cập nhật</h3><p>Các cảm nhận được duyệt trong quản trị sẽ tự động hiển thị tại đây.</p></div></div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section-space home-pricing" id="home-pricing">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="section-heading-row" data-aos="fade-up"><div><span class="section-eyebrow">Bảng giá</span><h2 class="section-title section-title--small">Chọn gói phù hợp với mục tiêu triển khai.</h2></div><a class="home-link" href="{{ route('pricing') }}">Xem bảng giá <i class="fa-solid fa-arrow-right"></i></a></div>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                @forelse($pricingPlans as $index => $plan)
                    <div><article id="pricing-plan-{{ $plan->id }}" class="home-pricing-card {{ $plan->is_featured ? 'home-pricing-card--featured' : '' }}" data-aos="fade-up" @if($index > 0) data-aos-delay="{{ min($index * 80, 240) }}" @endif><h3>{{ $plan->name }}</h3>@if($plan->summary)<p>{{ $plan->summary }}</p>@endif<div class="home-pricing-card__price">{{ $plan->display_price }}</div>@if($plan->price_note)<small class="home-pricing-card__price-note">{{ $plan->price_note }}</small>@endif<ul>@foreach($plan->features ?? [] as $feature)<li>{{ $feature }}</li>@endforeach</ul><a class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-full border border-secondary bg-transparent px-5 py-3 text-sm font-bold leading-tight text-secondary shadow-sm transition duration-200 hover:-translate-y-px hover:bg-secondary hover:text-white hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary" href="{{ route('pricing') }}#pricing-plan-{{ $plan->id }}">Xem chi tiết <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></article></div>
                @empty
                    <div class="md:col-span-2 xl:col-span-4"><div class="empty-state"><i class="fa-solid fa-tags" aria-hidden="true"></i><h3>Bảng giá đang được cập nhật</h3><p>Các gói dịch vụ sẽ được quản lý và hiển thị tại trang bảng giá.</p><a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white" href="{{ route('pricing') }}">Mở trang bảng giá</a></div></div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section-space home-news" id="home-news">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="section-heading-row" data-aos="fade-up">
                <div>
                    <span class="section-eyebrow">Tin tức - Kiến thức - Sự kiện</span>
                    <h2 class="section-title section-title--small">Cập nhật mới từ THT Media.</h2>
                    <p class="section-lead">Cập nhật hoạt động, góc nhìn thực chiến và những sự kiện THT Media đang đồng hành.</p>
                </div>
                <a class="home-link" href="{{ route('news.index') }}">Xem tất cả bài viết <i class="fa-solid fa-angle-right" aria-hidden="true"></i></a>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                @forelse($newsItems as $index => $article)
                    @php
                        $articleUrl = route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]);
                        $articleType = $article['category'] ?: 'Tin tức';
                        $articleIcon = match ($article['category_slug'] ?? '') {
                            'kien-thuc', 'knowledge' => 'fa-lightbulb',
                            'su-kien', 'event' => 'fa-calendar-days',
                            default => 'fa-newspaper',
                        };
                    @endphp
                    <div class="" data-aos="fade-up" @if($index > 0) data-aos-delay="{{ $index * 100 }}" @endif>
                        <article class="home-news-card">
                            <a class="home-news-card__image block" href="{{ $articleUrl }}">
                                <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}" loading="lazy" width="800" height="500">
                                <span class="home-news-card__type"><i class="fa-solid {{ $articleIcon }}" aria-hidden="true"></i> {{ $articleType }}</span>
                            </a>
                            <div class="home-news-card__body">
                                <h3><a href="{{ $articleUrl }}">{{ $article['title'] }}</a></h3>
                                @if($article['excerpt'])<p>{{ $article['excerpt'] }}</p>@endif
                                <a class="home-link" href="{{ $articleUrl }}">Xem chi tiết <i class="fa-solid fa-angle-right" aria-hidden="true"></i></a>
                            </div>
                        </article>
                    </div>
                @empty
                    <div><div class="empty-state"><i class="fa-solid fa-newspaper" aria-hidden="true"></i><h3>Tin tức đang được cập nhật</h3><p>Những nội dung mới của THT Media sẽ được đăng tải tại đây.</p><a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" href="{{ route('news.index') }}">Xem trang tin tức</a></div></div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section-space home-contact home-contact--plain" id="home-contact">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-12">
                <div class="lg:col-span-3" data-aos="fade-right"><div class="home-contact__intro"><span class="section-eyebrow">Liên hệ</span><p class="home-contact__helper">Chia sẻ bài toán của anh/chị, THT Media sẽ liên hệ ngay để cùng tìm hướng triển khai phù hợp.</p><div class="home-contact__details">
                    @if($contactPhones->isNotEmpty())
                        <div class="home-contact__detail"><i class="fa-solid fa-phone" aria-hidden="true"></i><span class="home-contact__phone-list">
                            @foreach($contactPhones as $contactPhone)
                                @if(!$loop->first)<span aria-hidden="true"> - </span>@endif<a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $contactPhone['number']) }}">{{ $contactPhone['number'] }}</a>
                            @endforeach
                        </span></div>
                    @endif
                    @if(filled($website['email'] ?? null))<div class="home-contact__detail"><i class="fa-solid fa-envelope" aria-hidden="true"></i><a href="mailto:{{ $website['email'] }}">{{ $website['email'] }}</a></div>@endif
                    @if(filled($website['address'] ?? null))<div class="home-contact__detail"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span>{{ $website['address'] }}</span></div>@endif
                </div></div></div>
                <div class="lg:col-span-5" data-aos="fade-up"><div class="home-contact__form"><form class="grid gap-4 md:grid-cols-2" action="{{ route('contact.submit') }}" method="POST" enctype="multipart/form-data">@csrf<input class="hidden" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"><div><label class="ui-label" for="home-name">Họ và tên</label><input class="ui-input" id="home-name" name="name" value="{{ old('name') }}" required></div><div><label class="ui-label" for="home-phone">Số điện thoại</label><input class="ui-input" id="home-phone" name="phone" value="{{ old('phone') }}" type="tel" required></div><div><label class="ui-label" for="home-company">Doanh nghiệp</label><input class="ui-input" id="home-company" name="company" value="{{ old('company') }}"></div><div><label class="ui-label" for="home-budget">Ngân sách dự kiến</label><select class="ui-select" id="home-budget" name="budget"><option value="">Chưa xác định</option><option>Dưới 30 triệu</option><option>30–80 triệu</option><option>80–200 triệu</option><option>Trên 200 triệu</option></select></div><div class="md:col-span-2"><label class="ui-label" for="home-message">Nội dung cần tư vấn</label><textarea class="ui-input" id="home-message" name="message" rows="4" required placeholder="Mục tiêu, phạm vi, địa điểm hoặc thời gian dự kiến...">{{ old('message') }}</textarea></div><div class="md:col-span-2"><button class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-6 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" type="submit">Gửi yêu cầu tư vấn <i class="fa-solid fa-arrow-up-right-from-square ml-2"></i></button></div></form></div></div>
                <div class="lg:col-span-4" data-aos="fade-left">
                    <div class="home-contact__map">
                        @if($homeMapEmbedUrl)
                            <iframe src="{{ $homeMapEmbedUrl }}" title="Bản đồ vị trí {{ $website['name'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                        @else
                            <div class="home-contact__map-placeholder"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i><span>Google Maps chưa được cấu hình</span></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
