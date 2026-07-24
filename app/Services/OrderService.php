<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderService
{
    public const STATUSES = [
        'pending' => 'Chờ xác nhận',
        'processing' => 'Đang xử lý',
        'shipping' => 'Đang giao hàng',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid' => 'Chưa thanh toán',
        'partial' => 'Còn nợ',
        'paid' => 'Đã thanh toán',
        'refunded' => 'Đã hoàn tiền',
    ];

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Order::query()->with('user');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('order_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        return $query->latest()
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function getFormContext(Order $order): array
    {
        return [
            'order' => $order->load(['items', 'payments', 'statusHistories.user']),
            'statuses' => self::STATUSES,
            'paymentStatuses' => self::PAYMENT_STATUSES,
            'users' => User::query()
                ->whereHas('roles', fn ($query) => $query
                    ->where('guard_name', 'web')
                    ->whereIn('name', ['admin', 'staff']))
                ->orderBy('name')
                ->pluck('name', 'id'),
        ];
    }

    public function update(Order $order, array $data): void
    {
        $oldStatus = $order->status;

        $order->update([
            'status' => $data['status'],
            'payment_status' => $data['payment_status'],
            'assigned_to' => $this->toNullableInt($data['assigned_to'] ?? null),
            'admin_note' => $data['admin_note'] ?? null,
        ]);

        if ($oldStatus !== $order->status) {
            $order->statusHistories()->create([
                'to_status' => $order->status,
                'from_status' => $oldStatus,
                'user_id' => auth()->id(),
                'note' => $data['admin_note'] ?? null,
            ]);
        }
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
