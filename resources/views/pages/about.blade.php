@extends('layouts.master')

@section('title', 'Giới thiệu — ' . $website['name'])

@section('content')
<div class="breadcrumb-wrap"><div class="container"><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item active">Giới thiệu</li></ol></nav></div></div>
<section class="page-hero"><div class="container"><h1>{{ $website['name'] }}</h1><p>{{ $website['tagline'] }}.</p></div></section>
<section class="section-space">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6"><img class="w-100 rounded-4 shadow-sm" src="{{ asset('assets/images/banners/hero-main.svg') }}" alt="{{ $website['name'] }}" width="1400" height="560"></div>
            <div class="col-lg-6">
                <h2 class="display-6 fw-black">Lựa chọn làm đẹp rõ ràng và phù hợp</h2>
                <p class="text-muted">Giao diện giới thiệu được thiết kế để trình bày câu chuyện thương hiệu, tiêu chí chọn sản phẩm và cam kết dịch vụ. Thay nội dung mẫu bằng thông tin doanh nghiệp đã được phê duyệt.</p>
                <div class="row g-3 mt-2">
                    <div class="col-6"><div class="content-card p-3 h-100"><div class="display-6 fw-black text-primary">100+</div><div class="text-muted">Thương hiệu lựa chọn</div></div></div>
                    <div class="col-6"><div class="content-card p-3 h-100"><div class="display-6 fw-black text-primary">4</div><div class="text-muted">Nhóm sản phẩm chính</div></div></div>
                </div>
            </div>
        </div>
        <div class="row g-4 mt-4">
            <div class="col-md-4"><div class="content-card h-100"><i class="bi bi-patch-check display-6 text-primary"></i><h3 class="h5 fw-bold mt-3">Chất lượng</h3><p class="text-muted mb-0">Ưu tiên nguồn gốc rõ ràng và thông tin sản phẩm minh bạch.</p></div></div>
            <div class="col-md-4"><div class="content-card h-100"><i class="bi bi-shield-heart display-6 text-primary"></i><h3 class="h5 fw-bold mt-3">An toàn</h3><p class="text-muted mb-0">Tư vấn theo nhu cầu và lưu ý sử dụng phù hợp với từng làn da.</p></div></div>
            <div class="col-md-4"><div class="content-card h-100"><i class="bi bi-chat-heart display-6 text-primary"></i><h3 class="h5 fw-bold mt-3">Đồng hành</h3><p class="text-muted mb-0">Hỗ trợ trước và sau mua qua hotline cùng các kênh trực tuyến.</p></div></div>
        </div>
    </div>
</section>
@endsection

