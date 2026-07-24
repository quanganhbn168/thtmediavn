<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View { return view('frontend.account.index', ['orders' => auth()->user()->orders()->latest()->take(5)->get()]); }
    public function orders(): View { return view('frontend.account.orders', ['orders' => auth()->user()->orders()->latest()->paginate(10)]); }
    public function order(Order $order): View { abort_unless($order->user_id === auth()->id(), 403); return view('frontend.account.order', ['order' => $order->load(['items', 'statusHistories'])]); }
    public function profile(): View { return view('frontend.account.profile', ['user' => auth()->user(), 'addresses' => auth()->user()->addresses]); }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', Rule::unique('users')->ignore(auth()->id())], 'phone' => ['nullable', 'string', 'max:30']]);
        auth()->user()->update($data);
        return back()->with('success', 'Đã cập nhật thông tin tài khoản.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'phone' => ['required', 'string', 'max:30'], 'province' => ['required', 'string', 'max:100'], 'district' => ['nullable', 'string', 'max:100'], 'ward' => ['nullable', 'string', 'max:100'], 'address' => ['required', 'string', 'max:255'], 'is_default' => ['nullable', 'boolean']]);
        if ($data['is_default'] ?? false) auth()->user()->addresses()->update(['is_default' => false]);
        auth()->user()->addresses()->create($data);
        return back()->with('success', 'Đã thêm địa chỉ giao hàng.');
    }

    public function destroyAddress(UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === auth()->id(), 403);
        $address->delete();
        return back()->with('success', 'Đã xóa địa chỉ.');
    }
}
