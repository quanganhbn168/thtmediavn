@extends('layouts.master')

@section('title', 'Thanh toán đơn '.$order->order_code.' — '.$website['name'])

@section('content')
<section class="section-space bg-soft sepay-payment-page">
    <div class="container">
        <div class="content-card mx-auto sepay-payment-card"
             data-sepay-payment
             data-status-url="{{ route('checkout.payment.status', $order->payment_public_token) }}"
             data-success-url="{{ route('checkout.success', ['code' => $order->order_code, 'token' => $order->payment_public_token]) }}"
             data-expires-at="{{ $order->payment_expires_at?->toIso8601String() }}">
            <div class="text-center mb-4">
                <span class="sepay-kicker"><i class="bi bi-shield-check me-1"></i>Thanh toán QR bảo mật</span>
                <h1 class="h2 mt-3">Quét mã để thanh toán</h1>
                <p class="text-muted mb-0">Đơn {{ $order->order_code }} · giữ hàng đến {{ $order->payment_expires_at?->format('H:i d/m/Y') }}</p>
            </div>

            <div class="alert alert-success text-center {{ $order->payment_status === 'paid' ? '' : 'd-none' }}" data-sepay-success>
                <i class="bi bi-check-circle-fill me-1"></i><strong>RHEA đã nhận được thanh toán.</strong>
                <a href="{{ route('checkout.success', ['code' => $order->order_code, 'token' => $order->payment_public_token]) }}">Xem xác nhận đơn hàng</a>
            </div>
            <div class="alert alert-warning text-center {{ $order->status === 'payment_expired' ? '' : 'd-none' }}" data-sepay-expired>
                <i class="bi bi-clock-history me-1"></i>Phiên thanh toán đã hết hạn và hàng đã được trả lại kho. Nếu đã chuyển tiền, vui lòng liên hệ RHEA để kiểm tra.
            </div>

            <div class="row g-4 align-items-center" data-sepay-waiting @if($order->payment_status === 'paid' || $order->status === 'payment_expired') hidden @endif>
                <div class="col-md-5 text-center">
                    <div class="sepay-qr-wrap"><img src="{{ $qrUrl }}" alt="VietQR thanh toán đơn {{ $order->order_code }}" width="320" height="320"></div>
                    <div class="sepay-waiting-status mt-3"><span></span>Đang chờ thanh toán</div>
                    <p class="small text-muted mt-2 mb-0">Còn <strong data-sepay-countdown>--:--</strong></p>
                </div>
                <div class="col-md-7">
                    <div class="bank-transfer-box">
                        @foreach([
                            'Ngân hàng' => $sePay['bank_name'] ?: $sePay['bank_code'],
                            'Chủ tài khoản' => $sePay['account_name'],
                            'Số tài khoản' => $sePay['account_number'],
                            'Số tiền' => number_format((float) $order->total_amount, 0, ',', '.').'₫',
                            'Nội dung' => $order->payment_code,
                        ] as $label => $value)
                            <div class="bank-transfer-row"><span>{{ $label }}</span><strong>{{ $value }}</strong><button type="button" data-copy-value="{{ $value }}" aria-label="Sao chép {{ $label }}"><i class="bi bi-copy"></i></button></div>
                        @endforeach
                    </div>
                    <div class="alert alert-light border mt-3 mb-0 small"><i class="bi bi-exclamation-circle me-1"></i>Vui lòng giữ nguyên <strong>số tiền</strong> và <strong>nội dung chuyển khoản</strong>. Trang sẽ tự cập nhật sau khi ngân hàng ghi nhận.</div>
                </div>
            </div>

            <div class="text-center mt-4"><a class="btn btn-outline-primary" href="{{ route('catalog') }}">Tiếp tục mua sắm</a></div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-sepay-payment]');
    if (!root) return;
    const waiting = root.querySelector('[data-sepay-waiting]');
    const success = root.querySelector('[data-sepay-success]');
    const expired = root.querySelector('[data-sepay-expired]');
    const countdown = root.querySelector('[data-sepay-countdown]');
    const expiresAt = Date.parse(root.dataset.expiresAt || '');
    let finished = !waiting || waiting.hidden;

    const renderCountdown = () => {
        if (!Number.isFinite(expiresAt) || finished) return;
        const seconds = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
        countdown.textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
    };
    const poll = async () => {
        if (finished) return;
        try {
            const response = await fetch(root.dataset.statusUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!response.ok) return;
            const data = await response.json();
            if (data.status === 'paid') {
                finished = true;
                waiting.hidden = true;
                success.classList.remove('d-none');
                window.setTimeout(() => window.location.assign(data.redirect || root.dataset.successUrl), 900);
            } else if (data.status === 'expired') {
                finished = true;
                waiting.hidden = true;
                expired.classList.remove('d-none');
            }
        } catch (_) {}
    };

    renderCountdown();
    window.setInterval(renderCountdown, 1000);
    window.setInterval(poll, 4000);
});
</script>
@endpush
