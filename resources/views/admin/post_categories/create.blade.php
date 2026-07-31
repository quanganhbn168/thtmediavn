@extends('layouts.admin')

@section('title', 'Thêm danh mục bài viết mới')
@section('page-title', 'Thêm Danh Mục Mới')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.post-categories.index') }}">Danh mục bài viết</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thêm mới</li>
    </ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.post-categories.store') }}" method="POST">
        @csrf

        <div class="row">
            <!-- Cột trái: Thông tin cơ bản -->
            <div class="col-md-8 mb-4">
                <x-card type="primary" :outline="true" title="Thông tin danh mục" :collapsible="true">
                    <!-- Tên danh mục (Đa ngôn ngữ) -->
                    <x-input 
                        name="name" 
                        label="Tên danh mục" 
                        :translatable="true" 
                        :required="true"
                    />

                    <!-- Mô tả danh mục (Đa ngôn ngữ) -->
                    <x-textarea 
                        name="description" 
                        label="Mô tả ngắn danh mục" 
                        :translatable="true" 
                        rows="4"
                    />
                </x-card>
            </div>

            <!-- Cột phải: Cấu hình cha, trạng thái, thứ tự & SEO -->
            <div class="col-md-4 mb-4">
                <!-- Cấu hình hiển thị -->
                <x-card type="info" :outline="true" title="Cấu hình hiển thị" :collapsible="true" class="mb-4">
                    <x-admin.category-tree-select
                        id="parent_id"
                        name="parent_id"
                        label="Danh mục cha"
                        :categories="$categories"
                        :selected="old('parent_id')"
                        :exclude-ids="$excludedParentIds"
                        :parent-mode="true"
                        placeholder="Không có danh mục cha"
                    />

                    <!-- Thứ tự sắp xếp -->
                    <x-input 
                        type="number" 
                        name="sort_order" 
                        label="Thứ tự hiển thị" 
                        value="0" 
                    />

                    <div class="mb-3 border-top pt-3">
                        <label class="form-label font-weight-bold d-block">Hiển thị trang chủ</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_home" id="is_home" value="1" @checked(old('is_home'))>
                            <label class="form-check-label cursor-pointer font-weight-bold" for="is_home">Dùng bài viết của danh mục này ở khối “Blog”</label>
                        </div>
                        <div class="form-text">Có thể chọn nhiều danh mục. Bài nổi bật được ưu tiên làm bài lớn bên trái.</div>
                    </div>

                    <!-- Trạng thái hoạt động -->
                    <div class="mb-3 border-top pt-3">
                        <label class="form-label font-weight-bold d-block">Trạng thái hoạt động</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label cursor-pointer font-weight-bold" for="is_active">Kích hoạt hiển thị</label>
                        </div>
                    </div>
                </x-card>

                <!-- Cấu hình SEO -->
                <x-card type="secondary" :outline="true" title="Tối ưu SEO danh mục" :collapsible="true">
                    <x-input 
                        name="seo_title" 
                        label="SEO Title" 
                        :translatable="true" 
                        placeholder="Tiêu đề chuẩn SEO..."
                    />

                    <x-textarea 
                        name="seo_description" 
                        label="SEO Description" 
                        :translatable="true" 
                        rows="3"
                        placeholder="Mô tả ngắn chuẩn SEO..."
                    />
                </x-card>
            </div>

            <!-- NÚT BẤM (Full Width dưới cùng) -->
            <div class="col-12 mb-5">
                <div class="card p-3 border shadow-sm rounded bg-white">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.post-categories.index') }}" class="btn btn-outline-secondary px-4 py-2 font-weight-bold">
                            <i class="bi bi-x-circle me-1"></i> Hủy bỏ
                        </a>
                        <button type="submit" name="submit_action" value="save_and_create" class="btn btn-info text-white px-4 py-2 font-weight-bold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Lưu & Tạo mới (Ctrl + Shift + S)
                        </button>
                        <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Lưu danh mục mới (Ctrl + S)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
