@props(['product', 'showSold' => false])
@php
    $oldPrice = $product['old_price'] ?? null;
    $discount = $oldPrice ? max(1, (int) round((1 - ($product['price'] / $oldPrice)) * 100)) : null;
    $imageUrl = str_starts_with($product['image'], 'http') || str_starts_with($product['image'], '/')
        ? $product['image']
        : asset('assets/images/products/' . $product['image']);
    $priceText = number_format($product['price'], 0, ',', '.') . '₫';
    $oldPriceText = $oldPrice ? number_format($oldPrice, 0, ',', '.') . '₫' : '';
    $availability = $product['availability'] ?? (($product['stock'] ?? true) ? 'in_stock' : 'out_of_stock');
    $availabilityMeta = match ($availability) {
        'preorder' => ['class' => 'preorder', 'icon' => 'clock-history', 'label' => 'Nhận đặt trước'],
        'out_of_stock' => ['class' => 'out', 'icon' => 'x-circle', 'label' => 'Tạm hết hàng'],
        default => ['class' => '', 'icon' => 'check-circle', 'label' => 'Còn hàng'],
    };
@endphp
<article class="product-card">
    <div class="product-card-media">
        <a href="{{ route('content.show', ['domain' => 'san-pham', 'slug' => $product['slug']]) }}" aria-label="{{ $product['name'] }}">
            <img src="{{ $imageUrl }}" alt="{{ $product['name'] }}" loading="lazy" width="600" height="600">
        </a>
        <div class="product-badge-stack">
            @foreach($product['badges'] ?? [] as $badge)
                <span class="product-badge {{ in_array($badge, ['Mới', 'Combo']) ? 'new' : '' }}">{{ $badge }}</span>
            @endforeach
            @if($discount)
                <span class="product-badge sale">-{{ $discount }}%</span>
            @endif
        </div>
        <button class="wishlist-btn" type="button" data-wishlist="{{ $product['id'] }}" aria-label="Thêm {{ $product['name'] }} vào yêu thích" aria-pressed="false">
            <i class="bi bi-heart"></i>
        </button>
        <div class="product-card-actions">
        <button class="btn" type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#quickViewModal"
                    data-image="{{ $imageUrl }}"
                    data-brand="{{ $product['brand'] }}"
                    data-title="{{ $product['name'] }}"
                    data-price="{{ $priceText }}"
                    data-old-price="{{ $oldPriceText }}"
                    data-product-id="{{ $product['id'] }}"
                    data-variant-id="{{ $product['variant_id'] ?? '' }}"
                    aria-label="Xem nhanh">
            <i class="bi bi-eye"></i>
        </button>
        <button class="btn" type="button" data-add-cart data-product-id="{{ $product['id'] }}" data-variant-id="{{ $product['variant_id'] ?? '' }}" data-product-name="{{ $product['name'] }}" aria-label="Thêm vào giỏ">
            <i class="bi bi-bag-plus"></i>
        </button>
        </div>
    </div>
    <div class="product-card-body">
        <div class="product-brand">{{ $product['brand'] }}</div>
        <h3 class="product-title"><a href="{{ route('content.show', ['domain' => 'san-pham', 'slug' => $product['slug']]) }}">{{ $product['name'] }}</a></h3>
        <div class="d-flex align-items-center flex-wrap">
            <span class="product-price">{{ $priceText }}</span>
            @if($oldPrice)
                <span class="product-old-price">{{ $oldPriceText }}</span>
                <span class="product-discount">-{{ $discount }}%</span>
            @endif
        </div>
        @if($showSold)
            <div class="product-sold">
                <div class="product-sold-bar">
                    <div class="product-sold-progress" style="--sold: {{ $product['sold'] ?? 50 }}%"></div>
                    <div class="product-sold-text"><i class="bi bi-fire me-1"></i>{{ $product['sold_text'] ?? 'Đang bán chạy' }}</div>
                </div>
            </div>
        @endif
        @if(!empty($product['gift']))
            <div class="product-gift"><i class="bi bi-gift-fill me-1"></i>{{ $product['gift'] }}</div>
        @endif
        @unless($showSold)
            <div class="product-stock {{ $availabilityMeta['class'] }}">
                <i class="bi bi-{{ $availabilityMeta['icon'] }} me-1"></i>
                {{ $availabilityMeta['label'] }}
            </div>
        @endunless
    </div>
</article>
