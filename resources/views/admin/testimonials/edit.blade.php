@extends('layouts.admin')

@section('title', 'Sửa cảm nhận khách hàng')
@section('page-title', 'Sửa cảm nhận khách hàng')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.testimonials.index') }}">Cảm nhận khách hàng</a></li><li class="breadcrumb-item active">Chỉnh sửa</li></ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.testimonials.update', $testimonial) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <div class="col-lg-8">
                <x-card type="primary" :outline="true" title="Nội dung cảm nhận" :collapsible="true">
                    <div class="row g-3"><div class="col-md-7"><x-input name="name" label="Tên khách hàng" :value="$testimonial->name" required /></div><div class="col-md-5"><x-input name="label" label="Nhãn phụ" :value="$testimonial->label" placeholder="Ví dụ: Da hỗn hợp · Hà Nội" /></div></div>
                    <x-textarea name="content" label="Nội dung testimonial" :value="$testimonial->content" rows="7" required />
                </x-card>
            </div>
            <div class="col-lg-4">
                <x-card type="info" :outline="true" title="Hiển thị" :collapsible="true" class="mb-4">
                    <x-select name="rating" label="Số sao" :value="$testimonial->rating" :options="[5 => '5 sao', 4 => '4 sao', 3 => '3 sao', 2 => '2 sao', 1 => '1 sao']" required />
                    <x-input name="sort_order" type="number" label="Thứ tự" :value="$testimonial->sort_order" min="0" step="1" />
                    <div class="border-top pt-3"><input type="hidden" name="is_active" value="0"><div class="form-check form-switch"><input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_active" id="testimonial_is_active" value="1" @checked((bool) old('is_active', $testimonial->is_active))><label class="form-check-label cursor-pointer fw-semibold" for="testimonial_is_active">Hiển thị trên trang chủ</label></div></div>
                </x-card>
                <x-card type="secondary" :outline="true" title="Ảnh đại diện" :collapsible="true" class="mb-4"><x-image-upload name="avatar" label="Ảnh khách hàng" :existing-url="$testimonial->getFirstMediaUrl('testimonial_avatar') ?: null" placeholder="Tải ảnh đại diện (không bắt buộc)" :width="400" :height="400" /><p class="text-muted small mb-0">Không có ảnh thì website dùng chữ cái đầu của tên.</p></x-card>
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 mb-5"><a class="btn btn-default" href="{{ route('admin.testimonials.index') }}"><i class="bi bi-arrow-left me-1"></i>Quay lại</a><button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Lưu thay đổi</button></div>
    </form>
@endsection
