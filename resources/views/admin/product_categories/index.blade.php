@extends('layouts.admin')

@section('title', 'Danh mục sản phẩm')
@section('page-title', 'Danh mục sản phẩm')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Danh mục sản phẩm</li>
    </ol>
@endsection

@section('content')
    <x-admin.index-card
        title="Danh sách danh mục sản phẩm"
        description="Quản lý phân nhóm sản phẩm hiển thị trong kho hàng."
        icon="bi-tags"
        :create-url="route('admin.product-categories.create')"
        create-label="Thêm danh mục"
        resource="product_category"
        bulk-delete-warning="Danh mục đang có dữ liệu liên quan sẽ được chặn xóa."
        :reorderable="true"
        :reorder-enabled="! request()->hasAny(['search', 'per_page', 'parent_id', 'status', 'featured', 'home'])"
        :order-start="$categories->firstItem() ?? 1"
    >
    <x-slot:filters>
        <form action="{{ route('admin.product-categories.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-xl-4 col-lg-6">
                    <label for="product-category-search" class="form-label">Từ khóa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input
                            type="search"
                            id="product-category-search"
                            class="form-control"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Nhập tên danh mục"
                        />
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <x-admin.category-tree-select
                        name="parent_id"
                        label="Danh mục cha"
                        :categories="$filterCategories"
                        :selected="request('parent_id')"
                        placeholder="Tất cả danh mục cha"
                    />
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6">
                    <label for="product-category-status" class="form-label">Trạng thái</label>
                    <select id="product-category-status" name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" @selected(request('status') === 'active')>Đang hiển thị</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Đang ẩn</option>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-4 col-md-6">
                    <label for="product-category-featured" class="form-label">Nổi bật</label>
                    <select id="product-category-featured" name="featured" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="yes" @selected(request('featured') === 'yes')>Có</option>
                        <option value="no" @selected(request('featured') === 'no')>Không</option>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-4 col-md-6">
                    <label for="product-category-home" class="form-label">Trang chủ</label>
                    <select id="product-category-home" name="home" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="yes" @selected(request('home') === 'yes')>Có</option>
                        <option value="no" @selected(request('home') === 'no')>Không</option>
                    </select>
                </div>
                <div class="col-xl-1 col-lg-4 col-md-6">
                    <label for="product-category-per-page" class="form-label">Số dòng</label>
                    <select id="product-category-per-page" name="per_page" class="form-select">
                        @foreach([10, 20, 25, 50] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-1 col-lg-4 col-md-6 d-flex align-items-end">
                    <button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Lọc</button>
                </div>
                <div class="col-xl-1 col-lg-4 col-md-6">
                    @if(request()->hasAny(['search', 'per_page', 'parent_id', 'status', 'featured', 'home']))
                        <a href="{{ route('admin.product-categories.index') }}" class="btn btn-default d-block" title="Xóa bộ lọc">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover text-nowrap align-middle mb-0">
            <thead>
                <tr>
                    <th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th>
                    <th>Tên</th>
                    <th>Danh mục cha</th>
                    <th class="text-center" style="width:110px">Kích hoạt</th>
                    <th class="text-center" style="width:110px">Nổi bật</th>
                    <th class="text-center" style="width:110px">Trang chủ</th>
                    <th class="text-end" style="width:130px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                    @forelse($categories as $category)
                        <tr data-record-id="{{ $category->id }}">
                            <td data-select-column class="text-center"><input form="admin-bulk-product_category-form" type="checkbox" name="ids[]" value="{{ $category->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $category->name }}"></td>
                            <td>
                                <strong>{{ $category->name }}</strong>
                            </td>
                            <td>{{ $category->parent?->name ?: '—' }}</td>
                            <td class="text-center">
                                <x-toggle
                                    model="ProductCategory"
                                    :id="$category->id"
                                    field="is_active"
                                    :checked="$category->is_active"
                                    label=""
                                />
                            </td>
                            <td class="text-center">
                                <x-toggle
                                    model="ProductCategory"
                                    :id="$category->id"
                                    field="is_featured"
                                    :checked="$category->is_featured"
                                    label=""
                                />
                            </td>
                            <td class="text-center">
                                <x-toggle
                                    model="ProductCategory"
                                    :id="$category->id"
                                    field="is_home"
                                    :checked="$category->is_home"
                                    label=""
                                />
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.product-categories.edit', $category) }}" class="btn btn-default" title="Chỉnh sửa">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button
                                        type="submit"
                                        form="delete-product-category-{{ $category->id }}"
                                        class="btn btn-default text-danger"
                                        title="Xóa"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="admin-empty">
                                    <span><i class="bi bi-grid-3x3-gap"></i></span>
                                    <h5>Chưa có danh mục</h5>
                                    <p>Tạo danh mục đầu tiên để phân loại sản phẩm.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @foreach($categories as $category)
            <form
                id="delete-product-category-{{ $category->id }}"
                action="{{ route('admin.product-categories.destroy', $category) }}"
                method="POST"
                class="d-none"
                data-admin-delete-form
                data-delete-title="Xóa danh mục sản phẩm này?"
                data-delete-warning="Danh mục đang có sản phẩm liên quan sẽ không thể xóa."
            >
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <x-slot:footer>
            @if($categories->hasPages())
                {{ $categories->links() }}
            @endif
        </x-slot:footer>
    </x-admin.index-card>
@endsection
