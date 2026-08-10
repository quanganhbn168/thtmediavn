<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Throwable;

class SePayTransactionProcessor
{
    public function __construct(private readonly OrderInventoryService $inventory) {}

    public function ingestWebhook(array $payload): PaymentTransaction
    {
        return $this->ingest([
            'provider' => 'sepay',
            'source' => 'webhook',
            'provider_transaction_id' => (string) $payload['id'],
            'reference_code' => $this->nullable($payload['referenceCode'] ?? null),
            'bank_gateway' => $this->nullable($payload['gateway'] ?? null),
            'account_number' => $this->nullable($payload['accountNumber'] ?? null),
            'payment_code' => $this->nullable($payload['code'] ?? null),
            'transaction_content' => $this->nullable($payload['content'] ?? null),
            'transfer_type' => strtolower((string) ($payload['transferType'] ?? '')),
            'amount' => (int) ($payload['transferAmount'] ?? 0),
            'transaction_at' => $this->date($payload['transactionDate'] ?? null),
            'signature_verified' => true,
            'raw_payload' => $payload,
        ]);
    }

    public function ingestApiTransaction(array $payload): PaymentTransaction
    {
        return $this->ingest([
            'provider' => 'sepay',
            'source' => 'api',
            'provider_transaction_id' => (string) $payload['id'],
            'reference_code' => $this->nullable($payload['reference_number'] ?? null),
            'bank_gateway' => $this->nullable($payload['bank_brand_name'] ?? null),
            'account_number' => $this->nullable($payload['account_number'] ?? null),
            'payment_code' => $this->nullable($payload['code'] ?? null),
            'transaction_content' => $this->nullable($payload['transaction_content'] ?? null),
            'transfer_type' => strtolower((string) ($payload['transfer_type'] ?? '')),
            'amount' => (int) ($payload['amount_in'] ?? 0),
            'transaction_at' => $this->date($payload['transaction_date'] ?? null),
            'signature_verified' => false,
            'raw_payload' => $payload,
        ]);
    }

    private function ingest(array $data): PaymentTransaction
    {
        $data['payment_code'] = $this->resolvePaymentCode($data['payment_code'], $data['transaction_content']);
        $data['deduplication_key'] = $this->deduplicationKey($data);
        $data['received_at'] = now();
        $data['match_status'] = 'unmatched';

        [$transaction, $created] = $this->persist($data);
        if (! $created) {
            $this->mergeSourcePayload($transaction, $data);
            if ($data['signature_verified'] && ! $transaction->signature_verified) {
                $transaction->update(['signature_verified' => true]);
            }
            $receivedMissingCode = false;
            if (! $transaction->payment_code && $data['payment_code']) {
                $receivedMissingCode = true;
                $transaction->update([
                    'payment_code' => $data['payment_code'],
                    'processed_at' => null,
                    'processing_error' => null,
                ]);
            }
            if ($transaction->processed_at !== null && ! $receivedMissingCode) {
                return $transaction->fresh();
            }
        }

        try {
            return $this->match($transaction);
        } catch (Throwable $exception) {
            $transaction->update([
                'processing_error' => mb_substr($exception->getMessage(), 0, 2000),
                'processed_at' => now(),
            ]);
            throw $exception;
        }
    }

    private function persist(array $data): array
    {
        $existing = PaymentTransaction::query()
            ->where(fn ($query) => $query
                ->where(function ($query) use ($data): void {
                    $query->where('provider', $data['provider'])
                        ->where('provider_transaction_id', $data['provider_transaction_id']);
                })
                ->orWhere(function ($query) use ($data): void {
                    $query->where('provider', $data['provider'])
                        ->where('deduplication_key', $data['deduplication_key']);
                }))
            ->first();

        if ($existing) {
            return [$existing, false];
        }

        try {
            return [PaymentTransaction::create($data), true];
        } catch (QueryException $exception) {
            $existing = PaymentTransaction::query()
                ->where('provider', $data['provider'])
                ->where(fn ($query) => $query
                    ->where('provider_transaction_id', $data['provider_transaction_id'])
                    ->orWhere('deduplication_key', $data['deduplication_key']))
                ->first();

            if (! $existing) {
                throw $exception;
            }

            return [$existing, false];
        }
    }

    private function mergeSourcePayload(PaymentTransaction $transaction, array $data): void
    {
        if ($transaction->source === $data['source']) {
            return;
        }

        $raw = $transaction->raw_payload;
        if (isset($raw['_sources']) && is_array($raw['_sources'])) {
            $sources = $raw['_sources'];
        } else {
            $sources = [$transaction->source => $raw];
        }
        $sources[$data['source']] = $data['raw_payload'];
        $transaction->update([
            'source' => 'combined',
            'raw_payload' => ['_sources' => $sources],
        ]);
    }

