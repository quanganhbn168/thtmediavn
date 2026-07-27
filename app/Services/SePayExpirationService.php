<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class SePayExpirationService
{
    public function __construct(private readonly OrderInventoryService $inventory) {}

    public function expireDue(int $limit = 500): int
    {
        $expired = 0;

        Order::query()
            ->where('payment_provider', 'sepay')
            ->where('status', 'pending_payment')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNull('stock_released_at')
            ->whereNotNull('payment_expires_at')
            ->where('payment_expires_at', '<=', now())
            ->orderBy('id')
            ->limit($limit)
            ->pluck('id')
            ->each(function (int $orderId) use (&$expired): void {
                if ($this->expire(Order::findOrFail($orderId))) {
                    $expired++;
                }
            });

        return $expired;
    }

    public function expire(Order $order): bool
    {
        return DB::transaction(function () use ($order): bool {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($order->status !== 'pending_payment'
                || $order->payment_status === 'paid'
                || $order->stock_released_at !== null
                || $order->payment_expires_at === null
                || $order->payment_expires_at->isFuture()) {
                return false;
            }

            $this->inventory->release($order);
            $order->update(['status' => 'payment_expired']);
            $order->statusHistories()->create([
                'from_status' => 'pending_payment',
                'to_status' => 'payment_expired',
                'note' => 'Phiên thanh toán SePay hết hạn; hệ thống đã hoàn tồn kho.',
            ]);

            return true;
        });
    }
}
