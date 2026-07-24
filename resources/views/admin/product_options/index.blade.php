@extends('layouts.admin')

@section('title', 'Thuộc tính sản phẩm')
@section('page-title', 'Thuộc tính sản phẩm')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thuộc tính sản phẩm</li>
    </ol>
@endsection

@section('content')
    <x-admin.index-card
        title="Danh sách thuộc tính"
        description="Khai báo thuộc tính và giá trị dùng cho biến thể sản phẩm."
        icon="bi-sliders"
        :create-url="route('admin.product-options.create')"
        create-label="Thêm thuộc tính"
        resource="product_option"
        :bulk-actions="['delete' => 'Xóa']"
        bulk-delete-warning="Thuộc tính đang được dùng trong sản phẩm sẽ không thể xóa."
        :reorderable="true"
        :reorder-enabled="! request()->hasAny(['search', 'per_page'])"
        :order-start="$options->firstItem() ?? 1"
    >
    <x-slot:filters>
        <form action="{{ route('admin.product-options.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-xl-8 col-md-8">
                    <label for="option-search" class="form-label">Từ khóa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" id="option-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nhập tên hoặc slug">
                    </div>
                </div>
                <div class="col-xl-2 col-md-2">
                    <label for="option-per-page" class="form-label">Số dòng</label>
                    <select id="option-per-page" name="per_page" class="form-select">
                        @foreach([10, 20, 25, 50] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-1 col-md-1 d-flex align-items-end">
                    <button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Lọc</button>
                </div>
                <div class="col-xl-1 col-md-1">
                    @if(request()->hasAny(['search', 'per_page']))
                        <a href="{{ route('admin.product-options.index') }}" class="btn btn-default d-block" title="Xóa bộ lọc">
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
                    <th>Thuộc tính</th>
                    <th>Mã</th>
                    <th>Giá trị</th>
                    <th>Kiểu hiển thị</th>
                        <th class="text-end" style="width:130px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($options as $option)
                        <tr data-record-id="{{ $option->id }}">
                            <td data-select-column class="text-center"><input form="admin-bulk-product_option-form" type="checkbox" name="ids[]" value="{{ $option->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $option->name }}"></td>
                            <td>
                                <strong>{{ $option->name }}</strong>
                            </td>
                            <td><small class="text-muted">{{ $option->slug }}</small></td>
                            <td>
                                @forelse($option->values as $value)
                                    <span class="badge text-bg-light me-1">{{ $value->value }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>{{ ['button' => 'Nút chọn', 'color' => 'Màu sắc', 'select' => 'Danh sách'][$option->display_type] ?? $option->display_type }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.product-options.edit', $option) }}" class="btn btn-default" title="Chỉnh sửa">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button
                                        type="submit"
                                        form="delete-product-option-{{ $option->id }}"
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
                            <td colspan="6" class="text-center py-5">
                                <div class="admin-empty">
                                    <span><i class="bi bi-sliders"></i></span>
                                    <h5>Chưa có thuộc tính</h5>
                                    <p>Tạo thuộc tính đầu tiên để tạo biến thể sản phẩm.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @foreach($options as $option)
            <form
                id="delete-product-option-{{ $option->id }}"
                action="{{ route('admin.product-options.destroy', $option) }}"
                method="POST"
                class="d-none"
                data-admin-delete-form
                data-delete-title="Xóa thuộc tính này?"
                data-delete-warning="Nếu thuộc tính đang được dùng, thao tác sẽ bị chặn."
            >
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <x-slot:footer>
            @if($options->hasPages())
                {{ $options->links() }}
            @endif
        </x-slot:footer>
    </x-admin.index-card>
@endsection
