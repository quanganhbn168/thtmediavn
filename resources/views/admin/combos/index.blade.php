@extends('layouts.admin')

@section('title', 'Combo')
@section('page-title', 'Combo')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Combo</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách Combo"
    description="Quản lý giá bán và các sản phẩm thành phần của Combo."
    icon="bi-boxes"
    :create-url="route('admin.combos.create')"
    create-label="Thêm Combo"
    resource="combo"
    :bulk-actions="['activate' => 'Hiển thị', 'deactivate' => 'Ẩn', 'delete' => 'Xóa']"
    bulk-delete-warning="Combo được xóa sẽ không còn hiển thị và các thành phần tồn kho liên quan cũng bị xóa."
    :reorderable="true"
    :reorder-enabled="! request()->hasAny(['search', 'category', 'status', 'per_page'])"
    :order-start="$combos->firstItem() ?? 1"
>
    <x-slot:filters>
        <form action="{{ route('admin.combos.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-xl-5 col-md-6"><label class="form-label" for="combo-search">Từ khóa</label><input id="combo-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Tên Combo hoặc slug"></div>
            <div class="col-xl-3 col-md-3"><label class="form-label" for="combo-category">Danh mục Combo</label><select id="combo-category" class="form-select" name="category"><option value="">Tất cả</option>@foreach($categories as $id => $name)<option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $name }}</option>@endforeach</select></div>
            <div class="col-xl-2 col-md-2"><label class="form-label" for="combo-status">Trạng thái</label><select id="combo-status" class="form-select" name="status"><option value="">Tất cả</option><option value="active" @selected(request('status') === 'active')>Đang bán</option><option value="inactive" @selected(request('status') === 'inactive')>Ẩn / nháp</option></select></div>
            <div class="col-xl-2 col-md-2"><label class="form-label" for="combo-per-page">Số dòng</label><select id="combo-per-page" class="form-select" name="per_page">@foreach([10, 20, 25, 50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>@endforeach</select></div>
            <div class="col-xl-1 col-md-1 d-flex gap-2"><button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Lọc</button></div>
            <div class="col-xl-1 col-md-1">@if(request()->hasAny(['search', 'category', 'status', 'per_page']))<a href="{{ route('admin.combos.index') }}" class="btn btn-default d-block" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a>@endif</div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Combo</th><th>Danh mục</th><th>Giá bán</th><th class="text-center">Thành phần</th><th class="text-center">Nổi bật</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead>
            <tbody>
            @forelse($combos as $combo)
                <tr data-record-id="{{ $combo->id }}">
                    <td data-select-column class="text-center"><input form="admin-bulk-combo-form" type="checkbox" name="ids[]" value="{{ $combo->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $combo->name }}"></td>
                    <td><div class="d-flex gap-2 align-items-center"><img src="{{ $combo->image_url }}" class="admin-product-thumb rounded" width="54" height="54" alt=""><div><strong>{{ $combo->name }}</strong><small class="d-block text-muted">/{{ $combo->slug }}</small></div></div></td>
                    <td>{{ $combo->category?->name ?: 'Chưa phân loại' }}</td>
                    <td><strong>{{ number_format((float) $combo->price, 0, ',', '.') }}₫</strong>@if($combo->compare_price)<small class="d-block text-muted text-decoration-line-through">{{ number_format((float) $combo->compare_price, 0, ',', '.') }}₫</small>@endif</td>
                    <td class="text-center"><a href="{{ route('admin.combos.components.index', $combo) }}" class="text-decoration-none"><strong>{{ $combo->items_count ?? $combo->items->count() }}</strong> <i class="bi bi-box-arrow-up-right small"></i></a></td>
                    <td class="text-center"><x-toggle model="Combo" :id="$combo->id" field="is_featured" :checked="$combo->is_featured" label="" /></td>
                    <td class="text-center"><span class="badge {{ $combo->is_active && $combo->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $combo->is_active && $combo->status === 'active' ? 'Đang bán' : 'Ẩn' }}</span></td>
                    <td class="text-end"><div class="btn-group btn-group-sm">@if($combo->isVisibleOnSite())<a class="btn btn-default" href="{{ route('combo.show', $combo->slug) }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i></a>@endif<a class="btn btn-default" href="{{ route('admin.combos.edit', $combo) }}"><i class="bi bi-pencil"></i></a><button type="submit" form="delete-combo-{{ $combo->id }}" class="btn btn-default text-danger"><i class="bi bi-trash"></i></button></div></td>
                </tr>
                <form id="delete-combo-{{ $combo->id }}" action="{{ route('admin.combos.destroy', $combo) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa Combo này?" data-delete-warning="Combo sẽ không còn hiển thị trên website.">@csrf @method('DELETE')</form>
            @empty
                <tr><td colspan="8" class="text-center py-5">Chưa có Combo.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-slot:footer>@if($combos->hasPages()){{ $combos->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
