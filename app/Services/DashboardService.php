<?php

namespace App\Services;

use App\Models\Order;
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
            'monthlyRevenue' => (float) (clone $orders)->where('status', 'completed')->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total_amount'),
            'pendingReviews' => Review::where('status', 'pending')->count(),
            'recentOrders' => Order::latest()->take(8)->get(),
        ];
    }
}
