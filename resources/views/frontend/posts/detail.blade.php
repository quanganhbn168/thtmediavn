@extends('layouts.master')

@section('title', $article['seo_title'] ?: $article['title'] . ' — ' . $website['name'])
@section('meta_description', $article['seo_description'] ?: $article['excerpt'])
@section('meta_keywords', $article['seo_keywords'] ?: '')
@include('partials.frontend.structured-data', ['schema' => $articleSchema])

@section('content')
<x-frontend.breadcrumb :items="array_values(array_filter([
    ['label' => 'Trang chủ', 'url' => route('home')],
    ['label' => 'Tin tức', 'url' => route('news.index')],
    ($article['category_name'] ?? false) ? ['label' => $article['category_name'], 'url' => route('content.show', ['domain' => 'tin-tuc', 'slug' => $article['category_slug']])] : null,
    ['label' => $article['title']],
]))" />

<section class="section-space news-detail-section">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid items-start gap-6 xl:gap-12 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <article class="news-detail-main">
                    <header class="news-detail-header">
                        <div class="news-detail-meta">
                            @if($article['category_name'] ?? false)
                                <a class="news-detail-category" href="{{ route('content.show', ['domain' => 'tin-tuc', 'slug' => $article['category_slug']]) }}">
                                    {{ $article['category_name'] }}
                                </a>
                            @endif
                            <span><i class="fa-solid fa-calendar-days mr-1"></i>{{ $article['date'] }}</span>
                        </div>
                        <h1>{{ $article['title'] }}</h1>
                        @if($article['excerpt'])<p class="news-detail-excerpt">{{ $article['excerpt'] }}</p>@endif
                    </header>

                    <figure class="news-detail-cover">
                        <img src="{{ str_starts_with($article['image'], 'http') || str_starts_with($article['image'], '/') ? $article['image'] : asset('assets/images/news/' . $article['image']) }}" alt="{{ $article['title'] }}" width="1200" height="750">
                    </figure>

                    @if(! empty($article['toc']))
                        <nav class="article-toc" aria-labelledby="article-toc-title">
                            <strong id="article-toc-title">Mục lục bài viết</strong>
                            <ol>
                                @foreach($article['toc'] as $tocItem)
                                    <li class="article-toc__level-{{ $tocItem['level'] }}"><a href="#{{ $tocItem['id'] }}">{{ $tocItem['label'] }}</a></li>
                                @endforeach
                            </ol>
                        </nav>
                    @endif

                    <div class="news-article-content">
                        @if(filled($article['content']))
                            {!! $article['content'] !!}
                        @elseif($article['excerpt'])
                            <p>{{ $article['excerpt'] }}</p>
                        @endif
                    </div>

                    @include('partials.frontend.comments', ['commentable' => $commentable, 'commentableType' => 'post', 'comments' => $comments])

                    <footer class="news-detail-footer">
                        <a href="{{ route('news.index') }}"><i class="fa-solid fa-arrow-left mr-2"></i>Quay lại danh sách tin</a>
                    </footer>
                </article>
            </div>

            <aside class="lg:col-span-3" aria-label="Nội dung bên cạnh">
                <div class="news-detail-sidebar">
                    @if($relatedNews->isNotEmpty())
                        <section class="news-sidebar-card">
                            <div class="news-sidebar-heading">
                                <h2>Bài viết mới</h2>
                                <a href="{{ route('news.index') }}" aria-label="Xem tất cả bài viết"><i class="fa-solid fa-arrow-right"></i></a>
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
                                            <h3><a href="{{ $relatedUrl }}">{{ $related['title'] }}</a></h3>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="news-sidebar-newsletter">
                        <span class="news-sidebar-newsletter-icon" aria-hidden="true"><i class="fa-solid fa-envelope-paper-heart"></i></span>
                        <h2>Nhận bài viết mới</h2>
                        @if(session('newsletter_success'))
                            <div class="news-newsletter-success"><i class="fa-solid fa-circle-check mr-1"></i>{{ session('newsletter_success') }}</div>
                        @else
                            <form action="{{ route('newsletter.store') }}" method="post">
                                @csrf
                                <input class="hidden" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                                <label class="sr-only" for="article-newsletter-email">Địa chỉ email</label>
                                <input class="ui-input @error('email') border-red-500 @enderror" id="article-newsletter-email" name="email" type="email" value="{{ old('email') }}" placeholder="Email của bạn" required>
                                @error('email')<div class="ui-error">{{ $message }}</div>@enderror
                                <button class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-full border border-transparent bg-white px-5 py-3 text-sm font-bold leading-tight text-ink shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-soft hover:text-primary hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" type="submit">Đăng ký nhận tin</button>
                            </form>
                        @endif
                    </section>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
