<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class CartService
{
    public function current(bool $create = true): ?Cart
    {
        $query = Cart::query();
        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->whereNull('user_id')->where('session_id', $this->guestToken());
        }

        $cart = $query->first();
        if (! $cart && $create) {
            $cart = Cart::create(['user_id' => Auth::id(), 'session_id' => Auth::check() ? null : $this->guestToken()]);
        }

        return $cart?->load(['items.product.brand', 'items.product.media', 'items.product.flashSaleItems.flashSale', 'items.variant.values.option']);
    }

    public function add(Product $product, ?ProductVariant $variant, int $quantity): Cart
    {
        if (! $product->isVisibleOnSite()) {
            throw ValidationException::withMessages(['product_id' => 'Sản phẩm này không khả dụng trong chế độ cửa hàng hiện tại.']);
        }
        if ($variant && $variant->product_id !== $product->id) {
            throw ValidationException::withMessages(['variant_id' => 'Phân loại sản phẩm không hợp lệ.']);
        }
        if (! $variant) {
            $variant = $product->default_variant;
        }
        if (! $variant) {
            throw ValidationException::withMessages(['product_id' => 'Sản phẩm chưa có biến thể để thêm vào giỏ.']);
        }
        if (! $variant->is_active) {
            throw ValidationException::withMessages(['variant_id' => 'Biến thể này hiện đang ngừng kinh doanh.']);
        }

        $stock = (int) $variant->stock;
        if ($product->track_inventory && ! $product->allow_preorder && $stock < 1) {
            throw ValidationException::withMessages(['quantity' => 'Sản phẩm hiện đã hết hàng.']);
        }

        $cart = $this->current();
        $item = $cart->items()->firstOrNew(['product_id' => $product->id, 'product_variant_id' => $variant?->id]);
        $newQuantity = ($item->exists ? $item->quantity : 0) + max(1, $quantity);
        if ($product->track_inventory && ! $product->allow_preorder && $newQuantity > $stock) {
            throw ValidationException::withMessages(['quantity' => "Chỉ còn {$stock} sản phẩm trong kho."]);
        }
        $item->quantity = $newQuantity;
        $item->save();

        return $this->current();
    }

    public function update(int $itemId, int $quantity): Cart
    {
        $cart = $this->current();
        $item = $cart->items->firstWhere('id', $itemId);
        abort_unless($item, 404);
        $variant = $item->variant ?: $item->product->default_variant;
        if (! $item->product->isVisibleOnSite()) {
            throw ValidationException::withMessages(['item_id' => 'Sản phẩm không còn khả dụng. Vui lòng gỡ khỏi giỏ hàng.']);
        }
        if (! $variant) {
            throw ValidationException::withMessages(['item_id' => 'Không xác định được biến thể cho sản phẩm.']);
        }

        if ($quantity < 1) {
            $item->delete();
            return $this->current();
        }
        $stock = (int) $variant->stock;
        if ($item->product->track_inventory && ! $item->product->allow_preorder && $quantity > $stock) {
            throw ValidationException::withMessages(['quantity' => "Chỉ còn {$stock} sản phẩm trong kho."]);
        }
        $item->update(['quantity' => $quantity]);
        return $this->current();
    }

    public function remove(int $itemId): Cart
    {
        $cart = $this->current();
        $cart->items()->whereKey($itemId)->delete();
        return $this->current();
    }

    public function applyCoupon(string $code): Cart
    {
        $cart = $this->current();
        $summary = $this->summary($cart, false);
        $coupon = Coupon::query()->visibleOnSite()->with(['products:id', 'categories:id'])->whereRaw('UPPER(code) = ?', [mb_strtoupper(trim($code))])->first();
        if (! $coupon || ! $coupon->isAvailable($summary['subtotal'], auth()->id()) || $this->eligibleSubtotal($cart, $coupon) <= 0) {
            throw ValidationException::withMessages(['coupon' => 'Mã giảm giá không hợp lệ hoặc chưa đủ điều kiện áp dụng.']);
        }
        $cart->update(['coupon_code' => $coupon->code]);
        return $this->current();
    }

    public function summary(?Cart $cart = null, bool $withCoupon = true): array
    {
        $cart ??= $this->current();
        $unavailableItems = $cart->items->filter(fn ($item) => ! $item->product?->isVisibleOnSite());
        $availableItems = $cart->items->reject(fn ($item) => $unavailableItems->contains('id', $item->id));
        $subtotal = $availableItems->sum(fn ($item) => $item->unit_price * $item->quantity);
        $coupon = $withCoupon && $cart->coupon_code ? Coupon::query()->visibleOnSite()->with(['products:id', 'categories:id'])->where('code', $cart->coupon_code)->first() : null;
        if ($coupon && (! $coupon->isAvailable($subtotal, auth()->id()) || $this->eligibleSubtotal($cart, $coupon) <= 0)) {
            $cart->update(['coupon_code' => null]);
            $coupon = null;
        }
        $eligibleSubtotal = $coupon ? $this->eligibleSubtotal($cart, $coupon) : $subtotal;
        $discount = $coupon?->discountFor($eligibleSubtotal) ?? 0;
        $shipping = $subtotal <= 0 || $subtotal >= 1000000 || $coupon?->type === 'free_shipping' ? 0 : 30000;

        return compact('subtotal', 'discount', 'shipping', 'coupon', 'unavailableItems') + ['total' => max(0, $subtotal - $discount + $shipping)];
    }

    private function eligibleSubtotal(Cart $cart, Coupon $coupon): float
    {
        $productIds = $coupon->products->pluck('id');
        $categoryIds = $coupon->categories->pluck('id');
        $visibleItems = $cart->items->filter(fn ($item) => $item->product?->isVisibleOnSite());
        if ($productIds->isEmpty() && $categoryIds->isEmpty()) return (float) $visibleItems->sum(fn ($item) => $item->unit_price * $item->quantity);
        return (float) $visibleItems->filter(fn ($item) => $productIds->contains($item->product_id) || $categoryIds->contains($item->product->product_category_id))->sum(fn ($item) => $item->unit_price * $item->quantity);
    }

    public function mergeGuestCart(User $user, string $sessionId): void
    {
        $guest = Cart::query()->whereNull('user_id')->where('session_id', $sessionId)->with('items')->first();
        if (! $guest) return;
        $userCart = Cart::firstOrCreate(['user_id' => $user->id], ['session_id' => null]);
        foreach ($guest->items as $item) {
            $target = $userCart->items()->firstOrNew(['product_id' => $item->product_id, 'product_variant_id' => $item->product_variant_id]);
            $target->quantity = ($target->exists ? $target->quantity : 0) + $item->quantity;
            $target->save();
        }
        if (! $userCart->coupon_code) $userCart->coupon_code = $guest->coupon_code;
        $userCart->save();
        $guest->delete();
    }

    public function guestToken(): string
    {
        if (! session()->has('cart_token')) session()->put('cart_token', (string) Str::uuid());
        return (string) session('cart_token');
    }
}
