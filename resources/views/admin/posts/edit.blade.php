@extends('layouts.admin')

@section('title', 'Chỉnh sửa bài viết')
@section('page-title', 'Chỉnh Sửa Bài Viết')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">Bài viết</a></li>
        <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
    </ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.posts.update', $post->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- CỘT TRÁI: Nội dung chính bài viết -->
            <div class="col-md-8 mb-4">
                <x-card type="primary" :outline="true" title="Nội dung bài viết" :collapsible="true">
                    <!-- Tiêu đề bài viết -->
                    <x-input 
                        name="name" 
                        label="Tiêu đề bài viết" 
                        :value="$post" 
                        :translatable="true" 
                        :required="true"
                    />

                    <!-- Tóm tắt ngắn -->
                    <x-textarea 
                        name="summary" 
                        label="Tóm tắt ngắn (Summary)" 
                        :value="$post" 
                        :translatable="true" 
                        rows="3"
                        placeholder="Nên nhập khoảng 150-250 từ ngắn gọn giới thiệu bài viết..."
                    />

                    <!-- Nội dung bài viết (TinyMCE) -->
                    <x-tinymce 
                        name="content" 
                        label="Nội dung chi tiết bài viết (Content)" 
                        :value="$post" 
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
                    <x-admin.category-tree-select
                        id="post_category_id"
                        name="post_category_id"
                        label="Danh mục bài viết"
                        :categories="$categories"
                        :selected="old('post_category_id', $post->post_category_id)"
                        :leaf-only="true"
                        :active-only="true"
                        required
                    />

                    <!-- Thời gian xuất bản -->
                    <div class="mb-3">
                        <label for="published_at" class="form-label font-weight-bold">Ngày xuất bản</label>
                        <input type="datetime-local" name="published_at" id="published_at" class="form-control @error('published_at') is-invalid @enderror" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
                        @error('published_at')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ảnh đại diện bài viết (Truyền path ảnh cũ làm value) -->
                    @php
                        $postMedia = $post->getFirstMedia('post_image');
                    @endphp
                    <x-image-upload 
                        name="image" 
                        label="Ảnh đại diện (Featured Image)" 
                        :value="$postMedia ? 'media/' . $postMedia->id . '/' . $postMedia->file_name : ''"
                        placeholder="Kéo thả ảnh mới vào đây nếu muốn đổi..."
                    />

                    <!-- Tùy chọn nổi bật / Kích hoạt -->
                    <div class="mb-3 border-top pt-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $post->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer font-weight-bold" for="is_featured">Đánh dấu nổi bật (Featured)</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ old('is_active', $post->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label cursor-pointer font-weight-bold" for="is_active">Kích hoạt hiển thị</label>
                        </div>
                    </div>
                </x-card>

                <!-- Tối ưu hóa SEO -->
                <x-card type="secondary" :outline="true" title="Tối ưu SEO bài viết" :collapsible="true">
                    <x-input 
                        name="seo_title" 
                        label="SEO Title" 
                        :value="$post" 
                        :translatable="true" 
                        placeholder="Mặc định sẽ lấy tiêu đề bài viết..."
                    />

                    <x-textarea 
                        name="seo_description" 
                        label="SEO Description" 
                        :value="$post" 
                        :translatable="true" 
                        rows="3"
                        placeholder="Mô tả chuẩn SEO xuất hiện ở tìm kiếm Google..."
                    />

                    <x-input 
                        name="seo_keywords" 
                        label="SEO Keywords" 
                        :value="$post" 
                        :translatable="true" 
                        placeholder="Ví dụ: xu huong truyen thong, kinh nghiem san xuat..."
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
                        <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Lưu thay đổi (Ctrl + S)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
