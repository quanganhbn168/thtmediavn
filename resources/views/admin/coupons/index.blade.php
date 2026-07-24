@extends('layouts.admin')

@section('title', 'Quản lý mã giảm giá')
@section('page-title', 'Mã giảm giá')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Mã giảm giá</li>
</ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách mã giảm giá"
    description="Quản lý mã giảm giá, thời gian hiệu lực và giới hạn áp dụng."
    icon="bi-ticket-perforated"
    :create-url="route('admin.coupons.create')"
    create-label="Thêm mã giảm giá"
    resource="coupon"
    bulk-delete-warning="Các giao dịch sử dụng mã này sẽ không bị ảnh hưởng."
>
    <x-slot:filters>
        <form action="{{ route('admin.coupons.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-xl-4 col-md-6">
                <label for="coupon-search" class="form-label">Từ khóa</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" class="form-control" id="coupon-search" name="search" value="{{ request('search') }}" placeholder="Nhập mã hoặc tên">
                </div>
            </div>
            <div class="col-xl-3 col-md-3">
                <label for="coupon-type" class="form-label">Loại mã</label>
                <select class="form-select" id="coupon-type" name="type">
                    <option value="">Tất cả</option>
                    <option value="fixed" @selected(request('type') === 'fixed')>Giảm tiền</option>
                    <option value="percent" @selected(request('type') === 'percent')>Phần trăm</option>
                    <option value="free_shipping" @selected(request('type') === 'free_shipping')>Miễn phí giao hàng</option>
                </select>
            </div>
            <div class="col-xl-3 col-md-3">
                <label for="coupon-status" class="form-label">Trạng thái</label>
                <select class="form-select" id="coupon-status" name="status">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Đang hoạt động</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Ngừng hoạt động</option>
                </select>
            </div>
            <div class="col-xl-1 col-md-3">
                <label for="coupon-per-page" class="form-label">Số dòng</label>
                <select class="form-select" id="coupon-per-page" name="per_page">
                    @foreach([10, 25, 50] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-1 col-md-3 d-flex">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Lọc</button>
            </div>
            <div class="col-xl-1 col-md-3">
                @if(request()->hasAny(['search', 'type', 'status', 'per_page']))
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-default d-block" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover text-nowrap align-middle mb-0">
            <thead>
                <tr>
                    <th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th>
                    <th style="width:80px">Mã</th>
                    <th>Ưu đãi</th>
                    <th>Điều kiện</th>
                    <th>Thời gian</th>
                    <th>Đã dùng</th>
                    <th>Trạng thái</th>
                    <th class="text-end" style="width:130px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                    <tr data-record-id="{{ $coupon->id }}">
                        <td data-select-column class="text-center"><input form="admin-bulk-coupon-form" type="checkbox" name="ids[]" value="{{ $coupon->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $coupon->code }}"></td>
                        <td class="text-body-secondary">#{{ $coupon->id }}</td>
                        <td>
                            <strong>{{ $coupon->code }}</strong>
                            <small class="d-block text-muted">{{ $coupon->name }}</small>
                        </td>
                        <td>
                            @if($coupon->type === 'percent')
                                {{ number_format((float) $coupon->value, 0, ',', '.') }}%
                                @if($coupon->max_discount)
                                    (Giảm tối đa {{ number_format((float) $coupon->max_discount, 0, ',', '.') }}₫)
                                @endif
                            @elseif($coupon->type === 'free_shipping')
                                Miễn phí giao hàng
                            @else
                                {{ number_format((float) $coupon->value, 0, ',', '.') }}₫
                            @endif
                        </td>
                        <td>
                            Tối thiểu {{ number_format((float) $coupon->minimum_order, 0, ',', '.') }}₫
                            <small class="d-block text-muted">
                                {{ $coupon->usage_limit ? 'Lượt tối đa: '.$coupon->usage_limit : 'Không giới hạn lượt' }}
                            </small>
                        </td>
                        <td>
                            {{ $coupon->starts_at ? $coupon->starts_at->format('d/m/Y H:i') : '—' }} <br>
                            {{ $coupon->ends_at ? $coupon->ends_at->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td>{{ $coupon->used_count }} / {{ $coupon->usage_limit ?: '∞' }}</td>
                        <td>
                            <span class="badge text-bg-{{ $coupon->is_active ? 'success' : 'secondary' }}">
                                {{ $coupon->is_active ? 'Hoạt động' : 'Tắt' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-default" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a>
                                <button type="submit" form="delete-coupon-{{ $coupon->id }}" class="btn btn-default text-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="admin-empty"><span><i class="bi bi-ticket-perforated"></i></span><h5>Chưa có mã giảm giá</h5><p>Hãy tạo mã giảm giá đầu tiên hoặc thay đổi bộ lọc.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach($coupons as $coupon)
        <form id="delete-coupon-{{ $coupon->id }}" action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa mã giảm giá này?" data-delete-warning="Các giao dịch áp dụng mã sẽ không bị ảnh hưởng.">@csrf @method('DELETE')</form>
    @endforeach

    <x-slot:footer>
        @if($coupons->hasPages())
            {{ $coupons->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
