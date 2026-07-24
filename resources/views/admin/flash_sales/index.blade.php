@extends('layouts.admin')

@section('title', 'Quản lý Flash Sale')
@section('page-title', 'Flash Sale')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Flash Sale</li>
</ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách Flash Sale"
    description="Chương trình giảm giá theo thời gian và số lượng."
    icon="bi-lightning-charge"
    :create-url="route('admin.flash-sales.create')"
    create-label="Tạo Flash Sale"
    resource="flash_sale"
    bulk-delete-warning="Sản phẩm trong chương trình sẽ bị gỡ khỏi Flash Sale trước khi xóa."
>
    <x-slot:filters>
        <form action="{{ route('admin.flash-sales.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-xl-4 col-md-8">
                <label for="flash-sale-search" class="form-label">Từ khóa</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" class="form-control" id="flash-sale-search" name="search" value="{{ request('search') }}" placeholder="Tên chương trình">
                </div>
            </div>
            <div class="col-xl-3 col-md-3">
                <label for="flash-sale-status" class="form-label">Trạng thái</label>
                <select class="form-select" id="flash-sale-status" name="is_active">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('is_active') === 'active')>Đang hoạt động</option>
                    <option value="inactive" @selected(request('is_active') === 'inactive')>Ngừng hoạt động</option>
                </select>
            </div>
            <div class="col-xl-1 col-md-3">
                <label for="flash-sale-per-page" class="form-label">Số dòng</label>
                <select class="form-select" id="flash-sale-per-page" name="per_page">
                    @foreach([10, 25, 50] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-1 col-md-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Lọc</button>
            </div>
            <div class="col-xl-1 col-md-3">
                @if(request()->hasAny(['search', 'is_active', 'per_page']))
                    <a href="{{ route('admin.flash-sales.index') }}" class="btn btn-default d-block" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover text-nowrap align-middle mb-0">
            <thead>
                <tr>
                    <th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th>
                    <th>Chương trình</th>
                    <th>Thời gian</th>
                    <th class="text-center">Sản phẩm</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end" style="width:130px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr data-record-id="{{ $sale->id }}">
                        <td data-select-column class="text-center"><input form="admin-bulk-flash_sale-form" type="checkbox" name="ids[]" value="{{ $sale->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $sale->name }}"></td>
                        <td><strong>{{ $sale->name }}</strong></td>
                        <td>
                            {{ $sale->starts_at ? $sale->starts_at->format('d/m/Y H:i') : '—' }}<br>
                            {{ $sale->ends_at ? $sale->ends_at->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="text-center">{{ $sale->items_count }}</td>
                        <td class="text-center">
                            <span class="badge text-bg-{{ $sale->isRunning() ? 'success' : 'secondary' }}">
                                {{ $sale->isRunning() ? 'Đang diễn ra' : ($sale->starts_at->isFuture() ? 'Sắp diễn ra' : 'Đã kết thúc') }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.flash-sales.edit', $sale) }}" class="btn btn-default" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a>
                                <button type="submit" form="delete-flash-sale-{{ $sale->id }}" class="btn btn-default text-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="admin-empty">
                                <span><i class="bi bi-lightning-charge"></i></span>
                                <h5>Chưa có Flash Sale.</h5>
                                <p>Tạo chương trình đầu tiên để bắt đầu khuyến mãi theo thời gian.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach($sales as $sale)
        <form id="delete-flash-sale-{{ $sale->id }}" action="{{ route('admin.flash-sales.destroy', $sale) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa Flash Sale này?" data-delete-warning="Sản phẩm được liên kết sẽ được gỡ ra khỏi chương trình.">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    <x-slot:footer>
        @if($sales->hasPages())
            {{ $sales->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
