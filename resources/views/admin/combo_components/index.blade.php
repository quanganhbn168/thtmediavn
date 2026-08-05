@extends('layouts.admin')

@section('title', 'Thành phần Combo')
@section('page-title', 'Thành phần Combo')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.combos.index') }}">Combo</a></li>
        <li class="breadcrumb-item active">{{ $combo->name }}</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Thành phần: {{ $combo->name }}"
    description="Quản lý sản phẩm, biến thể và số lượng được trừ tồn kho cho Combo này."
    icon="bi-diagram-3"
    :create-url="route('admin.combos.components.create', $combo)"
    create-label="Thêm thành phần"
    resource="combo_component"
>
    <x-slot:filters>
        <form action="{{ route('admin.combos.components.index', $combo) }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label" for="combo-component-search">Tìm sản phẩm</label>
                <input id="combo-component-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Tên sản phẩm thành phần">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="combo-component-per-page">Số dòng</label>
                <select id="combo-component-per-page" class="form-select" name="per_page">
                    @foreach([10, 20, 25, 50] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel me-1"></i>Lọc</button></div>
        </form>
    </x-slot:filters>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a class="btn btn-default btn-sm" href="{{ route('admin.combos.edit', $combo) }}"><i class="bi bi-arrow-left me-1"></i>Về Combo</a>
        <span class="text-muted small">{{ $components->total() }} thành phần</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Sản phẩm</th><th>Biến thể</th><th class="text-center">Số lượng / Combo</th><th class="text-center">Thứ tự</th><th class="text-end">Thao tác</th></tr></thead>
            <tbody>
            @forelse($components as $component)
                <tr>
                    <td><strong>{{ $component->product?->name ?: 'Sản phẩm đã xóa' }}</strong><small class="d-block text-muted">SKU: {{ $component->variant?->sku ?: 'Mặc định sản phẩm' }}</small></td>
                    <td>{{ $component->variant?->name ?: 'Mặc định' }}</td>
                    <td class="text-center"><strong>{{ $component->quantity }}</strong></td>
                    <td class="text-center">{{ $component->sort_order }}</td>
                    <td class="text-end"><div class="btn-group btn-group-sm"><a class="btn btn-default" href="{{ route('admin.combos.components.edit', [$combo, $component]) }}" title="Chỉnh sửa"><i class="bi bi-pencil"></i></a><button type="submit" form="delete-combo-component-{{ $component->id }}" class="btn btn-default text-danger" title="Xóa"><i class="bi bi-trash"></i></button></div></td>
                </tr>
                <form id="delete-combo-component-{{ $component->id }}" action="{{ route('admin.combos.components.destroy', [$combo, $component]) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa thành phần này?" data-delete-warning="Thành phần sẽ không còn được trừ tồn kho khi bán Combo.">@csrf @method('DELETE')</form>
            @empty
                <tr><td colspan="5" class="text-center py-5"><div class="admin-empty"><span><i class="bi bi-diagram-3"></i></span><h5>Chưa có thành phần</h5><p>Thêm sản phẩm đầu tiên để Combo có thể tính tồn kho.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-slot:footer>@if($components->hasPages()){{ $components->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
