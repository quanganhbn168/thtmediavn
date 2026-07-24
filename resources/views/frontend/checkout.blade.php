@extends('layouts.master')
@section('title','Thanh toán — '.$website['name'])
@section('content')
<div class="breadcrumb-wrap"><div class="container"><nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('cart') }}">Giỏ hàng</a></li><li class="breadcrumb-item active">Thanh toán</li></ol></nav></div></div>
<section class="section-space checkout-page"><div class="container-xl">
    <div class="alert alert-critical {{ $errors->any() ? '' : 'd-none' }}" data-checkout-errors>{{ $errors->first() }}</div>
<form action="{{ route('checkout.store') }}" method="post" data-checkout-form>@csrf<div class="row g-4 align-items-start"><div class="col-lg-8 checkout-main-column"><div class="row g-4"><div class="col-md-6">
<div class="content-card mb-3"><div class="d-flex align-items-center justify-content-between gap-2 mb-3"><h2 class="h5 fw-bold mb-0">Thông tin mua hàng</h2>@guest<a class="small fw-semibold" href="{{ route('login', ['redirect' => route('checkout', [], false)]) }}"><i class="bi bi-person-circle me-1"></i>Đăng nhập</a>@endguest</div>@guest<p class="small text-muted mt-n2">Đăng nhập để tự điền thông tin và theo dõi đơn hàng.</p>@endguest<div class="row g-3"><div class="col-12"><label class="form-label">Họ và tên *</label><input class="form-control" name="customer_name" value="{{ old('customer_name',auth()->user()?->name) }}" required></div><div class="col-12"><label class="form-label">Số điện thoại *</label><input class="form-control" name="customer_phone" value="{{ old('customer_phone',auth()->user()?->phone) }}" required></div><div class="col-12"><label class="form-label">Email</label><input class="form-control" type="email" name="customer_email" value="{{ old('customer_email',auth()->user()?->email) }}"></div></div></div>
<div class="content-card mb-3"><h2 class="h5 fw-bold mb-3">Địa chỉ giao hàng</h2><div class="row g-3"><div class="col-12"><label class="form-label">Tỉnh/Thành phố *</label><input class="form-control" name="shipping_province" value="{{ old('shipping_province') }}" required></div><div class="col-12"><label class="form-label">Quận/Huyện</label><input class="form-control" name="shipping_district" value="{{ old('shipping_district') }}"></div><div class="col-12"><label class="form-label">Phường/Xã</label><input class="form-control" name="shipping_ward" value="{{ old('shipping_ward') }}"></div><div class="col-12"><label class="form-label">Địa chỉ cụ thể *</label><input class="form-control" name="shipping_address" value="{{ old('shipping_address') }}" required></div><div class="col-12"><label class="form-label">Ghi chú</label><textarea class="form-control" name="note" rows="3">{{ old('note') }}</textarea></div></div></div>
</div><div class="col-md-6">
<div class="content-card mb-4"><h2 class="h5 fw-bold mb-3">Vận chuyển</h2><div class="d-flex align-items-center justify-content-between gap-3 border rounded-3 p-3"><span><i class="bi bi-truck text-primary me-2"></i>Phí vận chuyển</span><strong>{{ $summary['shipping'] ? number_format($summary['shipping'], 0, ',', '.').'₫' : 'Miễn phí' }}</strong></div></div>
<div class="content-card"><h2 class="h5 fw-bold mb-3">Phương thức thanh toán</h2><div class="form-check border rounded-3 p-3 ps-5 mb-2"><input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked><label class="form-check-label fw-bold" for="cod">Thanh toán khi nhận hàng (COD)</label></div><div class="form-check border rounded-3 p-3 ps-5"><input class="form-check-input" type="radio" name="payment_method" id="bank" value="bank_transfer"><label class="form-check-label fw-bold" for="bank">Chuyển khoản ngân hàng</label></div></div>
</div></div></div><aside class="col-lg-4 checkout-order-column"><div class="cart-summary sticky-lg-top" style="top:1rem"><h2 class="h5 fw-bold mb-3">Đơn hàng của bạn</h2>@foreach($cart->items as $item)<div class="d-flex gap-2 py-2 border-bottom"><img class="cart-product-img" src="{{ $item->variant?->image ?: $item->product->image_url }}" alt="" width="80" height="80"><div class="small flex-grow-1"><strong>{{ $item->product->name }}</strong>@if($item->variant)<div class="text-primary mt-1">{{ $item->variant->values->map(fn ($value) => ($value->option?->name ? $value->option->name.': ' : '').$value->value)->join(' · ') ?: $item->variant->name }}</div>@endif<div class="text-muted">SL: {{ $item->quantity }}</div></div><strong class="small">{{ number_format($item->unit_price*$item->quantity,0,',','.') }}₫</strong></div>@endforeach<div class="d-flex justify-content-between mt-3"><span>Tạm tính</span><strong>{{ number_format($summary['subtotal'],0,',','.') }}₫</strong></div><div class="d-flex justify-content-between mt-2"><span>Giảm giá</span><span>-{{ number_format($summary['discount'],0,',','.') }}₫</span></div><div class="d-flex justify-content-between mt-2"><span>Vận chuyển</span><span>{{ $summary['shipping']?number_format($summary['shipping'],0,',','.').'₫':'Miễn phí' }}</span></div><hr><div class="d-flex justify-content-between fs-5"><strong>Tổng cộng</strong><strong class="text-primary">{{ number_format($summary['total'],0,',','.') }}₫</strong></div><button class="btn btn-primary w-100 py-3 mt-3" type="submit" data-checkout-submit>Đặt hàng</button><p class="small text-muted text-center mt-2 mb-0">Bằng việc đặt hàng, anh/chị đồng ý với chính sách mua hàng.</p></div></aside></div></form></div></section>
@endsection

@push('styles')
<style>
    .checkout-page {
        background: var(--surface);
    }
    .checkout-main-column {
        padding-right: clamp(1rem, 2.5vw, 2.75rem);
    }
    .checkout-main-column .content-card {
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .checkout-order-column {
        align-self: stretch;
        padding: 0;
        border-left: 1px solid var(--line);
        background: var(--canvas);
    }
    .checkout-order-column .cart-summary {
        min-height: calc(100vh - 2rem);
        padding: clamp(1.5rem, 2.5vw, 2.5rem);
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    @media (max-width: 991.98px) {
        .checkout-main-column {
            padding-right: calc(var(--bs-gutter-x) * .5);
        }
        .checkout-order-column {
            padding: 0 calc(var(--bs-gutter-x) * .5);
            border-left: 0;
            background: transparent;
        }
        .checkout-order-column .cart-summary {
            min-height: auto;
            padding: 1.25rem;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--surface);
        }
    }
</style>
@endpush

