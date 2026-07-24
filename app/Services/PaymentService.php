<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentService
{
    public const METHODS = [
        'cash' => 'Tiền mặt',
        'bank_transfer' => 'Chuyển khoản',
        'vnpay' => 'VNPay',
        'momo' => 'MoMo',
        'zalopay' => 'ZaloPay',
    ];

    public const STATUSES = [
        'pending' => 'Chờ xử lý',
        'completed' => 'Hoàn tất',
        'failed' => 'Thất bại',
        'refunded' => 'Hoàn tiền',
    ];

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Payment::query()->with('order:id,order_code,customer_name');
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
                ->mapWithKeys(fn (Order $order): array => [$order->id => $order->order_code . ' · ' . $order->customer_name]),
            'methods' => self::METHODS,
            'statuses' => self::STATUSES,
        ];
    }

    public function create(array $data): Payment
    {
        $payment = Payment::create($data);
        $this->syncOrder($payment->order);

        return $payment;
    }

    public function update(Payment $payment, array $data): void
    {
        $oldOrder = $payment->order;

        $payment->update($data);
        $this->syncOrder($payment->fresh()->order);
        $this->syncOrder($oldOrder);
    }

    public function delete(Payment $payment): void
    {
        $order = $payment->order;
        $payment->delete();
        $this->syncOrder($order);
    }

    private function syncOrder(Order $order): void
    {
        $paid = (float) $order->payments()->where('status', 'completed')->sum('amount');
        $total = (float) $order->total_amount;

        $order->update([
            'paid_amount' => $paid,
            'remaining_amount' => max(0.0, $total - $paid),
            'payment_status' => $paid <= 0 ? 'unpaid' : ($paid >= $total ? 'paid' : 'partial'),
        ]);
    }
}

