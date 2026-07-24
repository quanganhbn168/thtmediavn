<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $carts) {}

    public function index(): View
    {
        $cart = $this->carts->current();
        return view('frontend.cart', ['cart' => $cart, 'summary' => $this->carts->summary($cart)]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        if (empty($data['product_id']) && empty($data['variant_id'])) {
            throw ValidationException::withMessages(['product_id' => 'Thiếu thông tin sản phẩm hoặc biến thể.']);
        }

        if (! empty($data['variant_id'])) {
            $variant = ProductVariant::query()->findOrFail((int) $data['variant_id']);
            if (! $variant->is_active) {
                throw ValidationException::withMessages(['variant_id' => 'Biến thể này hiện không còn hoạt động.']);
            }

            $product = Product::query()->where('is_active', true)->visibleOnSite()->findOrFail($variant->product_id);
            if (! empty($data['product_id']) && (int) $data['product_id'] !== (int) $product->id) {
                throw ValidationException::withMessages(['variant_id' => 'Biến thể không khớp với sản phẩm được chọn.']);
            }
            $data['product_id'] = $product->id;
        } else {
            $product = Product::query()->where('is_active', true)->visibleOnSite()->findOrFail((int) $data['product_id']);
            $activeVariantCount = $product->activeVariants()->count();
            $defaultVariant = $product->variants()
                ->where('is_active', true)
                ->where('is_default', true)
                ->first()
                ?: $product->variants()->where('is_active', true)->first()
                ?: $product->variants()->first();
            if (! $defaultVariant) {
                throw ValidationException::withMessages(['product_id' => 'Sản phẩm chưa có biến thể nào.']);
            }
            if ($activeVariantCount > 1) {
                throw ValidationException::withMessages(['variant_id' => 'Vui lòng chọn biến thể cho sản phẩm.']);
            }

            $variant = $defaultVariant;
            if ($variant) {
                $data['variant_id'] = $variant->id;
            }
        }

        if ($variant && ! $product->variants()->where('is_active', true)->whereKey($variant->id)->exists()) {
            throw ValidationException::withMessages(['variant_id' => 'Biến thể không phù hợp với sản phẩm.']);
        }

        $cart = $this->carts->add($product, $variant, (int) ($data['quantity'] ?? 1));
        $summary = $this->carts->summary($cart);
        if ($request->expectsJson()) return response()->json(['message' => 'Đã thêm sản phẩm vào giỏ hàng.', 'count' => $cart->items->sum('quantity'), 'total' => $summary['total']]);
        return back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng.');
    }

    public function update(Request $request, CartItem $item): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:99']]);
        $cart = $this->carts->update($item->id, (int) $data['quantity']);
        if ($request->expectsJson()) return response()->json(['message' => 'Đã cập nhật giỏ hàng.', 'count' => $cart->items->sum('quantity'), 'summary' => $this->carts->summary($cart)]);
        return back()->with('success', 'Đã cập nhật giỏ hàng.');
    }

    public function destroy(CartItem $item): JsonResponse|RedirectResponse
    {
        $cart = $this->carts->remove($item->id);
        if (request()->expectsJson()) return response()->json(['message' => 'Đã xóa sản phẩm.', 'count' => $cart->items->sum('quantity'), 'summary' => $this->carts->summary($cart)]);
        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    public function coupon(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['coupon' => ['required', 'string', 'max:60']]);
        $cart = $this->carts->applyCoupon($data['coupon']);
        if ($request->expectsJson()) return response()->json(['message' => 'Đã áp dụng mã giảm giá.', 'count' => $cart->items->sum('quantity'), 'summary' => $this->carts->summary($cart)]);
        return back()->with('success', 'Đã áp dụng mã giảm giá.');
    }

    public function removeCoupon(): JsonResponse|RedirectResponse
    {
        $cart = $this->carts->current();
        $cart->update(['coupon_code' => null]);
        $cart = $this->carts->current();
        if (request()->expectsJson()) return response()->json(['message' => 'Đã gỡ mã giảm giá.', 'count' => $cart->items->sum('quantity'), 'summary' => $this->carts->summary($cart)]);
        return back()->with('success', 'Đã gỡ mã giảm giá.');
    }
}
