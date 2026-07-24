@extends('layouts.admin')

@section('title', 'Tạo bài viết mới')
@section('page-title', 'Viết Bài Mới')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">Bài viết</a></li>
        <li class="breadcrumb-item active" aria-current="page">Viết bài</li>
    </ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.posts.store') }}" method="POST">
        @csrf

        <div class="row">
            <!-- CỘT TRÁI: Nội dung chính bài viết -->
            <div class="col-md-8 mb-4">
                <x-card type="primary" :outline="true" title="Nội dung bài viết" :collapsible="true">
                    <!-- Tiêu đề bài viết -->
                    <x-input 
                        name="name" 
                        label="Tiêu đề bài viết" 
                        :translatable="true" 
                        :required="true"
                    />

                    <!-- Tóm tắt ngắn -->
                    <x-textarea 
                        name="summary" 
                        label="Tóm tắt ngắn (Summary)" 
                        :translatable="true" 
                        rows="3"
                        placeholder="Nên nhập khoảng 150-250 từ ngắn gọn giới thiệu bài viết..."
                    />

                    <!-- Nội dung bài viết (TinyMCE) -->
                    <x-tinymce 
                        name="content" 
                        label="Nội dung chi tiết bài viết (Content)" 
                        :translatable="true" 
                        rows="15"
                    />
                </x-card>
            </div>

            <!-- CỘT PHẢI: Metadata, ảnh đại diện & SEO -->
            <div class="col-md-4 mb-4">
                <!-- Phân loại & Trạng thái -->
                <x-card type="info" :outline="true" title="Cấu hình xuất bản" :collapsible="true" class="mb-4">
                    <!-- Danh mục bài viết -->
                    <div class="mb-3">
                        <label for="post_category_id" class="form-label font-weight-bold">Danh mục bài viết <span class="text-danger">*</span></label>
                        <select name="post_category_id" id="post_category_id" class="form-select @error('post_category_id') is-invalid @enderror" required>
                            <option value="">--- Chọn danh mục ---</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('post_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->getTranslation('name', 'vi') }}
                                </option>
                            @endforeach
                        </select>
                        @error('post_category_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Thời gian xuất bản -->
                    <div class="mb-3">
                        <label for="published_at" class="form-label font-weight-bold">Ngày xuất bản</label>
                        <input type="datetime-local" name="published_at" id="published_at" class="form-control @error('published_at') is-invalid @enderror" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}">
                        @error('published_at')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ảnh đại diện bài viết -->
                    <x-image-upload 
                        name="image" 
                        label="Ảnh đại diện (Featured Image)" 
                        placeholder="Kéo thả ảnh đại diện bài viết tại đây..."
                    />

                    <!-- Tùy chọn nổi bật / Kích hoạt -->
                    <div class="mb-3 border-top pt-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_featured" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer font-weight-bold" for="is_featured">Đánh dấu nổi bật (Featured)</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label cursor-pointer font-weight-bold" for="is_active">Kích hoạt hiển thị</label>
                        </div>
                    </div>
                </x-card>

                <!-- Tối ưu hóa SEO -->
                <x-card type="secondary" :outline="true" title="Tối ưu SEO bài viết" :collapsible="true">
                    <x-input 
                        name="seo_title" 
                        label="SEO Title" 
                        :translatable="true" 
                        placeholder="Mặc định sẽ lấy tiêu đề bài viết..."
                    />

                    <x-textarea 
                        name="seo_description" 
                        label="SEO Description" 
                        :translatable="true" 
                        rows="3"
                        placeholder="Mô tả chuẩn SEO xuất hiện ở tìm kiếm Google..."
                    />

                    <x-input 
                        name="seo_keywords" 
                        label="SEO Keywords" 
                        :translatable="true" 
                        placeholder="Ví dụ: tin tuc bac ninh, le hoi lim..."
                    />
                </x-card>
            </div>

            <!-- NÚT BẤM (Full Width dưới cùng) -->
            <div class="col-12 mb-5">
                <div class="card p-3 border shadow-sm rounded bg-white">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary px-4 py-2 font-weight-bold">
                            <i class="bi bi-x-circle me-1"></i> Hủy bỏ
                        </a>
                        <button type="submit" name="submit_action" value="save_and_create" class="btn btn-info text-white px-4 py-2 font-weight-bold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Lưu & Tạo mới (Ctrl + Shift + S)
                        </button>
                        <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Lưu bài viết (Ctrl + S)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
