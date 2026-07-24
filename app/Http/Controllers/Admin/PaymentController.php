<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Payment\IndexPaymentRequest;
use App\Http\Requests\Admin\Payment\StorePaymentRequest;
use App\Http\Requests\Admin\Payment\UpdatePaymentRequest;
use App\Services\PaymentService;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function index(IndexPaymentRequest $request): View
    {
        return view('admin.payments.index', [
            'payments' => $this->paymentService->paginate($request->validated()),
            'methods' => PaymentService::METHODS,
            'statuses' => PaymentService::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('admin.payments.create', $this->paymentService->formContext());
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $payment = $this->paymentService->create($request->validated() + ['created_by' => $request->user()->id]);

        return redirect()
            ->route('admin.payments.edit', $payment)
            ->with('success', 'Đã ghi nhận giao dịch.');
    }

    public function edit(Payment $payment): View
    {
        return view('admin.payments.edit', compact('payment') + $this->paymentService->formContext());
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->paymentService->update($payment, $request->validated());

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Đã cập nhật giao dịch và công nợ.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->paymentService->delete($payment);

        return back()->with('success', 'Đã xóa giao dịch và đồng bộ công nợ.');
    }
}
