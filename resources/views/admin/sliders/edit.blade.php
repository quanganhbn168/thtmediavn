@extends('layouts.admin')

@section('title', 'Chỉnh sửa bộ trình chiếu')
@section('page-title', 'Chỉnh sửa bộ trình chiếu')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.sliders.index') }}">Bộ trình chiếu</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $slider->name }}</li>
</ol>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-xl-4">
        <form id="admin-save-form" action="{{ route('admin.sliders.update', $slider) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title"><i class="bi bi-sliders me-2"></i>Thông tin chung</h3></div>
                <div class="card-body">
                    <x-input name="name" label="Tên bộ trình chiếu" :value="$slider" :translatable="true" :required="true" id="slider_name" />

                    <x-select
                        name="key"
                        label="Vị trí hiển thị"
                        :options="$sliderTypes"
                        :selected="old('key', $slider->key)"
                        placeholder="Chọn vị trí sử dụng"
                        :required="true"
                        id="slider_type"
                    />

                    <div class="alert alert-light border small">
                        <i class="bi bi-code-square me-1 text-primary"></i>
                        Mã enum hiện tại: <code>{{ $slider->key }}</code>
                    </div>

                    <div class="form-check form-switch mt-4">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="slider_is_active" value="1" @checked((bool) old('is_active', $slider->is_active))>
                        <label class="form-check-label" for="slider_is_active">Bộ trình chiếu đang hoạt động</label>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.sliders.index') }}" class="btn btn-default"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Lưu thay đổi</button>
                </div>
            </div>
        </form>
    </div>

    <div class="col-xl-8">
        <div class="card card-info card-outline">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0"><i class="bi bi-images me-2"></i>Danh sách ảnh <span class="badge text-bg-info ms-1">{{ $items->count() }}</span></h3>
                <a href="{{ route('admin.slider-items.create', $slider) }}" class="btn btn-info btn-sm ms-auto"><i class="bi bi-plus-lg me-1"></i>Thêm ảnh</a>
            </div>
            <div class="card-body">
                @forelse($items as $item)
                    @php($media = $item->getFirstMedia('slide_image'))
                    <article class="d-flex flex-column flex-md-row gap-3 align-items-md-center py-3 border-bottom">
                        <div class="slider-item-thumb flex-shrink-0 bg-body-secondary rounded overflow-hidden">
                            <img src="{{ $media?->getUrl() ?: asset('images/no-image.png') }}" alt="{{ $item->title }}" class="w-100 h-100 object-fit-cover">
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <strong>{{ $item->title ?: 'Ảnh #'.$item->id }}</strong>
                                <span class="badge text-bg-light">Thứ tự {{ $item->sort_order }}</span>
                                <span class="badge {{ $item->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $item->is_active ? 'Đang hiển thị' : 'Đang ẩn' }}</span>
                            </div>
                            <div class="text-body-secondary small text-truncate">{{ $item->sub_title ?: 'Chưa có phụ đề' }}</div>
                        </div>
                        <div class="btn-group btn-group-sm flex-shrink-0">
                            <a href="{{ route('admin.slider-items.edit', $item) }}" class="btn btn-default" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a>
                            <button type="submit" form="delete-slider-item-{{ $item->id }}" class="btn btn-default text-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                        </div>
                    </article>
                @empty
                    <div class="admin-empty py-5">
                        <span><i class="bi bi-image"></i></span>
                        <h5>Chưa có ảnh trong bộ trình chiếu</h5>
                        <p>Thêm ảnh, nội dung và liên kết cho vị trí này.</p>
                        <a href="{{ route('admin.slider-items.create', $slider) }}" class="btn btn-info"><i class="bi bi-plus-lg me-1"></i>Thêm ảnh đầu tiên</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@foreach($items as $item)
    <form id="delete-slider-item-{{ $item->id }}" action="{{ route('admin.slider-items.destroy', $item) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa ảnh này?" data-delete-warning="Ảnh trong thư viện media cũng sẽ bị xóa.">
        @csrf
        @method('DELETE')
    </form>
@endforeach
@endsection

@push('css')
<style>
.slider-item-thumb { width: 150px; height: 92px; }
.min-width-0 { min-width: 0; }
@media (max-width: 575.98px) { .slider-item-thumb { width: 100%; height: 170px; } }
</style>
@endpush
