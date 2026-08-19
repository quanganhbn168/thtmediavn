@extends('layouts.plain')

@section('title', 'Lỗi hệ thống (500) | ' . $website['name'])

@section('body-class', 'bg-soft')

@section('body')
<main class="flex min-h-screen items-center py-20">
  <div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-2xl text-center">
        <div class="mb-3 text-8xl font-bold leading-none text-red-600">500</div>
        <h1 class="mb-3 text-2xl font-bold">Có lỗi xảy ra từ máy chủ!</h1>
        <p class="mb-6 text-muted">
          Hệ thống gặp sự cố không mong muốn trong quá trình xử lý yêu cầu của bạn.
          Đội ngũ kỹ thuật của chúng tôi đã được thông báo. Xin vui lòng thử lại sau ít phút.
        </p>
        <div class="flex flex-wrap justify-center gap-2">
          <a href="{{ route('home') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-transparent bg-primary px-5 py-3 text-sm font-bold leading-tight text-white shadow-sm transition duration-200 hover:-translate-y-px hover:bg-primary-hover hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <i class="fa-solid fa-arrow-left mr-1" aria-hidden="true"></i>
            Quay lại trang chủ
          </a>
          <a href="mailto:{{ $website['email'] }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-line bg-white px-5 py-3 text-sm font-bold leading-tight text-muted shadow-sm transition duration-200 hover:-translate-y-px hover:border-primary hover:bg-primary-soft hover:text-primary hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            <i class="fa-solid fa-life-ring mr-1" aria-hidden="true"></i>
            Liên hệ Kỹ thuật
          </a>
        </div>
    </div>
  </div>
</main>
@endsection
