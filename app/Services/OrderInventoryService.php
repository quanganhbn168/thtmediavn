<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;

class OrderInventoryService
{
    public function recordSold(Order $order): bool
    {
        $order = Order::query()->lockForUpdate()->findOrFail($order->id);
        if ($order->sold_count_recorded_at !== null) {
            return false;
        }

        $quantities = $order->items()
            ->whereNotNull('product_id')
            ->get(['product_id', 'quantity'])
            ->groupBy('product_id')
            ->map(fn ($items): int => (int) $items->sum('quantity'));

        foreach ($quantities as $productId => $quantity) {
            Product::withTrashed()->whereKey($productId)->increment('sold_count', $quantity);
        }

        $order->update(['sold_count_recorded_at' => now()]);

        return true;
    }

    public function release(Order $order): bool
    {
        $order = Order::query()->lockForUpdate()->findOrFail($order->id);
        if ($order->stock_released_at !== null || $order->payment_status === 'paid') {
            return false;
        }

        $items = $order->items()->where('stock_reserved', true)->get();
        foreach ($items as $item) {
            if (! $item->product_variant_id) {
                continue;
            }

            ProductVariant::query()
                ->whereKey($item->product_variant_id)
                ->lockForUpdate()
                ->first()
                ?->increment('stock', $item->quantity);
        }

        $order->update(['stock_released_at' => now()]);

        return true;
    }
}
