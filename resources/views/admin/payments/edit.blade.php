@extends('layouts.admin')

@section('title', 'Chỉnh sửa giao dịch')
@section('page-title', 'Chỉnh sửa giao dịch thanh toán')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Giao dịch thanh toán</a></li>
        <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
    </ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.payments.update', $payment) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-9">
                <x-card type="primary" :outline="true" title="Thông tin giao dịch" :collapsible="true">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-select
                                name="order_id"
                                label="Đơn hàng"
                                :options="$orders"
                                :selected="old('order_id', $payment->order_id)"
                                :required="true"
                            />
                        </div>
                        <div class="col-md-3">
                            <x-input name="payment_code" label="Mã giao dịch" :value="$payment->payment_code" :required="true" />
                        </div>
                        <div class="col-md-3">
                            <x-input name="transaction_id" label="Mã đối soát" :value="$payment->transaction_id" />
                        </div>
                        <div class="col-md-3">
                            <x-input
                                name="amount"
                                type="number"
                                label="Số tiền"
                                :value="$payment->amount"
                                min="0"
                                step="1000"
                                :required="true"
                            />
                        </div>
                        <div class="col-md-3">
                            <x-select
                                name="method"
                                label="Phương thức"
                                :options="$methods"
                                :selected="old('method', $payment->method)"
                                :required="true"
                            />
                        </div>
                        <div class="col-md-3">
                            <x-select
                                name="status"
                                label="Trạng thái"
                                :options="$statuses"
                                :selected="old('status', $payment->status)"
                                :required="true"
                            />
                        </div>
                        <div class="col-md-3">
                            <x-input
                                name="payment_date"
                                type="datetime-local"
                                label="Thời gian"
                                :value="$payment->payment_date?->format('Y-m-d\TH:i')"
                            />
                        </div>
                        <div class="col-12">
                            <x-textarea name="note" label="Ghi chú" :value="$payment->note" rows="4" />
                        </div>
                    </div>
                </x-card>
            </div>
            <div class="col-lg-3">
                <x-card type="info" :outline="true" title="Lưu ý" :collapsible="true">
                    <p class="mb-0 text-muted">
                        Cập nhật đúng phương thức, mã đối soát và trạng thái để đồng bộ giao dịch.
                    </p>
                </x-card>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('admin.payments.index') }}" class="btn btn-default">Hủy</a>
            <button class="btn btn-primary">Cập nhật giao dịch</button>
        </div>
    </form>
@endsection
