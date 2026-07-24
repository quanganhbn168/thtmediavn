@extends('layouts.master')

@section('title', 'Liên hệ — ' . $website['name'])

@section('content')
<div class="breadcrumb-wrap">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Liên hệ</li>
            </ol>
        </nav>
    </div>
</div>
<section class="page-hero">
    <div class="container">
        <h1>Liên hệ {{ $website['name'] }}</h1>
        <p>Gửi yêu cầu tư vấn sản phẩm, đơn hàng hoặc chính sách.</p>
    </div>
</section>
<section class="section-space">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="contact-info-card">
                    <div class="h3 fw-black text-white mb-4">{{ $website['name'] }}</div>
                    <h2 class="h4 fw-bold">{{ $website['company'] }}</h2>
                    <p class="text-white-50">Tiếp nhận tư vấn mua hàng và hỗ trợ sau bán.</p>
                    <div class="contact-info-row"><i class="bi bi-geo-alt-fill"></i><span>{{ $website['address'] }}</span></div>
                    <div class="contact-info-row"><i class="bi bi-telephone-fill"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $website['phone']) }}">{{ $website['phone'] }}</a></div>
                    <div class="contact-info-row"><i class="bi bi-envelope-fill"></i><a href="mailto:{{ $website['email'] }}">{{ $website['email'] }}</a></div>
                    <div class="contact-info-row"><i class="bi bi-clock-fill"></i><span>08:00 – 21:00, Thứ 2 – Chủ nhật</span></div>
                    <div class="social-links mt-4">
                        <a class="social-link" href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a class="social-link" href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a class="social-link" href="#" aria-label="Messenger"><i class="bi bi-messenger"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="content-card">
                    <h2 class="h3 fw-bold mb-4">Chúng tôi có thể hỗ trợ gì?</h2>
                    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                    <form class="row g-3" action="{{ route('contact.submit') }}" method="post">@csrf<input class="d-none" name="website" tabindex="-1" autocomplete="off">
                        <div class="col-md-6"><label class="form-label" for="contactName">Họ và tên</label><input class="form-control" id="contactName" name="name" value="{{ old('name') }}" type="text" required></div>
                        <div class="col-md-6"><label class="form-label" for="contactPhone">Số điện thoại</label><input class="form-control" id="contactPhone" name="phone" value="{{ old('phone') }}" type="tel" required></div>
                        <div class="col-md-6"><label class="form-label" for="contactEmail">Email</label><input class="form-control" id="contactEmail" name="email" value="{{ old('email') }}" type="email" required></div>
                        <div class="col-md-6"><label class="form-label" for="contactSubject">Chủ đề</label><select class="form-select" id="contactSubject" name="subject"><option>Tư vấn sản phẩm</option><option>Hỗ trợ đơn hàng</option><option>Đổi trả</option><option>Hợp tác kinh doanh</option></select></div>
                        <div class="col-12"><label class="form-label" for="contactMessage">Nội dung</label><textarea class="form-control" id="contactMessage" name="message" rows="5" required>{{ old('message') }}</textarea></div>
                        <div class="col-12"><button class="btn btn-primary px-4" type="submit"><i class="bi bi-send me-2"></i>Gửi yêu cầu</button></div>
                    </form>
                </div>
            </div>
            <div class="col-12">
                @php($mapEmbedUrl = \App\Support\MapEmbed::url($contactSettings?->map_embed))
                @if($mapEmbedUrl)
                    <div class="contact-map-embed">
                        <iframe src="{{ $mapEmbedUrl }}" title="Bản đồ vị trí {{ $website['name'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    </div>
                @else
                    <div class="map-placeholder">
                        <div class="text-center p-4">
                            <i class="bi bi-geo-alt-fill display-4 text-primary"></i>
                            <h2 class="h4 fw-bold mt-3">{{ $website['address'] }}</h2>
                            <p class="text-muted mb-0">Thay khối này bằng Google Maps hoặc bản đồ nhà cung cấp đang sử dụng.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

