@extends('layouts.master')

@section('title', $article['title'] . ' — ' . $website['name'])
@section('meta_description', $article['excerpt'])

@section('content')
<div class="breadcrumb-wrap">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('news.index') }}">Tin tức</a></li>
                <li class="breadcrumb-item active text-truncate" aria-current="page">{{ $article['title'] }}</li>
            </ol>
        </nav>
    </div>
</div>

<article class="section-space">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <header class="text-center mb-4">
                    <div class="news-date mb-2"><i class="bi bi-calendar3 me-1"></i>{{ $article['date'] }}</div>
                    <h1 class="display-6 fw-black">{{ $article['title'] }}</h1>
                    <p class="lead text-muted">{{ $article['excerpt'] }}</p>
                </header>
                <img class="w-100 rounded-4 shadow-sm mb-4" src="{{ str_starts_with($article['image'], 'http') || str_starts_with($article['image'], '/') ? $article['image'] : asset('assets/images/news/' . $article['image']) }}" alt="{{ $article['title'] }}" width="800" height="500">
                <div class="content-card product-description">
                    <p>Chăm sóc da hiệu quả không bắt đầu từ việc mua thật nhiều sản phẩm. Điều quan trọng hơn là hiểu tình trạng da, chọn nhóm hoạt chất phù hợp và duy trì quy trình ổn định đủ lâu để đánh giá kết quả.</p>
                    <h2 class="h4 mt-4">1. Bắt đầu từ nền da sạch và đủ ẩm</h2>
                    <p>Làm sạch vừa đủ giúp loại bỏ bụi bẩn, dầu thừa và cặn trang điểm mà không làm mất cân bằng hàng rào bảo vệ. Sau đó, sử dụng sản phẩm dưỡng ẩm có kết cấu phù hợp với loại da.</p>
                    <h2 class="h4 mt-4">2. Ưu tiên sản phẩm có mục tiêu rõ ràng</h2>
                    <p>Mỗi giai đoạn chỉ nên tập trung vào một hoặc hai vấn đề chính, chẳng hạn thiếu ẩm, thâm sau mụn hoặc bề mặt da không đều. Cách này giúp dễ theo dõi phản ứng và hạn chế chồng chéo hoạt chất.</p>

                    <h3 class="h5 mt-4 text-danger">Sản phẩm gợi ý dành cho bạn:</h3>
                    <div class="description-product-callout mt-3">
                        <img src="{{ $linkedProduct['image'] }}" alt="{{ $linkedProduct['name'] }}" width="600" height="600">
                        <div>
                            <div class="product-brand">{{ $linkedProduct['brand'] }}</div>
                            <div class="fw-bold mb-2">{{ $linkedProduct['name'] }}</div>
                            <div class="product-price mb-2">{{ number_format($linkedProduct['price'], 0, ',', '.') }}₫</div>
                            <a class="btn btn-sm btn-secondary" href="{{ route('product.show', $linkedProduct['slug']) }}">Xem chi tiết</a>
                        </div>
                    </div>

                    <h2 class="h4 mt-4">3. Duy trì chống nắng hằng ngày</h2>
                    <p>Chống nắng là bước nền tảng để bảo vệ kết quả của toàn bộ quy trình. Nên thoa đủ lượng, chọn chỉ số phù hợp và thoa lại khi hoạt động ngoài trời hoặc tiếp xúc ánh sáng kéo dài.</p>
                    <div class="alert alert-light border mt-4 mb-0">
                        Nội dung bài viết đang được cập nhật.
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>

<section class="section-space bg-soft">
    <div class="container">
        <x-section-heading title="Bài viết liên quan" />
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            @foreach($relatedNews as $related)
                <div class="col">
                    <article class="news-card">
                        <a class="news-card-image d-block" href="{{ route('content.show', ['domain' => $related['domain'] ?? 'tin-tuc', 'slug' => $related['slug']]) }}"><img src="{{ str_starts_with($related['image'], 'http') || str_starts_with($related['image'], '/') ? $related['image'] : asset('assets/images/news/' . $related['image']) }}" alt="{{ $related['title'] }}" loading="lazy" width="800" height="500"></a>
                        <div class="news-card-body">
                            <div class="news-date">{{ $related['date'] }}</div>
                            <h2 class="news-title"><a href="{{ route('content.show', ['domain' => $related['domain'] ?? 'tin-tuc', 'slug' => $related['slug']]) }}">{{ $related['title'] }}</a></h2>
                            <a class="news-readmore" href="{{ route('content.show', ['domain' => $related['domain'] ?? 'tin-tuc', 'slug' => $related['slug']]) }}">Đọc tiếp <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

