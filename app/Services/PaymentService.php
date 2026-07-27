<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public const METHODS = [
        'cash' => 'Tiền mặt / COD',
        'sepay_qr' => 'QR ngân hàng qua SePay',
        'manual_bank_transfer' => 'Chuyển khoản ghi nhận thủ công',
    ];

    public function __construct(private readonly OrderInventoryService $inventory) {}

    public const STATUSES = [
        'pending' => 'Chờ xử lý',
        'completed' => 'Hoàn tất',
        'failed' => 'Thất bại',
        'refunded' => 'Hoàn tiền',
    ];

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Payment::query()->with(['order:id,order_code,customer_name', 'transaction']);
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('payment_code', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function formContext(): array
    {
        return [
            'orders' => Order::query()
                ->latest('created_at')
                ->get()
                ->mapWithKeys(fn (Order $order): array => [$order->id => $order->order_code.' · '.$order->customer_name]),
            'methods' => self::METHODS,
            'statuses' => self::STATUSES,
        ];
    }

    public function create(array $data): Payment
    {
        $data['is_automatic'] = false;
        $payment = DB::transaction(function () use ($data): Payment {
            $payment = Payment::create($data);
            $this->syncOrder($payment->order);

            return $payment;
        });

        return $payment;
    }

    public function update(Payment $payment, array $data): void
    {
        $this->ensureManual($payment);
        $oldOrder = $payment->order;

        DB::transaction(function () use ($payment, $data, $oldOrder): void {
            $payment->update($data);
            $this->syncOrder($payment->fresh()->order);
            $this->syncOrder($oldOrder);
        });
    }

    public function delete(Payment $payment): void
    {
        $this->ensureManual($payment);
        $order = $payment->order;
        DB::transaction(function () use ($payment, $order): void {
            $payment->delete();
            $this->syncOrder($order);
        });
    }

    public function syncOrder(Order $order): void
    {
        $order = Order::query()->lockForUpdate()->findOrFail($order->id);
        $paid = (float) $order->payments()->where('status', 'completed')->sum('amount');
        $total = (float) $order->total_amount;
        $isPaid = $paid >= $total && $total > 0;
        $oldStatus = $order->status;
        $paidAt = $isPaid
            ? $order->payments()->where('status', 'completed')->max('payment_date')
            : null;

        $order->update([
            'paid_amount' => $paid,
            'remaining_amount' => max(0.0, $total - $paid),
            'payment_status' => $paid <= 0 ? 'unpaid' : ($isPaid ? 'paid' : 'partial'),
            'paid_at' => $paidAt,
            'status' => $isPaid && $oldStatus === 'pending_payment' ? 'pending' : $oldStatus,
        ]);

        if ($isPaid) {
            $this->inventory->recordSold($order);
            if ($oldStatus === 'pending_payment') {
                $order->statusHistories()->create([
                    'from_status' => 'pending_payment',
                    'to_status' => 'pending',
                    'user_id' => auth()->id(),
                    'note' => 'Đã ghi nhận đủ tiền; đơn chuyển sang chờ xác nhận.',
                ]);
            }
        }
    }

    private function ensureManual(Payment $payment): void
    {
        if ($payment->is_automatic) {
            throw ValidationException::withMessages([
                'payment' => 'Giao dịch SePay tự động là dữ liệu tài chính chỉ đọc, không thể sửa hoặc xóa.',
            ]);
        }
    }
}
