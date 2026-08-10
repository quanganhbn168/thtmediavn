@extends('layouts.master')

@section('title', 'Thanh toán — '.$website['name'])

@section('content')
<div class="breadcrumb-wrap"><div class="container"><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('cart') }}">Giỏ hàng</a></li><li class="breadcrumb-item active">Thanh toán</li></ol></nav></div></div>

<section class="section-space checkout-page">
    <div class="container-xl">
        <div class="alert alert-critical {{ $errors->any() ? '' : 'd-none' }}" data-checkout-errors>{{ $errors->first() }}</div>

        <form action="{{ route('checkout.store') }}" method="post" data-checkout-form>
            @csrf
            <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
            <input type="hidden" name="shipping_district" value="{{ old('shipping_district') }}">

            <div class="row g-4 align-items-start">
                <div class="col-lg-7 checkout-main-column">
                    @if($addresses->isNotEmpty())
                        <div class="content-card checkout-section-card mb-3">
                            <label class="form-label fw-bold" for="saved-address">Địa chỉ đã lưu</label>
                            <select class="form-select" id="saved-address" data-saved-address>
                                <option value="">Nhập địa chỉ khác</option>
                                @foreach($addresses as $address)
                                    <option value="{{ $address->id }}" data-address="{{ json_encode(['name' => $address->name, 'phone' => $address->phone, 'province' => $address->province, 'district' => $address->district, 'ward' => $address->ward, 'address' => $address->address]) }}">{{ $address->address }}, {{ $address->ward }}, {{ $address->province }}{{ $address->is_default ? ' — Mặc định' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="content-card checkout-section-card mb-3">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <h2 class="h5 fw-bold mb-0">1. Thông tin người nhận</h2>
                            @guest<a class="small fw-semibold" href="{{ route('login', ['redirect' => route('checkout', [], false)]) }}"><i class="bi bi-person-circle me-1"></i>Đăng nhập</a>@endguest
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label" for="customer-name">Họ và tên *</label><input class="form-control" id="customer-name" name="customer_name" autocomplete="name" value="{{ old('customer_name', auth()->user()?->name) }}" required></div>
                            <div class="col-md-6"><label class="form-label" for="customer-phone">Số điện thoại *</label><input class="form-control" id="customer-phone" name="customer_phone" type="tel" inputmode="tel" autocomplete="tel" pattern="(?:\+?84|0)(?:[ .-]?\d){9,10}" value="{{ old('customer_phone', auth()->user()?->phone) }}" required><div class="form-text">Ví dụ: 0901234567 hoặc +84901234567</div></div>
                            <div class="col-12"><label class="form-label" for="customer-email">Email <span class="text-muted fw-normal">(không bắt buộc)</span></label><input class="form-control" id="customer-email" type="email" name="customer_email" autocomplete="email" value="{{ old('customer_email', auth()->user()?->email) }}"></div>
                        </div>
                    </div>

                    <div class="content-card checkout-section-card mb-3">
                        <h2 class="h5 fw-bold mb-3">2. Địa chỉ giao hàng</h2>
                        <p class="small text-muted">Danh mục hành chính hiện hành gồm tỉnh/thành phố và phường/xã.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="shipping-province">Tỉnh/Thành phố *</label>
                                <select class="form-select" id="shipping-province" name="shipping_province" data-shipping-province required>
                                    <option value="">Chọn tỉnh/thành phố</option>
                                    @foreach($provinces as $code => $name)
                                        <option value="{{ $name }}" data-code="{{ $code }}" @selected(old('shipping_province') === $name)>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="shipping-ward">Phường/Xã *</label>
                                <select class="form-select" id="shipping-ward" name="shipping_ward" data-shipping-ward data-selected="{{ old('shipping_ward') }}" required disabled>
                                    <option value="">Chọn tỉnh/thành trước</option>
                                </select>
                            </div>
                            <div class="col-12"><label class="form-label" for="shipping-address">Số nhà, tên đường, tòa nhà *</label><input class="form-control" id="shipping-address" name="shipping_address" autocomplete="street-address" value="{{ old('shipping_address') }}" required></div>
                        </div>
                    </div>

                    <div class="content-card checkout-section-card mb-3">
                        <h2 class="h5 fw-bold mb-3">3. Phương thức thanh toán</h2>
                        <label class="checkout-payment-option mb-2" for="cod"><input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" @checked(old('payment_method', 'cod') === 'cod')><span><strong>Thanh toán khi nhận hàng (COD)</strong><small>THT MEDIA VN sẽ liên hệ xác nhận trước khi giao.</small></span></label>
                        <label @class(['checkout-payment-option', 'is-disabled' => ! $sePayEnabled]) for="sepay"><input class="form-check-input" type="radio" name="payment_method" id="sepay" value="sepay_qr" @checked(old('payment_method') === 'sepay_qr') @disabled(! $sePayEnabled)><span><strong>QR ngân hàng qua SePay</strong><small>{{ $sePayEnabled ? 'Quét VietQR ngay trên website; hệ thống tự xác nhận khi tiền về.' : 'Đang tạm ẩn vì cửa hàng chưa hoàn tất cấu hình SePay.' }}</small></span></label>
                    </div>

                    <div class="content-card checkout-section-card mb-3">
                        <h2 class="h5 fw-bold mb-3">4. Thông tin bổ sung</h2>
                        <details class="checkout-note"><summary>Thêm ghi chú cho đơn hàng</summary><textarea class="form-control mt-3" name="note" rows="3" maxlength="1000" placeholder="Thời gian nhận hàng, lưu ý khi giao…">{{ old('note') }}</textarea></details>
                        <hr>
                        <div class="form-check form-switch"><input class="form-check-input" id="requires-invoice" name="requires_invoice" value="1" type="checkbox" data-invoice-toggle @checked(old('requires_invoice'))><label class="form-check-label fw-semibold" for="requires-invoice">Tôi cần xuất hóa đơn</label></div>
                        <div class="row g-3 mt-1 d-none" data-invoice-fields>
                            <div class="col-md-7"><label class="form-label" for="invoice-company">Tên công ty *</label><input class="form-control" id="invoice-company" name="invoice_company" value="{{ old('invoice_company') }}"></div>
                            <div class="col-md-5"><label class="form-label" for="invoice-tax-code">Mã số thuế *</label><input class="form-control" id="invoice-tax-code" name="invoice_tax_code" inputmode="numeric" pattern="\d{10}(?:-\d{3})?" value="{{ old('invoice_tax_code') }}"></div>
                        </div>
                    </div>
                </div>

                <aside class="col-lg-5 checkout-order-column">
                    <div class="cart-summary sticky-lg-top checkout-summary">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-3"><h2 class="h5 fw-bold mb-0">Đơn hàng của bạn</h2><a class="small fw-semibold" href="{{ route('cart') }}"><i class="bi bi-pencil me-1"></i>Sửa giỏ hàng</a></div>
                        @foreach($cart->items as $item)
                            <div class="checkout-product-row"><img class="cart-product-img" src="{{ $item->isCombo() ? $item->combo->image_url : ($item->variant?->image ?: $item->product->image_url) }}" alt="" width="80" height="80"><div class="small flex-grow-1"><strong>{{ $item->isCombo() ? $item->combo->name : $item->product->name }}</strong>@if($item->isCombo())<div class="text-primary mt-1">Combo</div>@elseif($item->variant)<div class="text-primary mt-1">{{ $item->variant->values->map(fn ($value) => ($value->option?->name ? $value->option->name.': ' : '').$value->value)->join(' · ') ?: $item->variant->name }}</div>@endif<div class="text-muted">Số lượng: {{ $item->quantity }}</div></div><strong class="small">{{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}₫</strong></div>
                        @endforeach
                        <div class="d-flex justify-content-between mt-3"><span>Tạm tính</span><strong>{{ number_format($summary['subtotal'], 0, ',', '.') }}₫</strong></div>
                        <div class="d-flex justify-content-between mt-2"><span>Giảm giá</span><span>-{{ number_format($summary['discount'], 0, ',', '.') }}₫</span></div>
                        <div class="d-flex justify-content-between mt-2"><span>Vận chuyển</span><span>{{ $summary['shipping'] ? number_format($summary['shipping'], 0, ',', '.').'₫' : 'Miễn phí' }}</span></div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5"><strong>Tổng cộng</strong><strong class="text-primary">{{ number_format($summary['total'], 0, ',', '.') }}₫</strong></div>
                        <button class="btn btn-primary w-100 py-3 mt-3" type="submit" data-checkout-submit>Đặt hàng</button>
                        <p class="small text-muted text-center mt-2 mb-0">Khi đặt hàng, anh/chị đồng ý với <a href="{{ route('policies.purchase') }}" target="_blank">chính sách mua hàng</a> và <a href="{{ route('policies.privacy') }}" target="_blank">chính sách bảo mật</a>.</p>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</section>
@endsection
