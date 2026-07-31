@extends('layouts.master')

@php
    $articleUrl = route('news.show', ['slug' => $article['slug']]);
    $articleImage = preg_match('/^https?:\/\//i', $article['image']) === 1 ? $article['image'] : url($article['image']);
    $articleSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        '@id' => $articleUrl.'#article',
        'mainEntityOfPage' => ['@id' => $articleUrl.'#webpage'],
        'headline' => $article['title'],
        'description' => $article['seo_description'] ?: $article['excerpt'],
        'image' => $articleImage ?: null,
        'datePublished' => $article['published_at'] ?: null,
        'dateModified' => $article['modified_at'] ?: $article['published_at'] ?: null,
        'articleSection' => $article['category_name'] ?: null,
        'inLanguage' => str_replace('_', '-', app()->getLocale()),
        'author' => ['@id' => rtrim(url('/'), '/').'#organization'],
        'publisher' => ['@id' => rtrim(url('/'), '/').'#organization'],
    ], static fn (mixed $value): bool => $value !== null && $value !== '');
    $articleBreadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_values(array_filter([
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Trang chủ',
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Tin tức',
                'item' => route('news.index'),
            ],
            $article['category_name'] ? [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $article['category_name'],
                'item' => route('content.show', ['domain' => 'tin-tuc', 'slug' => $article['category_slug']]),
            ] : null,
            [
                '@type' => 'ListItem',
                'position' => $article['category_name'] ? 4 : 3,
                'name' => $article['title'],
                'item' => $articleUrl,
            ],
        ])),
    ];
@endphp

@section('title', $article['seo_title'] ?: $article['title'] . ' — ' . $website['name'])
@section('meta_description', $article['seo_description'] ?: $article['excerpt'])
@section('meta_keywords', $article['seo_keywords'])
@section('canonical', $articleUrl)
@section('seo_image', $articleImage)
@section('og_type', 'article')
@section('seo_published_time', $article['published_at'])
@section('seo_modified_time', $article['modified_at'])
@section('article_section', $article['category_name'])

@push('schemas')
    <script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <script type="application/ld+json">{!! json_encode($articleBreadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
<div class="breadcrumb-wrap">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('news.index') }}">Tin tức</a></li>
                @if($article['category_name'] ?? false)
                    <li class="breadcrumb-item">
                        <a href="{{ route('content.show', ['domain' => 'tin-tuc', 'slug' => $article['category_slug']]) }}">{{ $article['category_name'] }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active text-truncate" aria-current="page">{{ $article['title'] }}</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section-space news-detail-section">
    <div class="container">
        <div class="row g-4 g-xl-5 align-items-start">
            <div class="col-lg-9">
                <article class="news-detail-main">
                    <header class="news-detail-header">
                        <div class="news-detail-meta">
                            @if($article['category_name'] ?? false)
                                <a class="news-detail-category" href="{{ route('content.show', ['domain' => 'tin-tuc', 'slug' => $article['category_slug']]) }}">
                                    {{ $article['category_name'] }}
                                </a>
                            @endif
                            <span><i class="bi bi-calendar3 me-1"></i>{{ $article['date'] }}</span>
                        </div>
                        <h1>{{ $article['title'] }}</h1>
                        @if($article['excerpt'])<p class="news-detail-excerpt">{{ $article['excerpt'] }}</p>@endif
                    </header>

                    <figure class="news-detail-cover">
                        <img src="{{ str_starts_with($article['image'], 'http') || str_starts_with($article['image'], '/') ? $article['image'] : asset('assets/images/news/' . $article['image']) }}" alt="{{ $article['title'] }}" width="1200" height="750">
                    </figure>

                    <div class="news-article-content">
                        @if(filled($article['content']))
                            {!! $article['content'] !!}
                        @elseif($article['excerpt'])
                            <p>{{ $article['excerpt'] }}</p>
                        @endif
                    </div>

                    <footer class="news-detail-footer">
                        <a href="{{ route('news.index') }}"><i class="bi bi-arrow-left me-2"></i>Quay lại danh sách tin</a>
                    </footer>
                </article>
            </div>

            <aside class="col-lg-3" aria-label="Nội dung bên cạnh">
                <div class="news-detail-sidebar">
                    @if($relatedNews->isNotEmpty())
                        <section class="news-sidebar-card">
                            <div class="news-sidebar-heading">
                                <h2>Bài viết mới</h2>
                                <a href="{{ route('news.index') }}" aria-label="Xem tất cả bài viết"><i class="bi bi-arrow-right"></i></a>
                            </div>
                            <div class="news-sidebar-list">
                                @foreach($relatedNews as $related)
                                    @php
                                        $relatedUrl = route('content.show', ['domain' => $related['domain'] ?? 'tin-tuc', 'slug' => $related['slug']]);
                                        $relatedImage = str_starts_with($related['image'], 'http') || str_starts_with($related['image'], '/')
                                            ? $related['image']
                                            : asset('assets/images/news/' . $related['image']);
                                    @endphp
                                    <article class="news-sidebar-item">
                                        <a class="news-sidebar-thumb" href="{{ $relatedUrl }}" tabindex="-1" aria-hidden="true">
                                            <img src="{{ $relatedImage }}" alt="" loading="lazy" width="180" height="130">
                                        </a>
                                        <div>
                                            <div class="news-sidebar-date">{{ $related['date'] }}</div>
                                            <h3><a href="{{ $relatedUrl }}">{{ $related['title'] }}</a></h3>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="news-sidebar-newsletter">
                        <span class="news-sidebar-newsletter-icon" aria-hidden="true"><i class="bi bi-envelope-paper-heart"></i></span>
                        <h2>Nhận bài viết mới</h2>
                        @if(session('newsletter_success'))
                            <div class="news-newsletter-success"><i class="bi bi-check-circle-fill me-1"></i>{{ session('newsletter_success') }}</div>
                        @else
                            <form action="{{ route('newsletter.store') }}" method="post">
                                @csrf
                                <input class="d-none" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                                <label class="visually-hidden" for="article-newsletter-email">Địa chỉ email</label>
                                <input class="form-control @error('email') is-invalid @enderror" id="article-newsletter-email" name="email" type="email" value="{{ old('email') }}" placeholder="Email của bạn" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <button class="btn btn-light w-100" type="submit">Đăng ký nhận tin</button>
                            </form>
                        @endif
                    </section>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection

