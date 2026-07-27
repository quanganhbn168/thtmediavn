@extends('layouts.admin')

@section('title', 'Tổng quan')
@section('page-title', 'Tổng quan cửa hàng')

@section('content')
<div class="row g-3 mb-4">
    @foreach([
        ['Đơn mới hôm nay', $todayOrders, 'bi-receipt', 'primary'],
        ['Đang chờ thanh toán', $pendingPaymentOrders, 'bi-hourglass-split', 'warning'],
        ['Đã thu hôm nay', number_format($todayCollected,0,',','.').'₫', 'bi-cash-coin', 'success'],
        ['Giao dịch cần kiểm tra', $unmatchedTransactions, 'bi-exclamation-triangle', 'danger'],
        ['Đơn đang xử lý', $processingOrders, 'bi-box-seam', 'info'],
        ['Sản phẩm sắp hết', $lowStockProducts, 'bi-box2', 'secondary'],
    ] as [$label,$value,$icon,$color])
        <div class="col-md-6 col-xl-4"><div class="card h-100"><div class="card-body d-flex justify-content-between"><div><div class="text-muted">{{ $label }}</div><div class="fs-3 fw-bold">{{ $value }}</div></div><i class="bi {{ $icon }} fs-2 text-{{ $color }}"></i></div></div></div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-xl-7"><x-admin.table-card title="Đơn hàng mới"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Thanh toán</th><th>Trạng thái</th><th class="text-end">Tổng tiền</th></tr></thead><tbody>@forelse($recentOrders as $order)<tr><td><a class="fw-bold" href="{{ route('admin.orders.edit',$order) }}">{{ $order->order_code }}</a></td><td>{{ $order->customer_name }}</td><td>{{ $order->payment_method === 'sepay_qr' ? 'SePay QR' : 'COD' }}<small class="d-block text-muted">{{ $order->payment_status }}</small></td><td>{{ \App\Services\OrderService::STATUSES[$order->status] ?? $order->status }}</td><td class="text-end">{{ number_format((float)$order->total_amount,0,',','.') }}₫</td></tr>@empty<tr><td colspan="5" class="text-center py-5">Chưa có đơn hàng.</td></tr>@endforelse</tbody></table></div></x-admin.table-card></div>
    <div class="col-xl-5"><x-admin.table-card title="Giao dịch SePay mới"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Thời gian / nội dung</th><th class="text-end">Số tiền</th><th>Khớp</th></tr></thead><tbody>@forelse($recentTransactions as $transaction)<tr><td><a href="{{ route('admin.payment-transactions.show',$transaction) }}">{{ $transaction->transaction_at?->format('d/m H:i') ?: '—' }}</a><small class="d-block text-muted">{{ \Illuminate\Support\Str::limit($transaction->transaction_content,40) }}</small></td><td class="text-end fw-bold">{{ number_format($transaction->amount,0,',','.') }}₫</td><td>{{ \App\Models\PaymentTransaction::MATCH_STATUSES[$transaction->match_status] ?? $transaction->match_status }}</td></tr>@empty<tr><td colspan="3" class="text-center py-5">Chưa có giao dịch.</td></tr>@endforelse</tbody></table></div></x-admin.table-card></div>
</div>

<div class="alert alert-light border mt-3 mb-0">Doanh thu tháng theo các phiếu thanh toán hoàn tất: <strong class="text-success">{{ number_format($monthlyRevenue,0,',','.') }}₫</strong></div>
@endsection
