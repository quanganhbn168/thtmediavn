@extends('layouts.master')

@section('title', 'Chính sách đổi trả — '.$website['name'])

@section('content')
<div class="breadcrumb-wrap"><div class="container"><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item active">Chính sách đổi trả</li></ol></nav></div></div>
<section class="section-space bg-soft">
    <div class="container"><article class="content-card policy-content mx-auto">
        <h1 class="h2">Chính sách đổi trả</h1>
        <p>Khách hàng nên kiểm tra tên sản phẩm, số lượng và tình trạng bao bì khi nhận hàng.</p>
        <h2 class="h4">Khi cần hỗ trợ đổi trả</h2>
        <p>Giữ nguyên sản phẩm, tem và bao bì; chụp lại tình trạng nhận hàng rồi liên hệ RHEA trước khi gửi sản phẩm về. Cửa hàng sẽ kiểm tra trường hợp giao nhầm, thiếu hàng hoặc lỗi sản phẩm và hướng dẫn phương án phù hợp.</p>
        <p class="mb-0">Hotline hỗ trợ: <a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $website['phone']) }}">{{ $website['phone'] }}</a>.</p>
    </article></div>
</section>
@endsection
