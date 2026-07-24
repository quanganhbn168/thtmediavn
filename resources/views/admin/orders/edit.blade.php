@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng')
@section('page-title', 'Chi tiết đơn hàng')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Đơn hàng</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $order->order_code }}</li>
</ol>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="row g-3">
    <div class="col-lg-8">
        <x-card type="primary" :outline="true" title="Thông tin đơn hàng" :collapsible="true">
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-1 fw-bold">{{ $order->order_code }}</p>
                    <p class="mb-0">{{ $order->customer_name }}</p>
                    <p class="mb-0">{{ $order->customer_phone }}</p>
                    <p class="mb-0">{{ $order->customer_email }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1 fw-bold">Địa chỉ giao hàng</p>
                    <p class="mb-0">{{ $order->shipping_address }}, {{ $order->shipping_ward }}</p>
                    <p class="mb-0">{{ $order->shipping_district }}, {{ $order->shipping_province }}</p>
                </div>
            </div>
        </x-card>

        <x-card type="secondary" :outline="true" title="Chi tiết sản phẩm" :collapsible="true" class="mt-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>SKU</th>
                            <th>SL</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img src="{{ $item->image ?: asset('images/no-image.png') }}" width="48" height="48" style="object-fit: cover" class="rounded">
                                        <div>
                                            <strong>{{ $item->product_name }}</strong>
                                            <small class="d-block text-muted">{{ $item->variant_name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->sku }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format((float)$item->total_price, 0, ',', '.') }}₫</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 ms-auto" style="max-width: 360px">
                <div class="d-flex justify-content-between"><span>Tạm tính</span><strong>{{ number_format((float)$order->subtotal_amount, 0, ',', '.') }}₫</strong></div>
                <div class="d-flex justify-content-between"><span>Giảm giá</span><span>-{{ number_format((float)$order->discount_amount, 0, ',', '.') }}₫</span></div>
                <div class="d-flex justify-content-between"><span>Vận chuyển</span><span>{{ number_format((float)$order->shipping_amount, 0, ',', '.') }}₫</span></div>
                <hr>
                <div class="d-flex justify-content-between fs-5"><strong>Tổng</strong><strong>{{ number_format((float)$order->total_amount, 0, ',', '.') }}₫</strong></div>
            </div>
        </x-card>
    </div>

    <div class="col-lg-4">
        <x-card type="info" :outline="true" title="Xử lý đơn hàng" :collapsible="true">
            <form action="{{ route('admin.orders.update', $order) }}" method="post">
                @csrf
                @method('PUT')
                <x-select name="status" label="Trạng thái đơn" :options="$statuses" :selected="$order->status" />
                <x-select name="payment_status" label="Thanh toán" :options="$paymentStatuses" :selected="$order->payment_status" />
                <x-select name="assigned_to" label="Nhân viên phụ trách" :options="$users" :selected="$order->assigned_to" />
                <x-textarea name="admin_note" label="Ghi chú nội bộ" :value="$order->admin_note" rows="5" />
                <button class="btn btn-primary mt-2">Cập nhật đơn hàng</button>
            </form>
        </x-card>

        <x-card type="secondary" :outline="true" title="Lịch sử trạng thái" :collapsible="true" class="mt-3">
            @foreach($order->statusHistories->sortByDesc('created_at') as $history)
                <div class="border-bottom py-2">
                    <strong>{{ $statuses[$history->to_status] ?? $history->to_status }}</strong>
                    <small class="d-block text-muted">{{ $history->created_at->format('d/m/Y H:i') }} · {{ $history->user?->name ?: 'Hệ thống' }}</small>
                </div>
            @endforeach
        </x-card>
    </div>
</div>
@endsection
