@extends('layouts.plain')

@section('title', 'Không tìm thấy trang (404) | ' . $website['name'])

@section('body-class', 'bg-body-tertiary')

@section('body')
<main class="d-flex align-items-center min-vh-100 py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6 text-center">
        <div class="display-1 fw-bold text-primary lh-1 mb-3">404</div>
        <h1 class="h3 mb-3">Oops! Không tìm thấy trang yêu cầu.</h1>
        <p class="text-secondary mb-4">
          Trang bạn đang tìm kiếm không tồn tại, đã bị xóa hoặc đường dẫn bị thay đổi. 
          Vui lòng quay lại trang chủ của {{ $website['name'] }}.
        </p>
        <a href="{{ route('home') }}" class="btn btn-primary px-4 py-2">
          <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
          Quay lại trang chủ
        </a>
      </div>
    </div>
  </div>
</main>
@endsection
