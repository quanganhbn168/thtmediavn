<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\IndexOrderRequest;
use App\Http\Requests\Admin\Order\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(IndexOrderRequest $request): View
    {
        return view('admin.orders.index', [
            'orders' => $this->orderService->paginate($request->validated()),
            'statuses' => OrderService::STATUSES,
            'paymentStatuses' => OrderService::PAYMENT_STATUSES,
        ]);
    }

    public function edit(Order $order): View
    {
        return view('admin.orders.edit', $this->orderService->getFormContext($order));
    }

    public function update(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->update($order, $request->validated());

        return back()->with('success', 'Đã cập nhật đơn hàng.');
    }

    public function destroy(Order $order):RedirectResponse
    {
        $this->orderService->delete($order);

        return redirect()->route('admin.orders.index')->with('success','Đã đưa đơn hàng vào thùng rác.');
    }
}
