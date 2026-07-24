<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $carts) {}

    public function create(): View|RedirectResponse
    {
        $cart = $this->carts->current();
        if ($cart->items->isEmpty()) return redirect()->route('cart')->withErrors(['cart' => 'Giỏ hàng đang trống.']);
        return view('frontend.checkout', ['cart' => $cart, 'summary' => $this->carts->summary($cart), 'addresses' => auth()->user()?->addresses ?? collect()]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'shipping_province' => ['required', 'string', 'max:100'],
            'shipping_district' => ['nullable', 'string', 'max:100'],
            'shipping_ward' => ['nullable', 'string', 'max:100'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cod,bank_transfer'],
            'note' => ['nullable', 'string', 'max:1000'],
            'requires_invoice' => ['nullable', 'boolean'],
            'invoice_company' => ['nullable', 'required_if:requires_invoice,1', 'string', 'max:255'],
            'invoice_tax_code' => ['nullable', 'required_if:requires_invoice,1', 'string', 'max:50'],
        ]);

        $cart = $this->carts->current();
        if ($cart->items->isEmpty()) throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
        if ($cart->items->contains(fn ($item) => ! $item->product?->isVisibleOnSite())) {
            throw ValidationException::withMessages(['cart' => 'Giỏ hàng có sản phẩm không còn khả dụng. Vui lòng gỡ sản phẩm đó trước khi thanh toán.']);
        }

        $order = DB::transaction(function () use ($cart, $data) {
            $cart->load(['items.product.media', 'items.variant', 'items.product.variants']);
            foreach ($cart->items as $item) {
                $variant = $item->variant ?: $item->product->activeVariants()->where('is_active', true)->first();
                if (! $variant) {
                    throw ValidationException::withMessages(['cart' => "Sản phẩm {$item->product->name} chưa có biến thể khả dụng."]);
                }

                $stockModel = ProductVariant::query()->lockForUpdate()->findOrFail($variant->id);
                if ($item->product->track_inventory && ! $item->product->allow_preorder && $stockModel->stock < $item->quantity) {
                    throw ValidationException::withMessages(['cart' => "Sản phẩm {$item->product->name} không còn đủ số lượng."]);
                }
            }

            $summary = $this->carts->summary($cart);
            $coupon = $summary['coupon'];
            $order = Order::create($data + [
                'order_code' => $this->nextOrderCode(),
                'order_type' => 'website',
                'user_id' => auth()->id(),
                'coupon_id' => $coupon?->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'subtotal_amount' => $summary['subtotal'],
                'discount_amount' => $summary['discount'],
                'shipping_amount' => $summary['shipping'],
                'total_amount' => $summary['total'],
                'currency' => 'VND',
                'requires_invoice' => (bool) ($data['requires_invoice'] ?? false),
            ]);

            foreach ($cart->items as $item) {
                $variant = $item->variant ?: $item->product->activeVariants()->where('is_active', true)->first();
                if (! $variant) {
                    throw ValidationException::withMessages(['cart' => "Sản phẩm {$item->product->name} chưa có biến thể khả dụng."]);
                }

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
                    'unit_price' => $item->unit_price,
                    'discount_amount' => 0,
                    'total_price' => $item->unit_price * $item->quantity,
                ]);
                if ($item->product->track_inventory && ! $item->product->allow_preorder) {
                    $variant->decrement('stock', $item->quantity);
                }
                $item->product->increment('sold_count', $item->quantity);
            }

            $order->statusHistories()->create(['to_status' => 'pending', 'note' => 'Khách hàng tạo đơn hàng.']);
            if ($coupon) {
                $coupon->increment('used_count');
                DB::table('coupon_usages')->insert(['coupon_id' => $coupon->id, 'order_id' => $order->id, 'user_id' => auth()->id(), 'discount_amount' => $summary['discount'], 'created_at' => now(), 'updated_at' => now()]);
            }
            $cart->items()->delete();
            $cart->update(['coupon_code' => null]);

            return $order;
        });

        session(['recent_order_id' => $order->id]);
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Đặt hàng thành công.',
                'order_code' => $order->order_code,
                'redirect' => route('checkout.success', $order->order_code),
            ], 201);
        }
        return redirect()->route('checkout.success', $order->order_code);
    }

    public function success(string $code): View
    {
        $order = Order::query()->where('order_code', $code)->with('items')->firstOrFail();
        abort_unless(session('recent_order_id') === $order->id || (auth()->check() && $order->user_id === auth()->id()), 403);
        return view('frontend.checkout.success', compact('order'));
    }

    private function nextOrderCode(): string
    {
        return 'DH-'.now()->format('ymd').'-'.str_pad((string) (Order::withTrashed()->whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
    }
}
