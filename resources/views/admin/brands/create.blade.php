@extends('layouts.admin')

@section('title', 'Thêm thương hiệu')
@section('page-title', 'Thêm thương hiệu')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.brands.index') }}">Thương hiệu</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thêm mới</li>
    </ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.brands.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <x-card type="primary" :outline="true" title="Thông tin thương hiệu" :collapsible="true" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <x-input
                                id="brand_name"
                                name="name"
                                label="Tên thương hiệu"
                                :value="$brand->name"
                                placeholder="Ví dụ: THT Studio"
                                required
                            />
                        </div>
                        <div class="col-md-5">
                            <x-slug name="slug" label="Đường dẫn" :value="$brand->slug" source="brand_name" />
                        </div>
                        <div class="col-12">
                            <x-input
                                name="website"
                                type="url"
                                label="Website chính thức"
                                :value="$brand->website"
                                placeholder="https://thuong-hieu.com"
                            />
                        </div>
                        <div class="col-12">
                            <x-textarea
                                name="description"
                                label="Giới thiệu thương hiệu"
                                :value="$brand->description"
                                rows="6"
                                placeholder="Thông tin ngắn giúp đội ngũ quản trị nhận biết thương hiệu..."
                            />
                        </div>
                    </div>
                </x-card>

                <x-card type="secondary" :outline="true" title="Logo thương hiệu" :collapsible="true">
                    <x-image-upload
                        name="logo"
                        label="Logo"
                        placeholder="Kéo thả logo vào đây hoặc click để chọn file"
                        :max-files="1"
                        :convert-to-webp="true"
                    />
                    <p class="text-muted small mb-0">
                        Ưu tiên logo nền trong suốt hoặc ảnh tỷ lệ vuông để hiển thị đẹp ở các vị trí thương hiệu.
                    </p>
                </x-card>
            </div>

            <div class="col-lg-4">
                <x-card type="info" :outline="true" title="Cấu hình hiển thị" :collapsible="true">
                    <x-input
                        name="sort_order"
                        type="number"
                        label="Thứ tự hiển thị"
                        :value="old('sort_order', 0)"
                        min="0"
                        step="1"
                    />

                    <div class="border-top pt-3">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_active" id="brand_is_active" value="1" @checked((bool) old('is_active', true))>
                            <label class="form-check-label cursor-pointer fw-semibold" for="brand_is_active">Hiển thị thương hiệu</label>
                            <div class="form-text">Thương hiệu bị ẩn sẽ không xuất hiện ở website.</div>
                        </div>

                        <input type="hidden" name="is_featured" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_featured" id="brand_is_featured" value="1" @checked((bool) old('is_featured', false))>
                            <label class="form-check-label cursor-pointer fw-semibold" for="brand_is_featured">Hiển thị ở trang chủ</label>
                            <div class="form-text">Được phép xuất hiện trong dải thương hiệu ở trang chủ.</div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 mb-5">
            <a class="btn btn-default" href="{{ route('admin.brands.index') }}">
                <i class="bi bi-arrow-left me-1"></i>Quay lại
            </a>
            <button class="btn btn-primary" type="submit">
                <i class="bi bi-plus-lg me-1"></i>Tạo thương hiệu
            </button>
        </div>
    </form>
@endsection
