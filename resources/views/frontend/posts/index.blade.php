@extends('layouts.master')

@section('title', 'Tin tức làm đẹp — ' . $website['name'])

@section('content')
<div class="breadcrumb-wrap">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tin tức</li>
            </ol>
        </nav>
    </div>
</div>
<section class="page-hero">
    <div class="container">
        <h1>Kiến thức làm đẹp</h1>
        <p>Tổng hợp tin tức, góc nhìn và nội dung mới từ THT MEDIA VN.</p>
    </div>
</section>
<section class="section-space">
    <div class="container">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            @foreach($newsItems as $article)
                <div class="col">
                    <article class="news-card">
                        <a class="news-card-image d-block" href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">
                            <img src="{{ str_starts_with($article['image'], 'http') || str_starts_with($article['image'], '/') ? $article['image'] : asset('assets/images/news/' . $article['image']) }}" alt="{{ $article['title'] }}" loading="lazy" width="800" height="500">
                        </a>
                        <div class="news-card-body">
                            <div class="news-date"><i class="bi bi-calendar3 me-1"></i>{{ $article['date'] }}</div>
                            <h2 class="news-title"><a href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">{{ $article['title'] }}</a></h2>
                            <p class="news-excerpt">{{ $article['excerpt'] }}</p>
                            <a class="news-readmore" href="{{ route('content.show', ['domain' => $article['domain'] ?? 'tin-tuc', 'slug' => $article['slug']]) }}">Đọc tiếp <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

