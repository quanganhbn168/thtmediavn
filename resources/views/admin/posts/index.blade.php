@extends('layouts.admin')

@section('title', 'Quản lý bài viết')
@section('page-title', 'Bài viết')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Bài viết</li>
</ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách bài viết"
    description="Quản lý nội dung, danh mục và trạng thái xuất bản."
    icon="bi-newspaper"
    :create-url="route('admin.posts.create')"
    create-label="Viết bài mới"
    resource="post"
    bulk-delete-warning="Ảnh đại diện và dữ liệu liên quan của bài viết cũng sẽ bị xóa."
>
    <x-slot:filters>
        <form action="{{ route('admin.posts.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-xl-4 col-md-6">
                <label for="post-search" class="form-label">Từ khóa</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" class="form-control" id="post-search" name="search" value="{{ request('search') }}" placeholder="Tiêu đề hoặc tóm tắt">
                </div>
            </div>
            <div class="col-xl-3 col-md-3">
                <label for="post-category" class="form-label">Danh mục</label>
                <select class="form-select" id="post-category" name="category_id">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-md-3">
                <label for="post-status" class="form-label">Trạng thái</label>
                <select class="form-select" id="post-status" name="status">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Đang hoạt động</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Ngừng hoạt động</option>
                </select>
            </div>
            <div class="col-xl-1 col-md-3">
                <label for="post-per-page" class="form-label">Số dòng</label>
                <select class="form-select" id="post-per-page" name="per_page">
                    @foreach([10, 25, 50] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Lọc</button>
                @if(request()->hasAny(['search', 'category_id', 'status', 'per_page']))
                    <a href="{{ route('admin.posts.index') }}" class="btn btn-default" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover text-nowrap align-middle mb-0">
            <thead><tr>
                <th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th>
                <th style="width:80px">ID</th><th>Tiêu đề</th><th>Danh mục</th>
                <th class="text-center">Nổi bật</th><th class="text-center">Trạng thái</th>
                <th>Ngày xuất bản</th><th class="text-end" style="width:130px">Thao tác</th>
            </tr></thead>
            <tbody>
                @forelse($posts as $post)
                    <tr data-record-id="{{ $post->id }}">
                        <td data-select-column class="text-center"><input form="admin-bulk-post-form" type="checkbox" name="ids[]" value="{{ $post->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $post->name }}"></td>
                        <td class="text-body-secondary">#{{ $post->id }}</td>
                        <td><a href="{{ route('admin.posts.edit', $post) }}" class="fw-semibold text-decoration-none">{{ Str::limit($post->name, 60) }}</a></td>
                        <td>{{ $post->category?->name ?? '—' }}</td>
                        <td class="text-center"><x-toggle model="Post" :id="$post->id" field="is_featured" :checked="$post->is_featured" /></td>
                        <td class="text-center"><x-toggle model="Post" :id="$post->id" field="is_active" :checked="$post->is_active" /></td>
                        <td>{{ $post->published_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="text-end"><div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-default" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a>
                            <button type="submit" form="delete-post-{{ $post->id }}" class="btn btn-default text-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="admin-empty"><span><i class="bi bi-newspaper"></i></span><h5>Chưa có bài viết</h5><p>Hãy tạo bài viết đầu tiên hoặc thay đổi bộ lọc.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach($posts as $post)
        <form id="delete-post-{{ $post->id }}" action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa bài viết này?" data-delete-warning="Ảnh đại diện và dữ liệu liên quan cũng sẽ bị xóa.">@csrf @method('DELETE')</form>
    @endforeach

    <x-slot:footer>
        @if($posts->hasPages())
            {{ $posts->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
