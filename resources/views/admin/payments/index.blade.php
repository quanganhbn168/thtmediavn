@extends('layouts.admin')

@section('title', 'Quản lý giao dịch')
@section('page-title', 'Thanh toán')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Thanh toán</li>
</ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách giao dịch"
    description="Ghi nhận giao dịch và tự động đồng bộ công nợ đơn hàng."
    icon="bi-wallet2"
    :create-url="route('admin.payments.create')"
    create-label="Thêm giao dịch"
    resource="payment"
    bulk-delete-warning="Xóa giao dịch sẽ làm thay đổi công nợ đơn hàng liên quan."
>
    <x-slot:filters>
        <form action="{{ route('admin.payments.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-xl-4 col-md-6">
                <label for="payment-search" class="form-label">Từ khóa</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input id="payment-search" type="search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Mã giao dịch hoặc đối soát">
                </div>
            </div>
            <div class="col-xl-3 col-md-3">
                <label for="payment-method" class="form-label">Phương thức</label>
                <select id="payment-method" name="method" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($methods as $id => $name)
                        <option value="{{ $id }}" @selected(request('method') === $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-3 col-md-3">
                <label for="payment-status" class="form-label">Trạng thái</label>
                <select id="payment-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($statuses as $id => $name)
                        <option value="{{ $id }}" @selected(request('status') === $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-1 col-md-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Lọc</button>
            </div>
            <div class="col-xl-1 col-md-3">
                @if(request()->hasAny(['search', 'method', 'status']))
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-default d-block" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th>
                    <th>Mã giao dịch</th>
                    <th>Đơn hàng</th>
                    <th>Số tiền</th>
                    <th>Phương thức</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                    <th class="text-end" style="width:130px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr data-record-id="{{ $payment->id }}">
                        <td data-select-column class="text-center"><input form="admin-bulk-payment-form" type="checkbox" name="ids[]" value="{{ $payment->id }}" class="form-check-input" data-check-item aria-label="Chọn giao dịch {{ $payment->payment_code }}"></td>
                        <td>
                            <a href="{{ route('admin.payments.edit', $payment) }}" class="fw-semibold text-decoration-none">{{ $payment->payment_code }}</a>
                            <small class="d-block text-body-secondary">{{ $payment->transaction_id ?? '—' }}</small>
                        </td>
                        <td>{{ $payment->order?->order_code ?? '—' }}</td>
                        <td><strong>{{ number_format((float)$payment->amount, 0, ',', '.') }} ₫</strong></td>
                        <td>{{ $methods[$payment->method] ?? $payment->method }}</td>
                        <td>
                            <span class="badge text-bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">{{ $statuses[$payment->status] ?? $payment->status }}</span>
                        </td>
                        <td>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y H:i') : '—' }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-default" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a>
                                <button type="submit" form="delete-payment-{{ $payment->id }}" class="btn btn-default text-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="admin-empty">
                                <span><i class="bi bi-wallet2"></i></span>
                                <h5>Chưa có giao dịch.</h5>
                                <p>Tạo giao dịch đầu tiên để theo dõi công nợ.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach($payments as $payment)
        <form id="delete-payment-{{ $payment->id }}" action="{{ route('admin.payments.destroy', $payment) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa giao dịch này?" data-delete-warning="Công nợ đơn hàng liên quan sẽ được đồng bộ lại.">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
    <x-slot:footer>
        @if($payments->hasPages())
            {{ $payments->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
