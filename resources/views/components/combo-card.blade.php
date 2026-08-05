@props(['combo'])
@php
    $price = (float) $combo->price;
    $comparePrice = (float) ($combo->compare_price ?: 0);
    $quantity = $combo->availableQuantity();
    $available = $combo->allow_preorder || $quantity === null || $quantity > 0;
    $detailUrl = route('combo.show', $combo->slug);
@endphp
<article class="product-card combo-card">
    <div class="product-card-media"><a href="{{ $detailUrl }}" aria-label="{{ $combo->name }}"><img src="{{ $combo->image_url }}" alt="{{ $combo->name }}" loading="lazy" width="600" height="600"></a><div class="product-badge-stack"><span class="product-badge new">Combo</span>@if($combo->is_featured)<span class="product-badge">Nổi bật</span>@endif</div></div>
    <div class="product-card-body"><div class="product-brand">{{ $combo->category?->name ?: 'Combo chăm sóc da' }}</div><h3 class="product-title"><a href="{{ $detailUrl }}">{{ $combo->name }}</a></h3><div class="d-flex align-items-center flex-wrap"><span class="product-price">{{ number_format($price, 0, ',', '.') }}₫</span>@if($comparePrice > $price)<span class="product-old-price">{{ number_format($comparePrice, 0, ',', '.') }}₫</span>@endif</div><div class="product-stock {{ $available ? '' : 'out' }}"><i class="bi bi-{{ $available ? 'check-circle' : 'x-circle' }} me-1"></i>{{ $available ? 'Còn hàng' : 'Tạm hết hàng' }}</div><form action="{{ route('cart.store') }}" method="POST" class="mt-3">@csrf<input type="hidden" name="combo_id" value="{{ $combo->id }}"><button class="btn btn-primary w-100" type="submit" data-add-cart data-combo-id="{{ $combo->id }}" data-product-name="{{ $combo->name }}" @disabled(! $available)><i class="bi bi-bag-plus me-1"></i>Thêm Combo</button></form></div>
</article>
