@extends('layouts.master')

@section('title', 'Chính sách mua hàng — '.$website['name'])

@section('content')
<div class="breadcrumb-wrap"><div class="container"><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item active">Chính sách mua hàng</li></ol></nav></div></div>
<section class="section-space bg-soft">
    <div class="container"><article class="content-card policy-content mx-auto">
        <h1 class="h2">Chính sách mua hàng</h1>
        <p>RHEA tiếp nhận đơn hàng trên website, kiểm tra tình trạng hàng và liên hệ xác nhận trước khi giao.</p>
        <h2 class="h4">Đặt hàng và xác nhận</h2>
        <p>Khách hàng cần cung cấp đúng tên, số điện thoại và địa chỉ nhận hàng. Đơn chỉ được xử lý khi sản phẩm còn khả dụng và thông tin giao nhận hợp lệ.</p>
        <h2 class="h4">Thanh toán</h2>
        <p>Đơn hàng hỗ trợ thanh toán khi nhận hàng. Chuyển khoản thủ công chỉ xuất hiện khi cửa hàng đã công bố đầy đủ thông tin tài khoản; nội dung chuyển khoản phải đúng mã được hiển thị trên trang xác nhận.</p>
        <h2 class="h4">Phí vận chuyển</h2>
        <p>Phí vận chuyển và điều kiện miễn phí vận chuyển được tính trực tiếp trong giỏ hàng trước khi khách đặt đơn.</p>
        <p class="mb-0">Cần hỗ trợ? Liên hệ <a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $website['phone']) }}">{{ $website['phone'] }}</a>.</p>
    </article></div>
</section>
@endsection
