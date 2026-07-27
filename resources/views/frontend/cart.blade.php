@extends('layouts.master')

@section('title', 'Giỏ hàng — '.$website['name'])

@section('content')
<div class="breadcrumb-wrap"><div class="container"><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item active">Giỏ hàng</li></ol></nav></div></div>
<section class="section-space">
    <div class="container">
        <x-section-heading title="Giỏ hàng của bạn" :center="false" />
        @if($errors->any())<div class="alert alert-critical">{{ $errors->first() }}</div>@endif
        @if(session('success'))<div class="alert alert-positive">{{ session('success') }}</div>@endif
        @if($summary['unavailableItems']->isNotEmpty())
            <div class="alert alert-warning">Giỏ hàng có sản phẩm không còn khả dụng. Hãy gỡ các sản phẩm được đánh dấu trước khi thanh toán.</div>
        @endif

        @if($cart->items->isEmpty())
            <div class="content-card text-center py-5"><i class="bi bi-bag display-3 text-primary"></i><h2 class="h4 mt-3">Giỏ hàng đang trống</h2><p class="text-muted">Hãy chọn sản phẩm phù hợp để bắt đầu đơn hàng.</p><a class="btn btn-primary" href="{{ route('catalog') }}">Mua sắm ngay</a></div>
        @else
            <div data-cart-content>
            <div class="mb-4"><div class="d-flex justify-content-between gap-3 small mb-2"><span data-shipping-message>{{ $summary['freeShippingRemain'] > 0 ? 'Mua thêm '.number_format($summary['freeShippingRemain'],0,',','.').'₫ để được miễn phí vận chuyển' : 'Đơn hàng đã được miễn phí vận chuyển' }}</span><span data-shipping-percent>{{ $summary['freeShippingPercent'] }}%</span></div><div class="shipping-progress"><div class="shipping-progress-bar" data-shipping-progress style="width: {{ $summary['freeShippingPercent'] }}%"></div></div></div>
            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <div class="cart-table-wrap"><table class="table cart-table align-middle mb-0"><thead><tr><th class="p-3">Thông tin sản phẩm</th><th class="p-3 text-end">Đơn giá</th><th class="p-3 text-center">Số lượng</th><th class="p-3 text-end">Thành tiền</th></tr></thead><tbody>
                    @foreach($cart->items as $item)
                        @php($itemUnavailable = $summary['unavailableItems']->contains('id', $item->id))
                        <tr data-cart-row data-unit-price="{{ $itemUnavailable ? 0 : $item->unit_price }}" class="{{ $itemUnavailable ? 'opacity-75' : '' }}">
                            <td class="p-3"><div class="d-flex gap-3 align-items-center"><img class="cart-product-img" src="{{ $item->variant?->image ?: $item->product->image_url }}" alt="{{ $item->product->name }}" width="600" height="600"><div>@if($itemUnavailable)<span class="fw-bold d-block">{{ $item->product->name }}</span><span class="badge text-bg-warning">Không còn khả dụng</span>@else<a class="fw-bold d-block" href="{{ route('content.show', ['domain' => 'san-pham', 'slug' => $item->product->slug]) }}">{{ $item->product->name }}</a>@endif @if($item->variant)<div class="small text-muted mt-1">{{ $item->variant->name }}</div>@endif<form action="{{ route('cart.destroy',$item) }}" method="post" class="mt-2" data-cart-remove-form>@csrf @method('DELETE')<button @class(['btn', 'btn-sm', 'btn-outline-danger' => $itemUnavailable, 'btn-link text-critical p-0' => ! $itemUnavailable])>Xóa khỏi giỏ</button></form></div></div></td>
                            <td class="p-3 text-lg-end"><strong class="text-primary">{{ $itemUnavailable ? '—' : number_format($item->unit_price,0,',','.').'₫' }}</strong></td>
                            <td class="p-3">@unless($itemUnavailable)<form action="{{ route('cart.update',$item) }}" method="post" class="quantity-control mx-lg-auto" data-cart-quantity-form>@csrf @method('PATCH')<button name="quantity" value="{{ max(0,$item->quantity-1) }}" aria-label="Giảm số lượng">−</button><input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99" data-cart-quantity><button name="quantity" value="{{ $item->quantity+1 }}" aria-label="Tăng số lượng">+</button></form>@else<span class="d-block text-center">{{ $item->quantity }}</span>@endunless</td>
                            <td class="p-3 text-lg-end"><strong data-cart-line-total>{{ $itemUnavailable ? '—' : number_format($item->unit_price*$item->quantity,0,',','.').'₫' }}</strong></td>
                        </tr>
                    @endforeach
                    </tbody></table></div>
                    <a class="btn btn-outline-primary mt-3" href="{{ route('catalog') }}"><i class="bi bi-arrow-left me-2"></i>Tiếp tục mua hàng</a>
                </div>
                <aside class="col-lg-4"><div class="cart-summary"><h2 class="h5 fw-bold mb-3">Tóm tắt đơn hàng</h2>
                    <form action="{{ route('cart.coupon') }}" method="post" class="input-group mb-3" data-cart-coupon-form>@csrf<input class="form-control" name="coupon" value="{{ $cart->coupon_code }}" placeholder="Mã giảm giá"><button class="btn btn-outline-primary">Áp dụng</button></form>
                    <form action="{{ route('cart.coupon.destroy') }}" method="post" class="d-flex justify-content-between align-items-center mb-3 {{ $summary['coupon'] ? '' : 'd-none' }}" data-cart-remove-coupon>@csrf @method('DELETE')<span class="coupon-chip" data-cart-coupon-code>{{ $summary['coupon']?->code }}</span><button class="btn btn-link text-critical btn-sm">Gỡ mã</button></form>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Tạm tính</span><strong data-cart-subtotal>{{ number_format($summary['subtotal'],0,',','.') }}₫</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Giảm giá</span><span data-cart-discount>-{{ number_format($summary['discount'],0,',','.') }}₫</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Phí vận chuyển</span><span data-cart-shipping>{{ $summary['shipping'] ? number_format($summary['shipping'],0,',','.').'₫' : 'Miễn phí' }}</span></div><hr>
                    <div class="d-flex align-items-center justify-content-between mb-3"><span class="fw-bold">Tổng tiền</span><strong class="text-primary fs-4" data-cart-total>{{ number_format($summary['total'],0,',','.') }}₫</strong></div>
                    @if($summary['unavailableItems']->isEmpty())
                        <a class="btn btn-primary w-100 py-3" href="{{ route('checkout') }}" data-cart-checkout><i class="bi bi-shield-lock me-2"></i>Tiến hành thanh toán</a>
                    @else
                        <button class="btn btn-secondary w-100 py-3" type="button" disabled data-cart-checkout>Gỡ sản phẩm không khả dụng để tiếp tục</button>
                    @endif
                    <div class="small text-muted text-center mt-2">Hỗ trợ COD và chuyển khoản</div>
                </div></aside>
            </div>
            </div>
        @endif
    </div>
</section>
@endsection

