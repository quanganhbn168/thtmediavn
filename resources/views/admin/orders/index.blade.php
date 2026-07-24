@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')
@section('page-title', 'Đơn hàng')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Đơn hàng</li>
</ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách đơn hàng"
    description="Theo dõi đơn hàng, trạng thái giao hàng và thanh toán."
    icon="bi-cart-check"
    resource="order"
    bulk-delete-warning="Việc xóa đơn hàng sẽ làm mất dữ liệu liên quan đến thanh toán và lịch sử xử lý."
>
    <x-slot:filters>
        <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-xl-5 col-md-6">
                <label for="order-search" class="form-label">Từ khóa</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input id="order-search" type="search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Mã đơn, khách hàng, điện thoại">
                </div>
            </div>
            <div class="col-xl-3 col-md-3">
                <label for="order-status" class="form-label">Trạng thái</label>
                <select id="order-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($statuses as $id => $label)
                        <option value="{{ $id }}" @selected(request('status') === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-3 col-md-3">
                <label for="order-payment-status" class="form-label">Thanh toán</label>
                <select id="order-payment-status" name="payment_status" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($paymentStatuses as $id => $label)
                        <option value="{{ $id }}" @selected(request('payment_status') === $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-1 col-md-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Lọc</button>
            </div>
            <div class="col-xl-1 col-md-2">
                @if(request()->hasAny(['search', 'status', 'payment_status']))
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-default d-block" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover text-nowrap align-middle mb-0">
            <thead>
                <tr>
                    <th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Thanh toán</th>
                    <th class="text-end" style="width:130px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr data-record-id="{{ $order->id }}">
                        <td data-select-column class="text-center"><input form="admin-bulk-order-form" type="checkbox" name="ids[]" value="{{ $order->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $order->order_code }}"></td>
                        <td>
                            <a class="fw-bold text-decoration-none" href="{{ route('admin.orders.edit', $order) }}">{{ $order->order_code }}</a>
                            <small class="d-block text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            {{ $order->customer_name }}
                            <small class="d-block text-muted">{{ $order->customer_phone }}</small>
                        </td>
                        <td><strong>{{ number_format((float)$order->total_amount, 0, ',', '.') }}₫</strong></td>
                        <td>
                            <span class="badge text-bg-primary">{{ $statuses[$order->status] ?? $order->status }}</span>
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">{{ $paymentStatuses[$order->payment_status] ?? $order->payment_status }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-default" title="Xem chi tiết"><i class="bi bi-eye"></i></a>
                                <button type="submit" form="delete-order-{{ $order->id }}" class="btn btn-default text-danger" title="Xóa đơn hàng"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="admin-empty">
                                <span><i class="bi bi-cart-x"></i></span>
                                <h5>Chưa có đơn hàng.</h5>
                                <p>Đơn mới sẽ xuất hiện tại đây.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach($orders as $order)
        <form id="delete-order-{{ $order->id }}" action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa đơn hàng này?" data-delete-warning="Đơn hàng này sẽ bị xóa khỏi hệ thống.">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    <x-slot:footer>
        @if($orders->hasPages())
            {{ $orders->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
