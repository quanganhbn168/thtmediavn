@extends('layouts.admin')

@section('title', 'Thêm danh mục sản phẩm')
@section('page-title', 'Thêm danh mục sản phẩm')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.product-categories.index') }}">Danh mục sản phẩm</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thêm mới</li>
    </ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.product-categories.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <x-card type="primary" :outline="true" title="Thông tin danh mục" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <x-input
                                id="product_category_name"
                                name="name"
                                label="Tên danh mục"
                                required
                            />
                        </div>
                        <div class="col-md-5">
                            <x-slug
                                name="slug"
                                label="Đường dẫn"
                                source="product_category_name"
                            />
                        </div>
                        <div class="col-12">
                            <x-textarea
                                name="description"
                                label="Mô tả danh mục"
                                rows="6"
                                placeholder="Mô tả ngắn về nhóm sản phẩm..."
                            />
                        </div>
                    </div>
                </x-card>

                <x-card type="secondary" :outline="true" title="Ảnh đại diện">
                    <x-image-upload
                        name="image"
                        label="Ảnh danh mục"
                        placeholder="Kéo thả ảnh danh mục vào đây hoặc click để chọn file"
                        :max-files="1"
                        :convert-to-webp="true"
                        :width="600"
                        :height="600"
                    />
                </x-card>
            </div>

            <div class="col-lg-4">
                <x-card type="info" :outline="true" title="Cấu hình hiển thị" class="mb-4">
                    <x-admin.category-tree-select
                        name="parent_id"
                        label="Danh mục cha"
                        :categories="$categories"
                        :selected="old('parent_id')"
                        :exclude-ids="$excludedParentIds"
                        :parent-mode="true"
                        placeholder="Đây là thư mục gốc"
                    />

                    <x-input
                        name="sort_order"
                        type="number"
                        label="Thứ tự hiển thị"
                        :value="old('sort_order')"
                        min="0"
                        placeholder="Để trống để tự động xếp cuối"
                    />

                    <div class="border-top pt-3">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch mb-3">
                            <input
                                class="form-check-input cursor-pointer"
                                type="checkbox"
                                role="switch"
                                name="is_active"
                                id="product_category_is_active"
                                value="1"
                                @checked((bool) old('is_active', true))
                            >
                            <label class="form-check-label cursor-pointer fw-semibold" for="product_category_is_active">
                                Hiển thị danh mục
                            </label>
                        </div>

                        <input type="hidden" name="is_featured" value="0">
                        <div class="form-check form-switch mb-3">
                            <input
                                class="form-check-input cursor-pointer"
                                type="checkbox"
                                role="switch"
                                name="is_featured"
                                id="product_category_is_featured"
                                value="1"
                                @checked((bool) old('is_featured', false))
                            >
                            <label class="form-check-label cursor-pointer fw-semibold" for="product_category_is_featured">
                                Danh mục nổi bật
                            </label>
                        </div>

                        <input type="hidden" name="is_home" value="0">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input cursor-pointer"
                                type="checkbox"
                                role="switch"
                                name="is_home"
                                id="product_category_is_home"
                                value="1"
                                @checked((bool) old('is_home', false))
                            >
                            <label class="form-check-label cursor-pointer fw-semibold" for="product_category_is_home">
                                Hiển thị ở trang chủ
                            </label>
                        </div>
                    </div>
                </x-card>

                <x-card type="secondary" :outline="true" title="Tối ưu SEO">
                    <x-input
                        name="seo_title"
                        label="SEO title"
                        placeholder="Tiêu đề hiển thị trên công cụ tìm kiếm"
                    />
                    <x-textarea
                        name="seo_description"
                        label="SEO description"
                        rows="4"
                        placeholder="Mô tả ngắn cho công cụ tìm kiếm"
                    />
                </x-card>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 mb-5">
            <a class="btn btn-default" href="{{ route('admin.product-categories.index') }}">
                <i class="bi bi-arrow-left me-1"></i>Quay lại
            </a>
            <button class="btn btn-primary" type="submit">
                <i class="bi bi-plus-lg me-1"></i>Tạo danh mục
            </button>
        </div>
    </form>
    @include('admin.product_categories._seo-autofill')
@endsection
