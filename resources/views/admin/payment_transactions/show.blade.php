@extends('layouts.admin')

@section('title', 'Chi tiết giao dịch SePay')
@section('page-title', 'Chi tiết giao dịch ngân hàng')

@section('content')
@php
    $statusLabel = $statuses[$transaction->match_status] ?? $transaction->match_status;
    $canOverridePayment = \App\Support\AdminPermission::can(auth('admin')->user(), 'override payment status');
@endphp
<div class="d-flex justify-content-between gap-2 mb-3"><a href="{{ route('admin.payment-transactions.index') }}" class="btn btn-default"><i class="bi bi-arrow-left me-1"></i>Danh sách</a><span class="badge text-bg-primary align-self-center fs-6">{{ $statusLabel }}</span></div>
<div class="row g-3">
    <div class="col-lg-7">
        <x-card type="primary" :outline="true" title="Dữ liệu giao dịch">
            <dl class="row mb-0">
                <dt class="col-sm-4">Mã SePay</dt><dd class="col-sm-8"><code>{{ $transaction->provider_transaction_id }}</code></dd>
                <dt class="col-sm-4">Nguồn nhận</dt><dd class="col-sm-8">{{ strtoupper($transaction->source) }} · {{ $transaction->signature_verified ? 'HMAC hợp lệ' : 'API Bearer token' }}</dd>
                <dt class="col-sm-4">Thời gian</dt><dd class="col-sm-8">{{ $transaction->transaction_at?->format('d/m/Y H:i:s') ?: '—' }}</dd>
                <dt class="col-sm-4">Ngân hàng</dt><dd class="col-sm-8">{{ $transaction->bank_gateway }} · {{ $transaction->account_number }}</dd>
                <dt class="col-sm-4">Số tiền</dt><dd class="col-sm-8 fs-5 fw-bold">{{ number_format($transaction->amount, 0, ',', '.') }}₫</dd>
                <dt class="col-sm-4">Mã thanh toán</dt><dd class="col-sm-8"><code>{{ $transaction->payment_code ?: '—' }}</code></dd>
                <dt class="col-sm-4">Nội dung</dt><dd class="col-sm-8">{{ $transaction->transaction_content ?: '—' }}</dd>
                <dt class="col-sm-4">Mã tham chiếu</dt><dd class="col-sm-8">{{ $transaction->reference_code ?: '—' }}</dd>
                <dt class="col-sm-4">Đơn hàng</dt><dd class="col-sm-8">@if($transaction->order)<a href="{{ route('admin.orders.edit', $transaction->order) }}">{{ $transaction->order->order_code }}</a>@else — @endif</dd>
                @if($transaction->processing_error)<dt class="col-sm-4">Ghi chú xử lý</dt><dd class="col-sm-8 text-danger">{{ $transaction->processing_error }}</dd>@endif
            </dl>
        </x-card>

        <x-card type="secondary" :outline="true" title="Raw payload (chỉ đọc)" class="mt-3"><pre class="bg-body-tertiary border rounded p-3 mb-0 small" style="white-space:pre-wrap">{{ json_encode($transaction->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre></x-card>
    </div>
    <div class="col-lg-5">
        @if(!$transaction->payment_id && $canOverridePayment)
        <x-card type="warning" :outline="true" title="Gắn giao dịch thủ công">
            <p class="small text-muted">Chỉ dùng sau khi đã kiểm tra đúng người chuyển, số tiền và khả năng đáp ứng tồn kho.</p>
            <form method="post" action="{{ route('admin.payment-transactions.attach', $transaction) }}">@csrf
                <label class="form-label" for="order_id">Đơn hàng</label>
                <select class="form-select" id="order_id" name="order_id" required><option value="">Chọn đơn hàng</option>@foreach($orders as $order)<option value="{{ $order->id }}">{{ $order->order_code }} · {{ $order->customer_name }} · {{ number_format((float)$order->total_amount,0,',','.') }}₫</option>@endforeach</select>
                <label class="form-label mt-3" for="reason">Lý do / căn cứ đối soát</label>
                <textarea class="form-control" id="reason" name="reason" rows="4" minlength="10" required></textarea>
                <button class="btn btn-warning mt-3"><i class="bi bi-link-45deg me-1"></i>Gắn vào đơn hàng</button>
            </form>
        </x-card>
        @elseif($transaction->payment_id)
        <x-card type="success" :outline="true" title="Phiếu thanh toán"><p class="mb-1">Đã tạo phiếu <strong>{{ $transaction->payment?->payment_code }}</strong>.</p><p class="small text-muted mb-0">Dữ liệu SePay tự động bị khóa sửa và xóa để bảo toàn audit.</p></x-card>
        @else
        <x-card type="secondary" :outline="true" title="Chưa gắn đơn hàng"><p class="small text-muted mb-0">Tài khoản này chỉ có quyền xem dữ liệu audit. Cần quyền xử lý ngoại lệ thanh toán để gắn giao dịch thủ công.</p></x-card>
        @endif
    </div>
</div>
@endsection
