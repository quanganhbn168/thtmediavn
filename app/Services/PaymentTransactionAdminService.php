<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentTransactionAdminService
{
    public function __construct(private readonly PaymentService $payments) {}

    public function attach(PaymentTransaction $transaction, Order $order, User $actor, string $reason): Payment
    {
        return DB::transaction(function () use ($transaction, $order, $actor, $reason): Payment {
            $transaction = PaymentTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($transaction->payment_id !== null) {
                throw ValidationException::withMessages(['transaction' => 'Giao dịch này đã được gắn với một phiếu thanh toán.']);
            }
            if ($transaction->transfer_type !== 'in' || $transaction->amount <= 0) {
                throw ValidationException::withMessages(['transaction' => 'Chỉ có thể gắn giao dịch tiền vào hợp lệ.']);
            }

            $baseCode = $order->payment_code ?: 'RHEA-MANUAL-'.$order->id;
            $paymentCode = Payment::query()->where('payment_code', $baseCode)->exists()
                ? mb_substr($baseCode.'-'.substr($transaction->deduplication_key, 0, 8), 0, 50)
                : $baseCode;

            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_transaction_id' => $transaction->id,
                'payment_code' => $paymentCode,
                'amount' => $transaction->amount,
                'method' => 'sepay_qr',
                'status' => 'completed',
                'transaction_id' => $transaction->provider_transaction_id,
                'payment_date' => $transaction->transaction_at ?? now(),
                'note' => 'Admin '.$actor->name.' gắn giao dịch. Lý do: '.$reason,
                'created_by' => $actor->id,
                'is_automatic' => true,
            ]);

            $transaction->update([
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'match_status' => 'matched',
                'processed_at' => now(),
                'processing_error' => null,
            ]);
            $this->payments->syncOrder($order);

            return $payment;
        });
    }
}
