@extends('layouts.master')

@section('title', 'Tin tức và góc nhìn — ' . $website['name'])
@php
    $newsSchemaItems = collect($newsItems)->map(fn (array $item): array => [
        'name' => $item['title'],
        'url' => route('content.show', ['domain' => $item['domain'] ?? 'tin-tuc', 'slug' => $item['slug']]),
    ])->all();
@endphp
@include('partials.frontend.structured-data', ['schema' => \App\Support\SchemaMarkup::collection('Tin tức và góc nhìn', 'Góc nhìn, kinh nghiệm và cập nhật mới từ THT Media.', $newsSchemaItems, route('news.index'))])

@section('content')
<section class="page-hero">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <h1>Tin tức và góc nhìn</h1>
        <p>Tổng hợp tin tức, góc nhìn và nội dung mới từ THT MEDIA VN.</p>
    </div>
</section>
<x-frontend.breadcrumb :items="[['label' => 'Trang chủ', 'url' => route('home')], ['label' => 'Tin tức']]" />
<section class="section-space">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($newsItems as $article)
                <div>
                    <article class="news-card">
                        <a class="news-card-image block" href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">
                            <img src="{{ str_starts_with($article['image'], 'http') || str_starts_with($article['image'], '/') ? $article['image'] : asset('assets/images/news/' . $article['image']) }}" alt="{{ $article['title'] }}" loading="lazy" width="800" height="500">
                        </a>
                        <div class="news-card-body">
                            <div class="news-date"><i class="fa-solid fa-calendar-days mr-1"></i>{{ $article['date'] }}</div>
                            <h2 class="news-title"><a href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">{{ $article['title'] }}</a></h2>
                            <p class="news-excerpt">{{ $article['excerpt'] }}</p>
                            <a class="news-readmore" href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">Đọc tiếp <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
