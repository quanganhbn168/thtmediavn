@extends('layouts.admin')

@section('title', 'Danh mục bài viết')
@section('page-title', 'Danh mục bài viết')

@section('content')
    @php
        $hasFilters = request()->filled('search') || request()->filled('status');
        $canReorder = ! $hasFilters;
    @endphp

    <div data-admin-index>
        <x-admin.index-header
            description="Tổ chức nhóm nội dung và chọn danh mục bài viết được hiển thị trên trang chủ."
            :create-url="route('admin.post-categories.create')"
            create-label="Thêm danh mục"
        />

        <x-admin.filter-panel>
            <form action="{{ route('admin.post-categories.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label class="form-label" for="post-category-search">Từ khóa</label>
                    <input id="post-category-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên danh mục">
                </div>
                <div class="col-lg-3">
                    <label class="form-label" for="post-category-status">Trạng thái</label>
                    <select id="post-category-status" name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="active" @selected(request('status') === 'active')>Hiển thị</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Đang ẩn</option>
                    </select>
                </div>
                <div class="col-lg-1">
                    <label class="form-label" for="post-category-per-page">Số dòng</label>
                    <select id="post-category-per-page" name="per_page" class="form-select">
                        @foreach([10, 25, 50] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" type="submit">Lọc</button>
                    <a href="{{ route('admin.post-categories.index') }}" class="btn btn-default" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </x-admin.filter-panel>

        <x-admin.table-card title="Danh sách danh mục">
            <x-slot:tools>
                <span class="badge text-bg-light">{{ $categories->total() }} bản ghi</span>
                <button type="button" class="btn btn-default btn-sm" data-reorder-toggle @disabled(! $canReorder)>
                    <i class="bi bi-arrow-down-up me-1"></i><span data-reorder-label>Sắp xếp</span>
                </button>
            </x-slot:tools>

            <x-admin.bulk-toolbar form-id="post-category-bulk-form" resource="post_category" delete-warning="Các bài viết đang liên kết có thể bị ảnh hưởng." />

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th data-select-column class="text-center"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th>
                            <th data-order-column class="text-center"><i class="bi bi-arrow-down-up"></i></th>
                            <th>Tên danh mục</th>
                            <th>Danh mục cha</th>
                            <th class="text-center">Bài viết</th>
                            <th class="text-center">Trang chủ</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody data-sortable-body data-resource="post_category" data-reorder-url="{{ route('admin.common.reorder') }}" data-order-start="{{ $categories->firstItem() ?? 1 }}">
                        @forelse($categories as $category)
                            <tr data-record-id="{{ $category->id }}">
                                <td data-select-column class="text-center">
                                    <input form="post-category-bulk-form" type="checkbox" name="ids[]" value="{{ $category->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $category->name }}">
                                </td>
                                <td data-order-column class="text-center"><button type="button" class="admin-drag-handle" data-drag-handle aria-label="Kéo để sắp xếp"><i class="bi bi-grip-vertical fs-5"></i></button></td>
                                <td>
                                    <a href="{{ route('admin.post-categories.edit', $category) }}" class="fw-semibold text-decoration-none">{{ $category->name }}</a>
                                    <small class="d-block text-body-secondary">Thứ tự {{ $category->sort_order }}</small>
                                </td>
                                <td>{{ $category->parent?->name ?? '—' }}</td>
                                <td class="text-center">{{ $category->posts_count }}</td>
                                <td class="text-center"><x-toggle model="PostCategory" :id="$category->id" field="is_home" :checked="$category->is_home" label="" /></td>
                                <td class="text-center"><x-toggle model="PostCategory" :id="$category->id" field="is_active" :checked="$category->is_active" label="" /></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.post-categories.edit', $category) }}" class="btn btn-default btn-sm" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a>
                                    <button form="delete-post-category-{{ $category->id }}" class="btn btn-default btn-sm text-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-5">Chưa có danh mục.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @foreach($categories as $category)
                <form id="delete-post-category-{{ $category->id }}" action="{{ route('admin.post-categories.destroy', $category) }}" method="POST" class="d-none" data-admin-delete-form>
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach

            <x-slot:footer>
                @if($categories->hasPages())
                    {{ $categories->links() }}
                @endif
            </x-slot:footer>
        </x-admin.table-card>
    </div>
@endsection
