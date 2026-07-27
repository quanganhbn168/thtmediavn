<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class DashboardService
{
    public function getDashboardStats(User $actor): array
    {
        $orders = Order::query();

        return [
            'totalProducts' => Product::where('is_active', true)->count(),
            'lowStockProducts' => Product::where('track_inventory', true)->whereHas('activeVariants', fn ($q) => $q->where('stock', '<=', 5))->count(),
            'totalOrders' => $orders->count(),
            'pendingOrders' => (clone $orders)->where('status', 'pending')->count(),
            'todayOrders' => (clone $orders)->whereDate('created_at', today())->count(),
            'pendingPaymentOrders' => (clone $orders)->where('status', 'pending_payment')->count(),
            'processingOrders' => (clone $orders)->whereIn('status', ['processing', 'shipping'])->count(),
            'todayCollected' => (float) Payment::query()->where('status', 'completed')->whereDate('payment_date', today())->sum('amount'),
            'monthlyRevenue' => (float) Payment::query()->where('status', 'completed')->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
            'unmatchedTransactions' => PaymentTransaction::query()->whereIn('match_status', ['unmatched', 'amount_mismatch', 'late'])->count(),
            'pendingReviews' => Review::where('status', 'pending')->count(),
            'recentOrders' => Order::latest()->take(8)->get(),
            'recentTransactions' => PaymentTransaction::query()->with('order:id,order_code')->latest('transaction_at')->take(8)->get(),
        ];
    }
}
