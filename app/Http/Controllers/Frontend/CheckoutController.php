<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\SePayExpirationService;
use App\Services\SePayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
        private readonly SePayService $sePay,
        private readonly SePayExpirationService $expiration,
    ) {}

    public function create(): View|RedirectResponse
    {
        $cart = $this->carts->current();
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart')->withErrors(['cart' => 'Giỏ hàng đang trống.']);
        }
        $sePayEnabled = $this->sePay->isEnabled();
        $checkoutToken = (string) Str::uuid();
        session(['checkout_token' => $checkoutToken]);

        return view('frontend.checkout', [
            'cart' => $cart,
            'summary' => $this->carts->summary($cart),
            'addresses' => auth()->user()?->addresses ?? collect(),
            'sePayEnabled' => $sePayEnabled,
            'checkoutToken' => $checkoutToken,
            'provinces' => config('commerce.provinces', []),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $sePayEnabled = $this->sePay->isEnabled();
        $paymentMethods = $sePayEnabled ? ['cod', 'sepay_qr'] : ['cod'];

        $data = $request->validate([
            'checkout_token' => ['required', 'string', 'max:100'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^(?:\+?84|0)(?:[\s.\-]?\d){9,10}$/'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'shipping_province' => ['required', 'string', Rule::in(array_values(config('commerce.provinces', [])))],
            'shipping_district' => ['nullable', 'string', 'max:100'],
            'shipping_ward' => ['required', 'string', 'max:100'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in($paymentMethods)],
            'note' => ['nullable', 'string', 'max:1000'],
            'requires_invoice' => ['nullable', 'boolean'],
            'invoice_company' => ['nullable', 'required_if:requires_invoice,1', 'string', 'max:255'],
            'invoice_tax_code' => ['nullable', 'required_if:requires_invoice,1', 'string', 'max:50', 'regex:/^\d{10}(?:-\d{3})?$/'],
        ]);

        $sessionToken = (string) session('checkout_token', '');
        if ($sessionToken === '' || ! hash_equals($sessionToken, (string) $data['checkout_token'])) {
            throw ValidationException::withMessages(['cart' => 'Phiên thanh toán đã hết hạn. Vui lòng tải lại trang thanh toán.']);
        }
        unset($data['checkout_token']);

        $cart = $this->carts->current();
        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
        }
        if ($cart->items->contains(fn ($item) => $item->isCombo() ? ! $item->combo?->isVisibleOnSite() : ! $item->product?->isVisibleOnSite())) {
            throw ValidationException::withMessages(['cart' => 'Giỏ hàng có sản phẩm không còn khả dụng. Vui lòng gỡ sản phẩm đó trước khi thanh toán.']);
        }

        $order = DB::transaction(function () use ($cart, $data) {
            DB::table('carts')->where('id', $cart->id)->lockForUpdate()->first();
            $cart->refresh();
            $cart->load([
                'items.product.media',
                'items.variant',
                'items.product.variants',
                'items.combo.items.product.variants',
                'items.combo.items.variant',
            ]);
            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Đơn hàng này đã được ghi nhận hoặc giỏ hàng đang trống.']);
            }

            $requiredStock = [];
            $stockLabels = [];
            $comboReservations = [];
            foreach ($cart->items as $item) {
                if ($item->isCombo()) {
                    $reservations = $this->comboReservations($item);
                    if ($reservations === []) {
                        throw ValidationException::withMessages(['cart' => "Combo {$item->combo->name} chưa được cấu hình thành phần."]);
                    }
                    $comboReservations[$item->id] = $reservations;
                    foreach ($reservations as $reservation) {
                        if (! $reservation['stock_reserved']) {
                            continue;
                        }
                        $variantId = $reservation['variant']->id;
                        $requiredStock[$variantId] = ($requiredStock[$variantId] ?? 0) + $reservation['quantity'];
                        $stockLabels[$variantId] = $reservation['product']->name;
                    }
                    continue;
                }

                $variant = $item->variant ?: $item->product->activeVariants()->where('is_active', true)->first();
                if (! $variant) {
                    throw ValidationException::withMessages(['cart' => "Sản phẩm {$item->product->name} chưa có biến thể khả dụng."]);
                }

                if ($item->product->track_inventory && ! $item->product->allow_preorder) {
                    $requiredStock[$variant->id] = ($requiredStock[$variant->id] ?? 0) + $item->quantity;
                    $stockLabels[$variant->id] = $item->product->name;
                }
            }

            $lockedVariants = [];
            foreach ($requiredStock as $variantId => $quantity) {
                $stockModel = ProductVariant::query()->lockForUpdate()->findOrFail($variantId);
                $lockedVariants[$variantId] = $stockModel;
                if ($stockModel->stock < $quantity) {
                    throw ValidationException::withMessages(['cart' => "Sản phẩm {$stockLabels[$variantId]} không còn đủ số lượng."]);
                }
            }

            $summary = $this->carts->summary($cart);
            $coupon = $summary['coupon'];
            $isSePay = $data['payment_method'] === 'sepay_qr';
            $order = Order::create($data + [
                'order_code' => $this->nextOrderCode(),
                'order_type' => 'website',
                'user_id' => auth()->id(),
                'coupon_id' => $coupon?->id,
                'status' => $isSePay ? 'pending_payment' : 'pending',
                'payment_status' => 'unpaid',
                'payment_provider' => $isSePay ? 'sepay' : null,
                'payment_code' => $isSePay ? $this->sePay->paymentCode() : null,
                'payment_public_token' => $isSePay ? $this->sePay->publicToken() : null,
                'payment_expires_at' => $isSePay
                    ? now()->addMinutes((int) config('commerce.sepay.payment_timeout_minutes', 20))
                    : null,
                'stock_reserved_at' => now(),
                'subtotal_amount' => $summary['subtotal'],
                'discount_amount' => $summary['discount'],
                'shipping_amount' => $summary['shipping'],
                'total_amount' => $summary['total'],
                'paid_amount' => 0,
                'remaining_amount' => $summary['total'],
                'currency' => 'VND',
                'requires_invoice' => (bool) ($data['requires_invoice'] ?? false),
            ]);

            foreach ($cart->items as $item) {
                if ($item->isCombo()) {
                    $reservations = $comboReservations[$item->id] ?? [];
                    $stockReserved = collect($reservations)->contains('stock_reserved', true);
                    $orderItem = $order->items()->create([
                        'item_type' => 'combo',
                        'item_id' => $item->combo_id,
                        'item_name' => $item->combo->name,
                        'product_name' => $item->combo->name,
                        'image' => $item->combo->image_url,
                        'quantity' => $item->quantity,
                        'stock_reserved' => $stockReserved,
                        'unit_price' => $item->unit_price,
                        'discount_amount' => 0,
                        'total_price' => $item->unit_price * $item->quantity,
                    ]);
                    foreach ($reservations as $reservation) {
                        $orderItem->comboComponents()->create([
                            'combo_id' => $item->combo_id,
                            'component_product_id' => $reservation['product']->id,
                            'component_variant_id' => $reservation['variant']->id,
                            'component_product_name' => $reservation['product']->name,
                            'component_variant_name' => $reservation['variant']->name,
                            'sku' => $reservation['variant']->sku,
                            'quantity' => $reservation['quantity'],
                            'stock_reserved' => $reservation['stock_reserved'],
                        ]);
                        if ($reservation['stock_reserved']) {
                            $lockedVariants[$reservation['variant']->id]->decrement('stock', $reservation['quantity']);
                        }
                    }
                    continue;
                }

                $variant = $item->variant ?: $item->product->activeVariants()->where('is_active', true)->first();
                if (! $variant) {
                    throw ValidationException::withMessages(['cart' => "Sản phẩm {$item->product->name} chưa có biến thể khả dụng."]);
                }

                $stockReserved = $item->product->track_inventory && ! $item->product->allow_preorder;
                $order->items()->create([
                    'item_type' => 'product',
                    'item_id' => $item->product_id,
                    'item_name' => $item->product->name,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $item->product->name,
                    'variant_name' => $variant?->name,
                    'sku' => $variant?->sku,
                    'image' => $variant?->image ?? $item->product->image_url,
                    'quantity' => $item->quantity,
                    'stock_reserved' => $stockReserved,
                    'unit_price' => $item->unit_price,
                    'discount_amount' => 0,
                    'total_price' => $item->unit_price * $item->quantity,
                ]);
                if ($stockReserved) {
                    $lockedVariants[$variant->id]->decrement('stock', $item->quantity);
                }
            }

            $order->statusHistories()->create([
                'to_status' => $order->status,
                'note' => $order->payment_method === 'sepay_qr'
                    ? 'Khách hàng tạo đơn; tồn kho đã được giữ trong khi chờ thanh toán SePay.'
                    : 'Khách hàng tạo đơn, đang chờ cửa hàng xác nhận.',
            ]);
            if ($coupon) {
                $coupon->increment('used_count');
                DB::table('coupon_usages')->insert(['coupon_id' => $coupon->id, 'order_id' => $order->id, 'user_id' => auth()->id(), 'discount_amount' => $summary['discount'], 'created_at' => now(), 'updated_at' => now()]);
            }
            $cart->items()->delete();
            $cart->update(['coupon_code' => null]);

            return $order;
        });

        session()->forget('checkout_token');
        session(['recent_order_id' => $order->id]);
        $redirect = $order->payment_method === 'sepay_qr'
            ? route('checkout.payment', $order->payment_public_token)
            : route('checkout.success', $order->order_code);
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $order->payment_method === 'sepay_qr'
                    ? 'Đã tạo đơn. Vui lòng quét QR để thanh toán.'
                    : 'Đặt hàng thành công.',
                'order_code' => $order->order_code,
                'redirect' => $redirect,
            ], 201);
        }

        return redirect()->to($redirect);
    }

    /**
     * @return array<int, array{product: Product, variant: ProductVariant, quantity: int, stock_reserved: bool}>
     */
    private function comboReservations(CartItem $item): array
    {
        $reservations = [];

        foreach ($item->combo->items as $comboItem) {
            $component = $comboItem->product;
            $variant = $comboItem->variant ?: $component?->default_variant;
            if (! $component || ! $component->isVisibleOnSite() || ! $variant || ! $variant->is_active) {
                throw ValidationException::withMessages([
                    'cart' => "Combo {$item->combo->name} có thành phần không còn khả dụng.",
                ]);
            }

            $reservations[] = [
                'product' => $component,
                'variant' => $variant,
                'quantity' => $item->quantity * max(1, (int) $comboItem->quantity),
                'stock_reserved' => $component->track_inventory && ! $component->allow_preorder,
            ];
        }

        return $reservations;
    }

    public function payment(string $publicToken): View
    {
        $order = Order::query()
            ->where('payment_public_token', $publicToken)
            ->where('payment_provider', 'sepay')
            ->with('items')
            ->firstOrFail();
        $this->expiration->expire($order);
        $order->refresh();

        return view('frontend.checkout.payment', [
            'order' => $order,
            'sePay' => config('commerce.sepay', []),
            'qrUrl' => $this->sePay->qrUrl($order),
        ]);
    }

    public function paymentStatus(string $publicToken): JsonResponse
    {
        $order = Order::query()
            ->where('payment_public_token', $publicToken)
            ->where('payment_provider', 'sepay')
            ->firstOrFail();
        $this->expiration->expire($order);
        $order->refresh();

        $state = match (true) {
            $order->payment_status === 'paid' => 'paid',
            $order->status === 'payment_expired' => 'expired',
            default => 'waiting',
        };

        return response()->json([
            'status' => $state,
            'payment_status' => $order->payment_status,
            'paid_at' => $order->paid_at?->toIso8601String(),
            'expires_at' => $order->payment_expires_at?->toIso8601String(),
            'redirect' => $state === 'paid'
                ? route('checkout.success', ['code' => $order->order_code, 'token' => $order->payment_public_token])
                : null,
        ]);
    }

    public function success(Request $request, string $code): View
    {
        $order = Order::query()->where('order_code', $code)->with('items')->firstOrFail();
        $publicToken = (string) $request->query('token', '');
        $hasPublicToken = $publicToken !== ''
            && filled($order->payment_public_token)
            && hash_equals((string) $order->payment_public_token, $publicToken);
        abort_unless(
            session('recent_order_id') === $order->id
                || (auth()->check() && $order->user_id === auth()->id())
                || $hasPublicToken,
            403,
        );

        return view('frontend.checkout.success', [
            'order' => $order,
        ]);
    }

    private function nextOrderCode(): string
    {
        return 'THT-'.now()->format('ymd').'-'.Str::upper(substr((string) Str::ulid(), -6));
    }
}
