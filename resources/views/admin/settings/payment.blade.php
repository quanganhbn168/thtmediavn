@extends('layouts.admin')

@section('title', 'Cấu hình SePay')
@section('page-title', 'Thanh toán SePay')

@section('content')
@include('components.admin.settings-nav')

<div class="row g-3">
    <div class="col-lg-7">
        <x-card type="primary" :outline="true" title="Cấu hình vận hành">
            <div class="alert {{ $sePayEnabled ? 'alert-success' : 'alert-warning' }}"><strong>{{ $sePayEnabled ? 'SePay đã sẵn sàng ở checkout.' : 'SePay chưa đủ cấu hình để bật ở checkout.' }}</strong><div class="small">Giá trị nhạy cảm được đọc từ môi trường máy chủ và không hiển thị lại.</div></div>
            <dl class="row mb-0">
                <dt class="col-sm-5">Môi trường</dt><dd class="col-sm-7"><span class="badge text-bg-{{ $sePay['mode'] === 'live' ? 'success' : 'warning' }}">{{ strtoupper($sePay['mode']) }}</span></dd>
                <dt class="col-sm-5">Thời gian chờ</dt><dd class="col-sm-7">{{ $sePay['payment_timeout_minutes'] }} phút</dd>
                <dt class="col-sm-5">Tiền tố mã</dt><dd class="col-sm-7"><code>{{ $sePay['payment_prefix'] }}</code></dd>
                <dt class="col-sm-5">Ngân hàng</dt><dd class="col-sm-7">{{ $sePay['bank_name'] ?: $sePay['bank_code'] ?: 'Chưa cấu hình' }}</dd>
                <dt class="col-sm-5">Số tài khoản</dt><dd class="col-sm-7">{{ $sePay['account_number'] ?: 'Chưa cấu hình' }}</dd>
                <dt class="col-sm-5">Chủ tài khoản</dt><dd class="col-sm-7">{{ $sePay['account_name'] ?: 'Chưa cấu hình' }}</dd>
                <dt class="col-sm-5">HMAC Secret</dt><dd class="col-sm-7"><span class="badge text-bg-{{ $hasWebhookSecret ? 'success' : 'danger' }}">{{ $hasWebhookSecret ? 'Đã cấu hình' : 'Chưa cấu hình' }}</span></dd>
                <dt class="col-sm-5">API Token</dt><dd class="col-sm-7"><span class="badge text-bg-{{ $hasApiToken ? 'success' : 'danger' }}">{{ $hasApiToken ? 'Đã cấu hình' : 'Chưa cấu hình' }}</span></dd>
            </dl>
        </x-card>

        <x-card type="secondary" :outline="true" title="Webhook" class="mt-3">
            <label class="form-label">URL khai báo trên SePay</label><div class="input-group"><input class="form-control" value="{{ $webhookUrl }}" readonly><button class="btn btn-outline-primary" type="button" data-copy-value="{{ $webhookUrl }}"><i class="bi bi-copy"></i></button></div>
            <p class="small text-muted mt-2 mb-0">Chọn xác thực HMAC-SHA256 và dùng cùng giá trị với <code>SEPAY_WEBHOOK_SECRET</code>.</p>
        </x-card>
    </div>
    <div class="col-lg-5">
        <x-card type="info" :outline="true" title="Trạng thái kết nối">
            <div class="d-flex justify-content-between border-bottom py-2"><span>Webhook cuối</span><strong>{{ $lastWebhookAt ? \Carbon\Carbon::parse($lastWebhookAt)->format('d/m/Y H:i') : 'Chưa nhận' }}</strong></div>
            <div class="d-flex justify-content-between border-bottom py-2"><span>Đối soát cuối</span><strong>{{ $syncState?->last_reconciled_at?->format('d/m/Y H:i') ?: 'Chưa chạy' }}</strong></div>
            <div class="d-flex justify-content-between border-bottom py-2"><span>Kết quả cuối</span><strong>{{ $syncState?->last_status ?: '—' }}</strong></div>
            <div class="d-flex justify-content-between py-2"><span>Cần kiểm tra</span><strong class="text-danger">{{ $unmatchedCount }}</strong></div>
            @if($syncState?->last_error)<div class="alert alert-danger small mt-2">{{ $syncState->last_error }}</div>@endif
            <div class="d-grid gap-2 mt-3">
                <form method="post" action="{{ route('admin.settings.payment.test-connection') }}">@csrf<button class="btn btn-outline-primary w-100"><i class="bi bi-plug me-1"></i>Kiểm tra kết nối</button></form>
                <form method="post" action="{{ route('admin.settings.payment.reconcile') }}">@csrf<button class="btn btn-primary w-100"><i class="bi bi-arrow-repeat me-1"></i>Đối soát ngay</button></form>
                <a class="btn btn-default" href="{{ route('admin.payment-transactions.index') }}">Xem giao dịch ngân hàng</a>
            </div>
        </x-card>
        <div class="alert alert-light border mt-3 small"><strong>Cron máy chủ:</strong><br><code>* * * * * php /path/to/artisan schedule:run</code></div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('[data-copy-value]').forEach(button => {
    button.addEventListener('click', async () => {
        await navigator.clipboard.writeText(button.dataset.copyValue || '');
        window.Swal?.fire({toast:true,position:'top-end',showConfirmButton:false,timer:1800,icon:'success',title:'Đã sao chép webhook URL'});
    });
}));
</script>
@endpush
