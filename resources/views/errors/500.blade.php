@extends('layouts.plain')

@section('title', 'Lỗi hệ thống (500) | ' . $website['name'])

@section('body-class', 'bg-body-tertiary')

@section('body')
<main class="d-flex align-items-center min-vh-100 py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6 text-center">
        <div class="display-1 fw-bold text-danger lh-1 mb-3">500</div>
        <h1 class="h3 mb-3">Có lỗi xảy ra từ máy chủ!</h1>
        <p class="text-secondary mb-4">
          Hệ thống gặp sự cố không mong muốn trong quá trình xử lý yêu cầu của bạn. 
          Đội ngũ kỹ thuật của chúng tôi đã được thông báo. Xin vui lòng thử lại sau ít phút.
        </p>
        <div class="d-flex gap-2 justify-content-center">
          <a href="{{ route('home') }}" class="btn btn-primary px-4 py-2">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
            Quay lại trang chủ
          </a>
          <a href="mailto:{{ $website['email'] }}" class="btn btn-outline-secondary px-4 py-2">
            <i class="bi bi-life-preserver me-1" aria-hidden="true"></i>
            Liên hệ Kỹ thuật
          </a>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
