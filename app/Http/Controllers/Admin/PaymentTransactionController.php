<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\PaymentTransactionAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(array_keys(PaymentTransaction::MATCH_STATUSES))],
        ]);
        $query = PaymentTransaction::query()->with('order:id,order_code');
        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(fn ($builder) => $builder
                ->where('transaction_content', 'like', "%{$search}%")
                ->orWhere('payment_code', 'like', "%{$search}%")
                ->orWhere('reference_code', 'like', "%{$search}%")
                ->orWhere('provider_transaction_id', 'like', "%{$search}%"));
        }
        if ($status = $filters['status'] ?? null) {
            $query->where('match_status', $status);
        }

        return view('admin.payment_transactions.index', [
            'transactions' => $query->latest('transaction_at')->latest('id')->paginate(25)->withQueryString(),
            'statuses' => PaymentTransaction::MATCH_STATUSES,
            'statusCounts' => PaymentTransaction::query()->selectRaw('match_status, count(*) as aggregate')->groupBy('match_status')->pluck('aggregate', 'match_status'),
        ]);
    }

    public function show(PaymentTransaction $paymentTransaction): View
    {
        return view('admin.payment_transactions.show', [
            'transaction' => $paymentTransaction->load(['order', 'payment']),
            'statuses' => PaymentTransaction::MATCH_STATUSES,
            'orders' => Order::query()
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->latest()
                ->limit(100)
                ->get(['id', 'order_code', 'customer_name', 'total_amount']),
        ]);
    }

    public function attach(
        Request $request,
        PaymentTransaction $paymentTransaction,
        PaymentTransactionAdminService $service,
    ): RedirectResponse {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);
        $service->attach(
            $paymentTransaction,
            Order::findOrFail($data['order_id']),
            $request->user('admin'),
            $data['reason'],
        );

        return back()->with('success', 'Đã gắn giao dịch vào đơn hàng và đồng bộ công nợ.');
    }
}
