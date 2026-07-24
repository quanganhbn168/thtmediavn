<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WishlistController extends FrontendController
{
    public function index(): View
    {
        $productIds = DB::table('wishlists')->where('user_id', auth()->id())->pluck('product_id');
        $products = Product::query()->whereIn('id', $productIds)->where('is_active', true)->visibleOnSite()->with($this->productRelations())->get();
        $presented = $products->map(fn (Product $product) => $this->presentProduct($product));
        return view('frontend.wishlist', ['products' => $presented]);
    }

    public function toggle(Product $product): JsonResponse|RedirectResponse
    {
        abort_unless($product->isVisibleOnSite(), 404);
        $existing = DB::table('wishlists')->where('user_id', auth()->id())->where('product_id', $product->id);
        $active = ! $existing->exists();
        if ($active) DB::table('wishlists')->insert(['user_id' => auth()->id(), 'product_id' => $product->id, 'created_at' => now(), 'updated_at' => now()]);
        else $existing->delete();
        $count = DB::table('wishlists')->where('user_id', auth()->id())->count();
        if (request()->expectsJson()) return response()->json(['active' => $active, 'count' => $count]);
        return back()->with('success', $active ? 'Đã thêm vào yêu thích.' : 'Đã bỏ khỏi yêu thích.');
    }
}
