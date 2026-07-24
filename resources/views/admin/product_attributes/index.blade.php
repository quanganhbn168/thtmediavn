@extends('layouts.admin')

@section('title', 'Thuộc tính lọc')
@section('page-title', 'Thuộc tính lọc')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thuộc tính lọc</li>
    </ol>
@endsection

@section('content')
    <x-admin.index-card
        title="Nhóm thông tin để lọc"
        description="Dùng cho lọc catalogue và điều hướng; không tạo biến thể hoặc SKU."
        icon="bi-funnel"
        :create-url="route('admin.product-attributes.create')"
        create-label="Thêm thuộc tính lọc"
        resource="product_attribute"
        :reorderable="true"
        :reorder-enabled="! request()->hasAny(['search', 'per_page'])"
        :order-start="$attributes->firstItem() ?? 1"
    >
        <x-slot:filters>
            <form action="{{ route('admin.product-attributes.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label" for="attribute-search">Từ khóa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input id="attribute-search" class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Tên hoặc slug">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="attribute-per-page">Số dòng</label>
                    <select id="attribute-per-page" name="per_page" class="form-select">
                        @foreach([10, 20, 25, 50] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Lọc</button>
                    @if(request()->hasAny(['search', 'per_page']))
                        <a class="btn btn-default" href="{{ route('admin.product-attributes.index') }}" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a>
                    @endif
                </div>
            </form>
        </x-slot:filters>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th>
                        <th>Thuộc tính</th>
                        <th>Giá trị</th>
                        <th class="text-center" style="width:120px">Menu sản phẩm</th>
                        <th class="text-center" style="width:110px">Trạng thái</th>
                        <th class="text-end" style="width:130px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attributes as $attribute)
                        <tr data-record-id="{{ $attribute->id }}">
                            <td data-select-column class="text-center"><input form="admin-bulk-product_attribute-form" type="checkbox" name="ids[]" value="{{ $attribute->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $attribute->name }}"></td>
                            <td>
                                <a class="fw-semibold text-decoration-none" href="{{ route('admin.product-attributes.edit', $attribute) }}">{{ $attribute->name }}</a>
                                <small class="d-block text-muted">Thứ tự {{ $attribute->sort_order }}</small>
                            </td>
                            <td>
                                @forelse($attribute->values as $value)
                                    <span class="badge text-bg-light me-1 mb-1">{{ $value->value }}</span>
                                @empty
                                    <span class="text-muted">Chưa có giá trị</span>
                                @endforelse
                            </td>
                            <td class="text-center"><x-toggle model="ProductAttribute" :id="$attribute->id" field="show_in_product_menu" :checked="$attribute->show_in_product_menu" /></td>
                            <td class="text-center"><x-toggle model="ProductAttribute" :id="$attribute->id" field="is_active" :checked="$attribute->is_active" /></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.product-attributes.edit', $attribute) }}" class="btn btn-default" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a>
                                    <button type="submit" form="delete-product-attribute-{{ $attribute->id }}" class="btn btn-default text-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5"><div class="admin-empty"><span><i class="bi bi-funnel"></i></span><h5>Chưa có thuộc tính lọc</h5><p>Tạo nhóm như Loại da, Kiểu mi hoặc Chất liệu.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @foreach($attributes as $attribute)
            <form id="delete-product-attribute-{{ $attribute->id }}" action="{{ route('admin.product-attributes.destroy', $attribute) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa thuộc tính lọc này?" data-delete-warning="Không thể xóa khi một giá trị đang được dùng bởi sản phẩm.">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <x-slot:footer>
            @if($attributes->hasPages()){{ $attributes->links() }}@endif
        </x-slot:footer>
    </x-admin.index-card>
@endsection
