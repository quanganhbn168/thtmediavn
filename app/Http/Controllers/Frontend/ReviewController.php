<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['rating' => ['required', 'integer', 'between:1,5'], 'content' => ['required', 'string', 'min:10', 'max:2000']]);
        $orderItem = OrderItem::query()->where('product_id', $product->id)->whereHas('order', fn ($query) => $query->where('user_id', auth()->id())->where('status', 'completed'))->latest()->first();
        $product->reviews()->create($data + [
            'reviewable_type' => Product::class,
            'reviewable_id' => $product->id,
            'user_id' => auth()->id(),
            'order_item_id' => $orderItem?->id,
            'name' => auth()->user()->name,
            'is_verified' => (bool) $orderItem,
            'status' => 'pending',
        ]);
        return back()->with('success', 'Cảm ơn anh/chị. Đánh giá đang chờ duyệt.');
    }
}
