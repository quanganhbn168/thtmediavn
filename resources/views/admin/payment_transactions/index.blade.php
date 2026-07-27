@extends('layouts.admin')

@section('title', 'Giao dịch ngân hàng')
@section('page-title', 'Giao dịch ngân hàng SePay')

@section('content')
@php
    $adminUser = auth('admin')->user();
    $canReconcile = \App\Support\AdminPermission::can($adminUser, 'reconcile payments');
    $canViewAudit = \App\Support\AdminPermission::can($adminUser, 'view webhook logs');
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
    <div><h2 class="h4 mb-1">Dòng tiền nhận từ SePay</h2><p class="text-muted mb-0">Webhook và API v2 dùng chung một sổ giao dịch, có chống trùng ở tầng cơ sở dữ liệu.</p></div>
    @if($canReconcile)<form action="{{ route('admin.settings.payment.reconcile') }}" method="post">@csrf<button class="btn btn-primary"><i class="bi bi-arrow-repeat me-1"></i>Đối soát ngay</button></form>@endif
</div>

<div class="card mb-3"><div class="card-body">
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.payment-transactions.index') }}" class="btn btn-sm {{ request('status') ? 'btn-default' : 'btn-primary' }}">Tất cả</a>
        @foreach($statuses as $key => $label)<a href="{{ route('admin.payment-transactions.index', ['status' => $key]) }}" class="btn btn-sm {{ request('status') === $key ? 'btn-primary' : 'btn-default' }}">{{ $label }} <span class="badge text-bg-light ms-1">{{ $statusCounts[$key] ?? 0 }}</span></a>@endforeach
    </div>
    <form method="get" class="row g-2"><div class="col-md-9"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Tìm nội dung, mã thanh toán, mã tham chiếu…"></div><div class="col-md-3 d-grid"><button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Tìm giao dịch</button></div></form>
</div></div>

<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Thời gian</th><th>Ngân hàng / tài khoản</th><th>Nội dung</th><th>Mã thanh toán</th><th class="text-end">Số tiền</th><th>Đơn hàng</th><th>Trạng thái</th></tr></thead>
    <tbody>@forelse($transactions as $transaction)
        @php($color = match($transaction->match_status){'matched'=>'success','unmatched'=>'warning','amount_mismatch'=>'danger','late'=>'secondary','duplicate'=>'info',default=>'light'})
        <tr>
            <td>@if($canViewAudit)<a class="fw-semibold" href="{{ route('admin.payment-transactions.show', $transaction) }}">{{ $transaction->transaction_at?->format('d/m/Y H:i:s') ?: '—' }}</a>@else<span class="fw-semibold">{{ $transaction->transaction_at?->format('d/m/Y H:i:s') ?: '—' }}</span>@endif<small class="d-block text-muted">{{ strtoupper($transaction->source) }}</small></td>
            <td>{{ $transaction->bank_gateway ?: '—' }}<small class="d-block text-muted">{{ $transaction->account_number ?: '—' }}</small></td>
            <td style="min-width:240px">{{ \Illuminate\Support\Str::limit($transaction->transaction_content, 90) }}<small class="d-block text-muted">{{ $transaction->reference_code ?: $transaction->provider_transaction_id }}</small></td>
            <td><code>{{ $transaction->payment_code ?: '—' }}</code></td>
            <td class="text-end fw-bold">{{ number_format($transaction->amount, 0, ',', '.') }}₫</td>
            <td>@if($transaction->order)<a href="{{ route('admin.orders.edit', $transaction->order) }}">{{ $transaction->order->order_code }}</a>@else — @endif</td>
            <td><span class="badge text-bg-{{ $color }}">{{ $statuses[$transaction->match_status] ?? $transaction->match_status }}</span></td>
        </tr>
    @empty<tr><td colspan="7" class="text-center py-5 text-muted">Chưa có giao dịch SePay.</td></tr>@endforelse</tbody>
</table></div>@if($transactions->hasPages())<div class="card-footer">{{ $transactions->links() }}</div>@endif</div>
@endsection
