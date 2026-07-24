@extends('layouts.admin')

@section('title', 'Thương hiệu')
@section('page-title', 'Thương hiệu')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thương hiệu</li>
    </ol>
@endsection

@section('content')
    <x-admin.index-card
        title="Danh sách thương hiệu"
        description="Quản lý thương hiệu liên kết với sản phẩm."
        icon="bi-award"
        :create-url="route('admin.brands.create')"
        create-label="Thêm thương hiệu"
        resource="brand"
        :bulk-actions="['delete' => 'Xóa']"
        bulk-delete-warning="Thương hiệu đang có sản phẩm liên quan sẽ không thể xóa."
        :reorderable="true"
        :reorder-enabled="! request()->hasAny(['search', 'per_page'])"
        :order-start="$brands->firstItem() ?? 1"
    >
    <x-slot:filters>
        <form action="{{ route('admin.brands.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-xl-8 col-md-8">
                    <label for="brand-search" class="form-label">Từ khóa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" id="brand-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nhập tên hoặc slug">
                    </div>
                </div>
                <div class="col-xl-2 col-md-2">
                    <label for="brand-per-page" class="form-label">Số dòng</label>
                    <select id="brand-per-page" name="per_page" class="form-select">
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
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-default d-block" title="Xóa bộ lọc">
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
                    <th>Đường dẫn</th>
                    <th class="text-center" style="width:100px">Sản phẩm</th>
                    <th class="text-center" style="width:110px">Trạng thái</th>
                    <th class="text-center" style="width:110px">Trang chủ</th>
                    <th class="text-end" style="width:130px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                        <tr data-record-id="{{ $brand->id }}">
                            <td data-select-column class="text-center"><input form="admin-bulk-brand-form" type="checkbox" name="ids[]" value="{{ $brand->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $brand->name }}"></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if(filled($brand->logo))
                                        <img
                                            src="{{ Str::startsWith((string) $brand->logo, ['http://', 'https://']) ? $brand->logo : asset(ltrim((string) $brand->logo, '/')) }}"
                                            class="rounded border bg-white p-1"
                                            width="40"
                                            height="40"
                                            style="object-fit: contain"
                                            alt=""
                                        >
                                    @else
                                        <span class="rounded-circle bg-light text-secondary" style="width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center">
                                            <i class="bi bi-award"></i>
                                        </span>
                                    @endif
                                    <strong>{{ $brand->name }}</strong>
                                </div>
                            </td>
                            <td><span class="text-muted">/{{ $brand->slug }}</span></td>
                            <td class="text-center">{{ number_format($brand->products_count) }}</td>
                            <td class="text-center">
                                <x-toggle model="Brand" :id="$brand->id" field="is_active" :checked="$brand->is_active" label="" />
                            </td>
                            <td class="text-center">
                                <x-toggle model="Brand" :id="$brand->id" field="is_featured" :checked="$brand->is_featured" label="" />
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-default" title="Chỉnh sửa">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button
                                        type="submit"
                                        form="delete-brand-{{ $brand->id }}"
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
                                    <span><i class="bi bi-award"></i></span>
                                    <h5>Chưa có thương hiệu</h5>
                                    <p>Thêm thương hiệu để liên kết với sản phẩm.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @foreach($brands as $brand)
            <form
                id="delete-brand-{{ $brand->id }}"
                action="{{ route('admin.brands.destroy', $brand) }}"
                method="POST"
                class="d-none"
                data-admin-delete-form
                data-delete-title="Xóa thương hiệu này?"
                data-delete-warning="Thương hiệu đang được dùng bởi sản phẩm không thể xóa."
            >
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <x-slot:footer>
            @if($brands->hasPages())
                {{ $brands->links() }}
            @endif
        </x-slot:footer>
    </x-admin.index-card>
@endsection
