@extends('layouts.master')

@section('title', $product['name'] . ' — ' . $website['name'])
@section('meta_description', $product['name'] . ' chính hãng tại ' . $website['name'])

@section('content')
@php
    $oldPrice = $product['old_price'] ?? null;
    $discount = $oldPrice ? (int) round((1 - $product['price'] / $oldPrice) * 100) : null;
@endphp
<div class="breadcrumb-wrap">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('catalog') }}">Sản phẩm</a></li>
                <li class="breadcrumb-item active text-truncate" aria-current="page">{{ $product['name'] }}</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section-space-sm">
    <div class="container">
        <div class="row g-4 g-xl-5">
            <div class="col-lg-5">
                <div class="product-gallery-main">
                    <img data-gallery-main src="{{ $product['image'] }}" alt="{{ $product['name'] }}" width="600" height="600">
                </div>
                <div class="row row-cols-4 g-2 mt-2">
                    @foreach($gallery as $index => $image)
                        <div class="col">
                            <button class="product-thumb {{ $index === 0 ? 'active' : '' }} w-100" type="button" data-gallery-thumb="{{ $image }}" aria-label="Ảnh sản phẩm {{ $index + 1 }}">
                                <img src="{{ $image }}" alt="" width="600" height="600">
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-7">
                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="product-brand mb-2">{{ $product['brand'] }}</div>
                        <h1 class="product-detail-title">{{ $product['name'] }}</h1>
                        <div class="product-meta d-flex flex-wrap gap-2 gap-md-3">
                            <span>Thương hiệu: <a class="text-primary fw-bold" href="{{ route('catalog', ['brand' => $product['brand_slug']]) }}">{{ $product['brand'] }}</a></span>
                            <span>|</span>
                            @php
                                $availability = $product['availability'] ?? ($product['stock'] ? 'in_stock' : 'out_of_stock');
                            @endphp
                            <span>Tình trạng: <strong class="{{ $availability === 'in_stock' ? 'text-success' : ($availability === 'preorder' ? 'text-warning' : 'text-danger') }}">{{ $availability === 'in_stock' ? 'Còn hàng' : ($availability === 'preorder' ? 'Nhận đặt trước' : 'Tạm hết hàng') }}</strong></span>
                        </div>
                        <div class="product-detail-price">
                            {{ number_format($product['price'], 0, ',', '.') }}₫
                            @if($oldPrice)<del>{{ number_format($oldPrice, 0, ',', '.') }}₫</del>@endif
                            @if($discount)<span class="badge text-bg-danger align-middle ms-2">-{{ $discount }}%</span>@endif
                        </div>

                        <div class="deal-box">
                            <div class="deal-box-head d-flex align-items-center justify-content-between gap-2">
                                <span><i class="bi bi-fire me-2"></i>Hot deal</span>
                                <div class="countdown" data-countdown>
                                    <div class="countdown-unit"><strong data-days>00</strong><span>Ngày</span></div>
                                    <div class="countdown-unit"><strong data-hours>00</strong><span>Giờ</span></div>
                                    <div class="countdown-unit"><strong data-minutes>00</strong><span>Phút</span></div>
                                    <div class="countdown-unit"><strong data-seconds>00</strong><span>Giây</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="gift-box">
                            <div class="gift-box-head"><i class="bi bi-gift-fill me-2"></i>Quà tặng khuyến mãi</div>
                            <div class="gift-box-body">
                                <div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="gift1"><label class="form-check-label" for="gift1">3 miếng mặt nạ đất sét Cattier</label></div>
                                <div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="gift2"><label class="form-check-label" for="gift2">Bông tẩy trang 40 miếng</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="gift3"><label class="form-check-label" for="gift3">1 chai sữa dưỡng thể 100ml</label></div>
                            </div>
                        </div>

                        <form action="{{ route('cart.store') }}" method="post">@csrf<input type="hidden" name="product_id" value="{{ $productModel->id }}">
                        @if($productModel->variants->isNotEmpty())
                        <div class="mb-3"><div class="option-label">Phân loại:</div><div class="d-flex flex-wrap gap-2">
                            @foreach($productModel->variants->where('is_active',true) as $variant)<label class="variant-btn {{ $variant->is_default?'active':'' }}"><input class="visually-hidden" type="radio" name="variant_id" value="{{ $variant->id }}" @checked($variant->is_default || $loop->first)>{{ $variant->name ?: $variant->values->pluck('value')->join(' / ') }}</label>@endforeach
                        </div></div>
                        @endif
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="fw-bold">Số lượng:</span>
                            <div class="quantity-control" data-quantity-control>
                                <button type="button" data-quantity-action="decrease" aria-label="Giảm số lượng">−</button>
                                <input id="productQuantity" name="quantity" type="number" value="1" min="1" aria-label="Số lượng">
                                <button type="button" data-quantity-action="increase" aria-label="Tăng số lượng">+</button>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary flex-grow-1 py-3" type="submit">
                                <i class="bi bi-bag-plus me-2"></i>Thêm vào giỏ hàng
                            </button>
                            <button class="btn btn-outline-primary btn-icon flex-shrink-0" type="button" data-wishlist="{{ $product['id'] }}" aria-label="Yêu thích"><i class="bi bi-heart"></i></button>
                        </div>
                        </form><a class="btn btn-dark w-100 mt-2 py-3" href="{{ route('cart') }}">Mua ngay</a>
                    </div>
                    <div class="col-xl-4">
                        <div class="policy-card">
                            <div class="policy-card-head">Chính sách của chúng tôi</div>
                            <div class="policy-row"><i class="bi bi-truck"></i><span>Miễn phí vận chuyển theo điều kiện</span></div>
                            <div class="policy-row"><i class="bi bi-shield-check"></i><span>Bảo hành chính hãng toàn quốc</span></div>
                            <div class="policy-row"><i class="bi bi-patch-check"></i><span>Cam kết chính hãng 100%</span></div>
                            <div class="policy-row"><i class="bi bi-arrow-repeat"></i><span>Hỗ trợ đổi nếu sản phẩm lỗi</span></div>
                        </div>
                        <div class="content-card p-3 mt-3">
                            <div class="small fw-bold mb-2">Mã ưu đãi có thể áp dụng</div>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="coupon-chip" type="button" data-copy-code="MTD10">MTD10</button>
                                <button class="coupon-chip" type="button" data-copy-code="FREESHIP">FREESHIP</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space bg-soft">
    <div class="container">
        <div class="content-card product-description">
            <ul class="nav nav-pills product-tabs mb-4" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#description" type="button">Mô tả sản phẩm</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#usage" type="button">Cách sử dụng</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#policy" type="button">Chính sách đổi trả</button></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="description">
                    <h2 class="h4">Thông tin sản phẩm</h2>
                    {!! $productModel->description ?: '<p>Thông tin sản phẩm đang được cập nhật.</p>' !!}
                    <div class="description-product-callout mt-4">
                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" width="600" height="600">
                        <div>
                            <div class="product-brand">{{ $product['brand'] }}</div>
                            <div class="fw-bold mb-2">{{ $product['name'] }}</div>
                            <div class="product-price mb-2">{{ number_format($product['price'], 0, ',', '.') }}₫</div>
                            <a class="btn btn-sm btn-secondary" href="#top">Xem thông tin mua hàng</a>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="usage">
                    <h2 class="h4">Hướng dẫn sử dụng</h2>
                    {!! $productModel->usage ?: '<p>Hướng dẫn sử dụng đang được cập nhật.</p>' !!}
                </div>
                <div class="tab-pane fade" id="policy">
                    <h2 class="h4">Chính sách đổi trả</h2>
                    <p>Kiểm tra sản phẩm khi nhận hàng. Nếu giao nhầm, thiếu hàng hoặc sản phẩm có dấu hiệu lỗi, vui lòng giữ nguyên tem và bao bì rồi liên hệ cửa hàng để được hỗ trợ.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space pt-0" id="danh-gia">
    @php
        $approvedReviews = $productModel->reviews;
        $averageRating = $approvedReviews->isNotEmpty() ? (float) $approvedReviews->avg('rating') : 0;
    @endphp
    <div class="container">
        <div class="content-card">
            <div class="row g-4">
                <div class="col-lg-7">
                    <h2 class="h4 fw-bold">Đánh giá sản phẩm</h2>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <strong class="display-5 text-primary">{{ number_format($averageRating, 1) }}</strong>
                        <div><div class="text-warning">{{ str_repeat('★', (int) round($averageRating)) }}{{ str_repeat('☆', 5 - (int) round($averageRating)) }}</div><small class="text-muted">{{ $approvedReviews->count() }} đánh giá</small></div>
                    </div>
                    @forelse($approvedReviews as $review)
                        <article class="border-top py-3">
                            <div class="d-flex justify-content-between"><strong>{{ $review->name }}</strong><span class="text-warning">{{ str_repeat('★', $review->rating) }}</span></div>
                            @if($review->is_verified)<span class="badge text-bg-success my-1">Đã mua hàng</span>@endif
                            <p class="mb-0">{{ $review->content }}</p>
                        </article>
                    @empty
                        <p class="text-muted">Sản phẩm chưa có đánh giá. Hãy là người đầu tiên chia sẻ trải nghiệm.</p>
                    @endforelse
                </div>
                <div class="col-lg-5">
                    @auth
                        <h3 class="h5 fw-bold">Viết đánh giá</h3>
                        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                        <form action="{{ route('product.reviews.store', $productModel) }}" method="post">
                            @csrf
                            <div class="mb-3"><label class="form-label">Số sao</label><select class="form-select" name="rating" required><option value="5">5 sao — Rất tốt</option><option value="4">4 sao — Tốt</option><option value="3">3 sao — Bình thường</option><option value="2">2 sao — Chưa tốt</option><option value="1">1 sao — Không hài lòng</option></select></div>
                            <div class="mb-3"><label class="form-label">Nội dung</label><textarea class="form-control" name="content" rows="5" minlength="10" required></textarea></div>
                            <button class="btn btn-primary">Gửi đánh giá</button>
                        </form>
                    @else
                        <div class="p-4 bg-soft rounded-4 text-center"><p>Vui lòng đăng nhập để đánh giá sản phẩm.</p><a class="btn btn-primary" href="{{ route('login') }}">Đăng nhập</a></div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</section>

@if($relatedProducts->isNotEmpty())
<section class="section-space">
    <div class="container">
        <x-section-heading title="Có thể bạn thích" :href="route('products.by-category', ['category' => $product['category']])" />
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach($relatedProducts as $related)
                <div class="col"><x-product-card :product="$related" /></div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

