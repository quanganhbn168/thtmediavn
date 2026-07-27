@extends('layouts.master')

@php
    $productName = trim((string) $product['name']);
    $brandName = trim((string) ($product['brand'] ?? ''));
    $displayName = $productName;

    if ($brandName !== '' && $brandName !== 'Không thương hiệu' && $productName !== '') {
        $brandPrefixes = [
            $brandName . ' - ',
            $brandName . ' – ',
            $brandName . ' — ',
            $brandName . ': ',
            $brandName . ' | ',
            $brandName . ' ',
        ];

        foreach ($brandPrefixes as $prefix) {
            if (str_starts_with($productName, $prefix)) {
                $displayName = trim(substr($productName, strlen($prefix)));
                break;
            }
        }
    }
@endphp

@section('title', $displayName . ' — ' . $website['name'])
@section('meta_description', $product['name'] . ' chính hãng tại ' . $website['name'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
@endpush

@section('content')
@php
    $oldPrice = $product['old_price'] ?? null;
    $discount = $oldPrice ? (int) round((1 - $product['price'] / $oldPrice) * 100) : null;
    $giftText = trim((string) ($product['gift'] ?? ''));
    $hasGift = $giftText !== '';
    $hasSelectableVariants = $productModel->options->filter(fn($option) => $option->values->isNotEmpty())->isNotEmpty()
        && $productModel->variants->where('is_active', true)->count() > 1;
    $useOptionSelector = $hasSelectableVariants && $productModel->variant_selection_mode === 'options';
    $usedValueIds = collect($variantSelectionData)->flatMap(fn($variant) => $variant['value_ids'])->unique();
    $minimumVariantPrice = (float) (collect($variantSelectionData)->min('price') ?? $product['price']);
    $availability = $product['availability'] ?? ($product['stock'] ? 'in_stock' : 'out_of_stock');
    $summaryText = trim(strip_tags((string) $productModel->summary));
    $attributeValuesBySlug = $productModel->attributeValues->groupBy(fn ($item) => $item->attribute?->slug ?? 'khac');
    $skinTypes = $attributeValuesBySlug->get('loai-da', collect())->pluck('value');
    $skinConcerns = $attributeValuesBySlug->get('van-de', collect())->pluck('value');
    $ingredients = $attributeValuesBySlug->get('thanh-phan', collect())->merge($attributeValuesBySlug->get('thanh-phan-noi-bat', collect()))->pluck('value')->unique();
    $textures = $attributeValuesBySlug->get('ket-cau', collect())->pluck('value');
    $approvedReviews = $productModel->reviews;
    $averageRating = $approvedReviews->isNotEmpty() ? (float) $approvedReviews->avg('rating') : 0;
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

<section class="section-space-sm" id="top">
    <div class="container">
        <div class="row g-4 g-xl-5">
            <div class="col-lg-5">
                <div class="product-gallery" data-product-gallery>
                    <div class="swiper product-gallery-main" data-product-gallery-main>
                        <div class="swiper-wrapper">
                            @foreach($gallery as $index => $image)
                                <div class="swiper-slide">
                                    <img src="{{ $image }}" alt="{{ $product['name'] }}{{ $gallery->count() > 1 ? ' — Ảnh ' . ($index + 1) : '' }}" @if($index > 0) loading="lazy" @endif width="900" height="900">
                                </div>
                            @endforeach
                        </div>
                        @if($gallery->count() > 1)
                            <button class="product-gallery-nav product-gallery-prev" type="button" data-product-gallery-prev aria-label="Ảnh trước"><i class="bi bi-chevron-left"></i></button>
                            <button class="product-gallery-nav product-gallery-next" type="button" data-product-gallery-next aria-label="Ảnh tiếp theo"><i class="bi bi-chevron-right"></i></button>
                        @endif
                    </div>

                    @if($gallery->count() > 1)
                        <div class="swiper product-gallery-thumbs mt-2" data-product-gallery-thumbs>
                            <div class="swiper-wrapper">
                                @foreach($gallery as $index => $image)
                                    <button class="swiper-slide product-gallery-thumb" type="button" aria-label="Xem ảnh sản phẩm {{ $index + 1 }}">
                                        <img src="{{ $image }}" alt="" loading="lazy" width="180" height="180">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-7">
                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="product-brand mb-2">{{ $product['brand'] }}</div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <h1 class="product-detail-title mb-0">{{ $displayName }}</h1>
                            <button class="btn btn-outline-primary btn-icon flex-shrink-0" type="button" data-wishlist="{{ $product['id'] }}" aria-label="Yêu thích {{ $displayName }}">
                                <i class="bi bi-heart"></i>
                            </button>
                        </div>
                        @if($summaryText !== '')
                            <p class="product-detail-summary">{{ $summaryText }}</p>
                        @endif
                        <div class="product-purchase-proof">
                            <a href="#danh-gia"><span class="rating-stars">{{ str_repeat('★', (int) round($averageRating)) }}{{ str_repeat('☆', 5 - (int) round($averageRating)) }}</span> {{ number_format($averageRating, 1) }} ({{ $approvedReviews->count() }} đánh giá)</a>
                            <span><i class="bi bi-bag-check"></i> Đã bán {{ number_format($productModel->sold_count) }}</span>
                        </div>
                        <div class="product-meta d-flex flex-wrap gap-2 gap-md-3">
                            <span>Thương hiệu: <a class="text-primary fw-bold" href="{{ route('catalog', ['brand' => $product['brand_slug']]) }}">{{ $product['brand'] }}</a></span>
                            <span>|</span>
                            <span>Tình trạng: <strong class="{{ $availability === 'in_stock' ? 'text-positive' : ($availability === 'preorder' ? 'text-warning' : 'text-critical') }}">{{ $availability === 'in_stock' ? 'Còn hàng' : ($availability === 'preorder' ? 'Nhận đặt trước' : 'Tạm hết hàng') }}</strong></span>
                        </div>
                        <div class="product-detail-price" data-product-price>
                            <span data-current-price>{{ $useOptionSelector ? 'Từ ' . number_format($minimumVariantPrice, 0, ',', '.') : number_format($product['price'], 0, ',', '.') }}₫</span>
                            <del data-compare-price @class(['d-none' => $useOptionSelector || !$oldPrice])>{{ $oldPrice ? number_format($oldPrice, 0, ',', '.') . '₫' : '' }}</del>
                            <span data-discount-badge @class(['badge', 'bg-critical', 'align-middle', 'ms-2', 'd-none' => $useOptionSelector || !$discount])>{{ $discount ? '-' . $discount . '%' : '' }}</span>
                        </div>

                        @if($hasFlashDeal)
                        <div class="deal-box">
                            <div class="deal-box-head d-flex align-items-center justify-content-between gap-2">
                                <span><i class="bi bi-fire me-2"></i>Hot deal</span>
                                <div class="countdown" data-countdown data-deadline="{{ $flashDealUntil }}">
                                    <div class="countdown-unit"><strong data-days>00</strong><span>Ngày</span></div>
                                    <div class="countdown-unit"><strong data-hours>00</strong><span>Giờ</span></div>
                                    <div class="countdown-unit"><strong data-minutes>00</strong><span>Phút</span></div>
                                    <div class="countdown-unit"><strong data-seconds>00</strong><span>Giây</span></div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($hasGift)
                        <div class="gift-box">
                            <div class="gift-box-head"><i class="bi bi-gift-fill me-2"></i>Quà tặng khuyến mãi</div>
                            <div class="gift-box-body">{{ $giftText }}</div>
                        </div>
                        @endif

                        @if($skinTypes->isNotEmpty() || $skinConcerns->isNotEmpty())
                            <div class="product-suitability">
                                <strong>Phù hợp:</strong>
                                {{ $skinTypes->merge($skinConcerns)->unique()->implode(' · ') }}
                            </div>
                        @endif

                        <form id="product-purchase-form" action="{{ route('cart.store') }}" method="post">@csrf<input type="hidden" name="product_id" value="{{ $productModel->id }}">
                        @if($useOptionSelector)
                        <div
                            data-option-variant-picker
                            data-min-price="{{ $minimumVariantPrice }}"
                            data-track-inventory="{{ $productModel->track_inventory ? 1 : 0 }}"
                            data-allow-preorder="{{ $productModel->allow_preorder ? 1 : 0 }}"
                        >
                            <script type="application/json" data-variant-data>@json($variantSelectionData)</script>
                            <input type="hidden" name="variant_id" value="" data-selected-variant>

                            @foreach($productModel->options as $option)
                                @php
                                    $availableValues = $option->values->whereIn('id', $usedValueIds);
                                @endphp
                                @if($availableValues->isNotEmpty())
                                    <div class="mb-3" data-option-group data-option-id="{{ $option->id }}">
                                        <div class="option-label">{{ $option->name }}:</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($availableValues as $value)
                                                <button class="variant-btn" type="button" data-option-value data-value-id="{{ $value->id }}">
                                                    {{ $value->value }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @elseif($hasSelectableVariants)
                        <div
                            class="mb-3"
                            data-variant-group
                            data-track-inventory="{{ $productModel->track_inventory ? 1 : 0 }}"
                            data-allow-preorder="{{ $productModel->allow_preorder ? 1 : 0 }}"
                        >
                            <div class="option-label">Phân loại:</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($productModel->variants->where('is_active', true) as $variant)
                                    @php
                                        $variantData = collect($variantSelectionData)->firstWhere('id', (int) $variant->id);
                                    @endphp
                                    <label
                                        class="variant-btn {{ $variant->is_default || $loop->first ? 'active' : '' }}"
                                        data-variant
                                        data-price="{{ $variantData['price'] ?? $variant->price }}"
                                        data-compare-price="{{ $variantData['compare_price'] ?? '' }}"
                                        data-stock="{{ $variantData['stock'] ?? $variant->stock }}"
                                    >
                                        <input class="visually-hidden" type="radio" name="variant_id" value="{{ $variant->id }}" @checked($variant->is_default || $loop->first)>
                                        {{ $variant->name ?: $variant->values->pluck('value')->join(' / ') }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="fw-bold">Số lượng:</span>
                            <div class="quantity-control" data-quantity-control>
                                <button type="button" data-quantity-action="decrease" aria-label="Giảm số lượng">−</button>
                                <input id="productQuantity" name="quantity" type="number" value="1" min="1" aria-label="Số lượng">
                                <button type="button" data-quantity-action="increase" aria-label="Tăng số lượng">+</button>
                            </div>
                        </div>

                        @if($availability !== 'out_of_stock')
                            <div class="product-purchase-actions" data-variant-actions>
                                <button class="btn btn-outline-primary py-3" type="submit" name="action" value="add_to_cart" data-add-cart data-product-id="{{ $product['id'] }}" data-variant-id="{{ $product['variant_id'] ?? '' }}" data-product-name="{{ $product['name'] }}" data-quantity-target="#productQuantity">
                                    <i class="bi bi-bag-plus me-2"></i>Thêm vào giỏ hàng
                                </button>
                                <button class="btn btn-primary py-3" type="submit" name="action" value="buy_now" data-add-cart data-buy-now data-product-id="{{ $product['id'] }}" data-variant-id="{{ $product['variant_id'] ?? '' }}" data-product-name="{{ $product['name'] }}" data-quantity-target="#productQuantity">
                                    Mua ngay
                                </button>
                            </div>
                        @else
                            <button class="btn btn-secondary w-100 py-3" type="button" disabled>Tạm hết hàng</button>
                        @endif
                        </form>
                    </div>
                    <div class="col-xl-4">
                        <div class="policy-card">
                            <div class="policy-card-head">Chính sách của chúng tôi</div>
                            <div class="policy-row"><i class="bi bi-truck"></i><span>Phí vận chuyển được tính theo điều kiện đơn hàng</span></div>
                            <div class="policy-row"><i class="bi bi-patch-check"></i><span>Cam kết chính hãng 100%</span></div>
                            <div class="policy-row"><i class="bi bi-chat-heart"></i><span>Hỗ trợ tư vấn trước và sau khi mua</span></div>
                        </div>
                        @if($availableCoupons->isNotEmpty())
                        <div class="content-card p-3 mt-3">
                            <div class="small fw-bold mb-2">Mã ưu đãi có thể áp dụng</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($availableCoupons as $coupon)
                                    <button class="coupon-chip" type="button" data-copy-code="{{ $coupon->code }}">{{ $coupon->code }}</button>
                                @endforeach
                            </div>
                        </div>
                        @endif
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
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#description" type="button">Dành cho ai?</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#benefits" type="button">Công dụng chính</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#usage" type="button">Thành phần &amp; routine</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#specs" type="button">Thông tin sản phẩm</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#policy" type="button">Chính sách đổi trả</button></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="description">
                        <h2 class="h4">Sản phẩm này dành cho ai?</h2>
                        @if($skinTypes->isNotEmpty())<p><strong>Loại da phù hợp:</strong> {{ $skinTypes->implode(', ') }}.</p>@endif
                        @if($skinConcerns->isNotEmpty())<p><strong>Vấn đề da:</strong> {{ $skinConcerns->implode(', ') }}.</p>@endif
                        @if($textures->isNotEmpty())<p><strong>Kết cấu:</strong> {{ $textures->implode(', ') }}.</p>@endif
                        <p class="text-muted">Khi da đang kích ứng mạnh, có tổn thương hoặc đang theo liệu trình chuyên môn, nên hỏi người phụ trách điều trị trước khi thêm sản phẩm mới.</p>
                        <div class="description-product-callout mt-4">
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" width="600" height="600">
                            <div>
                                <div class="product-brand">{{ $product['brand'] }}</div>
                                <div class="fw-bold mb-2">{{ $displayName }}</div>
                                <div class="product-price mb-2">{{ number_format($product['price'], 0, ',', '.') }}₫</div>
                                <a class="btn btn-sm btn-secondary" href="#top">Xem thông tin mua hàng</a>
                            </div>
                        </div>
                </div>
                <div class="tab-pane fade" id="benefits">
                    <h2 class="h4">Công dụng chính</h2>
                    @if($summaryText !== '')<p class="lead fs-6">{{ $summaryText }}</p>@endif
                    {!! $productModel->description ?: '<p>Thông tin công dụng đang được cập nhật.</p>' !!}
                </div>
                <div class="tab-pane fade" id="usage">
                    <h2 class="h4">Thành phần nổi bật</h2>
                    @if($ingredients->isNotEmpty())
                        <ul class="product-ingredient-list">@foreach($ingredients as $ingredient)<li>{{ $ingredient }}</li>@endforeach</ul>
                    @else
                        <p class="text-muted">Thông tin thành phần nổi bật đang được cập nhật.</p>
                    @endif
                    <h2 class="h4 mt-4">Cách đưa vào routine</h2>
                    {!! $productModel->usage ?: '<p>Hướng dẫn sử dụng đang được cập nhật.</p>' !!}
                    <div class="routine-guide mt-3"><strong>Nguyên tắc chung:</strong> làm sạch → sản phẩm đặc trị/serum → kem dưỡng → chống nắng vào ban ngày. Ưu tiên hướng dẫn riêng trên bao bì sản phẩm.</div>
                </div>
                <div class="tab-pane fade" id="specs">
                    <h2 class="h4">Thông tin sản phẩm</h2>
                    @if($productModel->attributeValues->isNotEmpty())
                                     <div class="table-responsive">
                                         <table class="table table-sm table-borderless">
                                             <tbody>
                                    @php
                                        $attributeGroups = $productModel->attributeValues->groupBy(fn ($item) => $item->attribute?->name ?? 'Thông số');
                                    @endphp
                                    @foreach($attributeGroups as $attributeName => $values)
                                        <tr>
                                            <th class="pe-3" style="width: 220px;">{{ $attributeName }}</th>
                                            <td>{{ $values->pluck('value')->implode(', ') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Thông tin sản phẩm đang được cập nhật.</p>
                    @endif
                </div>
                <div class="tab-pane fade" id="policy">
                    <h2 class="h4">Chính sách đổi trả</h2>
                    <p>Kiểm tra tên sản phẩm, số lượng và tình trạng bao bì khi nhận hàng. Nếu giao nhầm, thiếu hàng hoặc sản phẩm có dấu hiệu lỗi, vui lòng giữ nguyên tem và bao bì rồi liên hệ RHEA để được kiểm tra.</p>
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('policies.returns') }}">Xem chính sách đổi trả</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space pt-0" id="danh-gia">
    <div class="container">
        <div class="content-card">
            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <h2 class="h4 fw-bold">Đánh giá sản phẩm</h2>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <strong class="display-5 text-primary">{{ number_format($averageRating, 1) }}</strong>
                        <div><div class="rating-stars">{{ str_repeat('★', (int) round($averageRating)) }}{{ str_repeat('☆', 5 - (int) round($averageRating)) }}</div><small class="text-muted">{{ $approvedReviews->count() }} đánh giá</small></div>
                    </div>
                    @forelse($approvedReviews as $review)
                        <article class="border-top py-3">
                            <div class="d-flex justify-content-between"><strong>{{ $review->name }}</strong><span class="rating-stars">{{ str_repeat('★', $review->rating) }}</span></div>
                            @if($review->is_verified)<span class="badge bg-positive my-1">Đã mua hàng</span>@endif
                            <p class="mb-0">{{ $review->content }}</p>
                        </article>
                    @empty
                        <p class="text-muted">Sản phẩm chưa có đánh giá. Hãy là người đầu tiên chia sẻ trải nghiệm.</p>
                    @endforelse
                </div>
                <div class="col-lg-4">
                    <div class="review-scope-note">
                        <i class="bi bi-patch-check-fill"></i>
                        <h3 class="h5">Đánh giá được kiểm duyệt</h3>
                        <p class="mb-0">Trang chỉ hiển thị đánh giá đã được RHEA duyệt hoặc xác minh từ đơn hàng.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if($relatedProducts->isNotEmpty())
<section class="section-space">
    <div class="container">
        <x-section-heading title="Có thể bạn thích" :href="route('content.show', ['domain' => 'danh-muc', 'slug' => $product['category']])" />
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
            @foreach($relatedProducts as $related)
                <div class="col"><x-product-card :product="$related" /></div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($availability !== 'out_of_stock')
<div class="product-mobile-buybar" data-variant-actions>
    <div><small>Giá sản phẩm</small><strong data-mobile-product-price>{{ number_format($product['price'], 0, ',', '.') }}₫</strong></div>
    <button class="btn btn-outline-primary" type="submit" form="product-purchase-form" data-add-cart data-purchase-form="#product-purchase-form" data-product-id="{{ $product['id'] }}" data-variant-id="{{ $product['variant_id'] ?? '' }}" data-product-name="{{ $product['name'] }}" data-quantity-target="#productQuantity">Thêm giỏ</button>
    <button class="btn btn-primary" type="submit" form="product-purchase-form" data-add-cart data-buy-now data-purchase-form="#product-purchase-form" data-product-id="{{ $product['id'] }}" data-variant-id="{{ $product['variant_id'] ?? '' }}" data-product-name="{{ $product['name'] }}" data-quantity-target="#productQuantity">Mua ngay</button>
</div>
@endif

@endsection