    private function match(PaymentTransaction $transaction): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction): PaymentTransaction {
            $transaction = PaymentTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if ($transaction->processed_at !== null) {
                return $transaction;
            }

            if ($transaction->transfer_type !== 'in' || $transaction->amount <= 0) {
                return $this->finish($transaction, 'ignored');
            }

            if (! hash_equals(
                $this->normalizeAccount((string) config('commerce.sepay.account_number')),
                $this->normalizeAccount((string) $transaction->account_number),
            )) {
                return $this->finish($transaction, 'ignored', 'Tài khoản nhận không khớp cấu hình THT MEDIA VN.');
            }

            if (! $transaction->payment_code) {
                return $this->finish($transaction, 'unmatched', 'Không tìm thấy mã thanh toán trong giao dịch.');
            }

            $order = Order::query()
                ->where('payment_provider', 'sepay')
                ->where('payment_code', strtoupper($transaction->payment_code))
                ->lockForUpdate()
                ->first();

            if (! $order) {
                return $this->finish($transaction, 'unmatched', 'Mã thanh toán không khớp đơn hàng.');
            }

            $transaction->update(['order_id' => $order->id]);

            if ($order->payment_status === 'paid') {
                return $this->finish($transaction, 'duplicate', 'Đơn hàng đã được thanh toán trước đó.');
            }

            $isLate = $order->stock_released_at !== null
                || ($order->payment_expires_at !== null
                    && $transaction->transaction_at !== null
                    && $transaction->transaction_at->isAfter($order->payment_expires_at));
            if ($isLate && ! (bool) config('commerce.sepay.allow_late_payment', false)) {
                return $this->finish($transaction, 'late', 'Giao dịch phát sinh sau khi phiên thanh toán hết hạn.');
            }
            if ($order->stock_released_at !== null) {
                return $this->finish($transaction, 'late', 'Tồn kho của đơn đã được hoàn; cần kiểm tra thủ công.');
            }

            $total = (int) round((float) $order->total_amount);
            $alreadyPaid = (int) round((float) $order->payments()->where('status', 'completed')->sum('amount'));
            $remaining = max(0, $total - $alreadyPaid);
            $allowsPartial = (bool) config('commerce.sepay.allow_underpayment', false);
            $amountMatches = $allowsPartial
                ? $transaction->amount > 0 && $transaction->amount <= $remaining
                : $alreadyPaid === 0 && $transaction->amount === $total;

            if (! $amountMatches) {
                return $this->finish($transaction, 'amount_mismatch', "Số tiền nhận {$transaction->amount} không khớp số tiền cần thu {$remaining}.");
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_transaction_id' => $transaction->id,
                'payment_code' => $alreadyPaid === 0
                    ? $order->payment_code
                    : $order->payment_code.'-'.substr($transaction->deduplication_key, 0, 8),
                'amount' => $transaction->amount,
                'method' => 'sepay_qr',
                'status' => 'completed',
                'transaction_id' => $transaction->provider_transaction_id,
                'payment_date' => $transaction->transaction_at ?? now(),
                'note' => 'Tự động ghi nhận từ SePay '.strtoupper($transaction->source).'.',
                'is_automatic' => true,
            ]);

            $paid = $alreadyPaid + $transaction->amount;
            $isPaid = $paid >= $total;
            $oldStatus = $order->status;
            $order->update([
                'paid_amount' => $paid,
                'remaining_amount' => max(0, $total - $paid),
                'payment_status' => $isPaid ? 'paid' : 'partial',
                'paid_at' => $isPaid ? ($transaction->transaction_at ?? now()) : null,
                'status' => $isPaid && $oldStatus === 'pending_payment' ? 'pending' : $oldStatus,
            ]);

            $transaction->update([
                'payment_id' => $payment->id,
                'match_status' => 'matched',
                'processed_at' => now(),
                'processing_error' => null,
            ]);

            if ($isPaid) {
                $this->inventory->recordSold($order);
                if ($oldStatus === 'pending_payment') {
                    $order->statusHistories()->create([
                        'from_status' => 'pending_payment',
                        'to_status' => 'pending',
                        'note' => 'SePay xác nhận đã nhận đủ tiền; đơn chuyển sang chờ xác nhận.',
                    ]);
                }
            }

            return $transaction->fresh(['order', 'payment']);
        });
    }

    private function finish(PaymentTransaction $transaction, string $status, ?string $error = null): PaymentTransaction
    {
        $transaction->update([
            'match_status' => $status,
            'processing_error' => $error,
            'processed_at' => now(),
        ]);

        return $transaction->fresh();
    }

    private function deduplicationKey(array $data): string
    {
        $reference = strtoupper(trim((string) ($data['reference_code'] ?? '')));
        $identity = $reference !== ''
            ? implode('|', [$data['provider'], $this->normalizeAccount((string) $data['account_number']), $reference])
            : implode('|', [
                $data['provider'],
                $this->normalizeAccount((string) $data['account_number']),
                $data['transaction_at']?->format('Y-m-d H:i:s') ?? '',
                (string) $data['amount'],
                mb_strtoupper(trim((string) $data['transaction_content'])),
            ]);

        return hash('sha256', $identity);
    }

    private function resolvePaymentCode(?string $code, ?string $content): ?string
    {
        $code = strtoupper(trim((string) $code));
        if ($code !== '') {
            return $code;
        }

        $prefix = preg_quote(strtoupper((string) config('commerce.sepay.payment_prefix', 'THT')), '/');
        if (preg_match('/(?<![A-Z0-9])('.$prefix.'[A-Z0-9]{8,12})(?![A-Z0-9])/i', (string) $content, $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }

    private function date(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return CarbonImmutable::parse($value, 'Asia/Ho_Chi_Minh');
    }

    private function normalizeAccount(string $value): string
    {
        return strtoupper((string) preg_replace('/[\s.\-]+/', '', trim($value)));
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
