@extends('layouts.plain')

@section('title', 'Không tìm thấy trang (404) | ' . $website['name'])

@section('body-class', 'bg-soft')

@section('body')
<main class="flex min-h-screen items-center py-20">
  <div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-2xl text-center">
        <div class="mb-3 text-8xl font-bold leading-none text-primary">404</div>
        <h1 class="mb-3 text-2xl font-bold">Oops! Không tìm thấy trang yêu cầu.</h1>
        <p class="mb-6 text-muted">
          Trang bạn đang tìm kiếm không tồn tại, đã bị xóa hoặc đường dẫn bị thay đổi.
          Vui lòng quay lại trang chủ của {{ $website['name'] }}.
        </p>
        <a href="{{ route('home') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
          <i class="fa-solid fa-arrow-left mr-1" aria-hidden="true"></i>
          Quay lại trang chủ
        </a>
    </div>
  </div>
</main>
@endsection
