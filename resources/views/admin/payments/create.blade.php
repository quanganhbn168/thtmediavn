@extends('layouts.admin')

@section('title', 'Thêm giao dịch')
@section('page-title', 'Thêm giao dịch thanh toán')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Thanh toán</a></li>
    <li class="breadcrumb-item active" aria-current="page">Thêm mới</li>
</ol>
@endsection

@section('content')
<form id="admin-save-form" action="{{ route('admin.payments.store') }}" method="POST">
    @csrf
    <x-card type="primary" :outline="true" title="Thông tin giao dịch" :collapsible="true">
        <div class="row g-3">
            <div class="col-md-6"><x-select name="order_id" label="Đơn hàng" :options="$orders" :selected="old('order_id')" :required="true" /></div>
            <div class="col-md-3"><x-input name="payment_code" label="Mã giao dịch" :required="true" /></div>
            <div class="col-md-3"><x-input name="transaction_id" label="Mã đối soát" /></div>
            <div class="col-md-3"><x-input name="amount" type="number" label="Số tiền" min="0" step="1000" :required="true" /></div>
            <div class="col-md-3"><x-select name="method" label="Phương thức" :options="$methods" :selected="old('method')" :required="true" /></div>
            <div class="col-md-3"><x-select name="status" label="Trạng thái" :options="$statuses" :selected="old('status')" :required="true" /></div>
            <div class="col-md-3"><x-input name="payment_date" type="datetime-local" label="Thời gian" /></div>
            <div class="col-12"><x-textarea name="note" label="Ghi chú / căn cứ ghi nhận" rows="4" required /></div>
        </div>
    </x-card>

    <div class="text-end mt-3">
        <a href="{{ route('admin.payments.index') }}" class="btn btn-default">Hủy</a>
        <button class="btn btn-primary">Ghi nhận giao dịch</button>
    </div>
</form>
@endsection
