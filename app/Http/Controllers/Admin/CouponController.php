<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexCouponRequest;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function __construct(private readonly CouponService $couponService) {}

    public function index(IndexCouponRequest $request): View
    {
        return view('admin.coupons.index', [
            'coupons' => $this->couponService->paginate($request->validated()),
        ]);
    }

    public function create(): View
    {
        return $this->editor(new Coupon);
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $coupon = $this->couponService->create($request->validated());

        return redirect()
            ->route('admin.coupons.edit', $coupon)
            ->with('success', 'Đã tạo mã giảm giá.');
    }

    public function edit(Coupon $coupon): View
    {
        return $this->editor($coupon->load(['products', 'categories']));
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $this->couponService->update($coupon, $request->validated());

        return back()->with('success', 'Đã cập nhật mã giảm giá.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $this->couponService->delete($coupon);

        return back()->with('success', 'Đã xóa mã giảm giá.');
    }

    private function editor(Coupon $coupon): View
    {
        return view('admin.coupons.coupon', $this->couponService->editorContext($coupon));
    }
}
