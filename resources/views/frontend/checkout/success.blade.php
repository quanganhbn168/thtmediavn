@extends('layouts.master')

@php
    $isSePay = $order->payment_method === 'sepay_qr';
    $isPaid = $order->payment_status === 'paid';
    $fullAddress = collect([$order->shipping_address, $order->shipping_ward, $order->shipping_district, $order->shipping_province])->filter()->implode(', ');
    $zaloUrl = data_get($website, 'social.zalo');
@endphp

@section('title', 'Đặt hàng thành công — '.$website['name'])

@section('content')
<section class="section-space bg-soft order-success-page">
    <div class="container">
        <div class="content-card mx-auto order-success-card">
            <div class="text-center">
                <div class="order-success-icon"><i class="bi bi-check2-circle"></i></div>
                <h1 class="h2 mt-3">{{ $isSePay && $isPaid ? 'Thanh toán thành công' : 'Đơn hàng đã được tiếp nhận' }}</h1>
                <p class="text-muted">{{ $isSePay && $isPaid ? 'RHEA đã nhận đủ tiền và chuyển đơn sang chờ xác nhận.' : 'RHEA SkinLab sẽ liên hệ xác nhận trước khi giao hàng.' }}</p>
            </div>

            @if($isSePay && ! $isPaid)
                <div class="alert alert-warning my-4">Đơn chưa được xác nhận thanh toán. <a href="{{ route('checkout.payment', $order->payment_public_token) }}">Quay lại trang QR</a>.</div>
            @endif

            <div class="order-success-details">
                <h2 class="h5">Thông tin đơn hàng</h2>
                <dl><div><dt>Mã đơn hàng</dt><dd>{{ $order->order_code }}</dd></div><div><dt>Người nhận</dt><dd>{{ $order->customer_name }}</dd></div><div><dt>Số điện thoại</dt><dd>{{ $order->customer_phone }}</dd></div><div><dt>Địa chỉ</dt><dd>{{ $fullAddress }}</dd></div><div><dt>Phương thức</dt><dd>{{ $isSePay ? 'QR ngân hàng qua SePay' : 'Thanh toán khi nhận hàng (COD)' }}</dd></div></dl>
            </div>

            <div class="order-success-products mt-4">
                <h2 class="h5">Sản phẩm</h2>
                @foreach($order->items as $item)
                    <div><span>{{ $item->product_name }} @if($item->variant_name)<small>— {{ $item->variant_name }}</small>@endif × {{ $item->quantity }}</span><strong>{{ number_format((float) $item->total_price, 0, ',', '.') }}₫</strong></div>
                @endforeach
                <div class="order-success-total"><span>Tổng thanh toán</span><strong>{{ number_format((float) $order->total_amount, 0, ',', '.') }}₫</strong></div>
            </div>

            <div class="d-flex justify-content-center flex-wrap gap-2 mt-4">
                <a class="btn btn-primary" href="{{ route('catalog') }}">Tiếp tục mua sắm</a>
                @if($isSePay && $zaloUrl)<a class="btn btn-outline-primary" href="{{ $zaloUrl }}" target="_blank" rel="noopener"><i class="bi bi-chat-dots me-1"></i>Liên hệ qua Zalo</a>@endif
                @auth<a class="btn btn-outline-primary" href="{{ route('account.orders.show', $order) }}">Xem đơn hàng</a>@endauth
            </div>
        </div>
    </div>
</section>
@endsection
