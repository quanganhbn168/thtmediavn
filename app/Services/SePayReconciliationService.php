<?php

namespace App\Services;

use App\Models\PaymentSyncState;
use Throwable;

class SePayReconciliationService
{
    public function __construct(
        private readonly SePayApiClient $api,
        private readonly SePayTransactionProcessor $processor,
    ) {}

    public function reconcile(int $maxTransactions = 1000): array
    {
        $state = PaymentSyncState::firstOrCreate(['provider' => 'sepay']);
        $sinceId = $state->last_transaction_id;
        $processed = 0;
        $matched = 0;
        $unmatched = 0;

        try {
            do {
                $query = [
                    'transfer_type' => 'in',
                    'per_page' => min(100, $maxTransactions - $processed),
                    'transaction_date_sort' => 'asc',
                    'timestamp_format' => 'iso8601',
                ];
                if ($sinceId) {
                    $query['since_id'] = $sinceId;
                } else {
                    $query['transaction_date_from'] = now()
                        ->subDays((int) config('commerce.sepay.initial_reconciliation_days', 7))
                        ->format('Y-m-d H:i:s');
                }

                $payload = $this->api->transactions($query);
                $rows = $payload['data'];
                foreach ($rows as $row) {
                    if (! is_array($row) || empty($row['id'])) {
                        continue;
                    }

                    $transaction = $this->processor->ingestApiTransaction($row);
                    $sinceId = (string) $row['id'];
                    $processed++;
                    if ($transaction->match_status === 'matched') {
                        $matched++;
                    } elseif ($transaction->match_status !== 'ignored') {
                        $unmatched++;
                    }

                    if ($processed >= $maxTransactions) {
                        break;
                    }
                }

                $hasMore = count($rows) === (int) $query['per_page'] && $processed < $maxTransactions;
                if ($hasMore) {
                    usleep(350000);
                }
            } while ($hasMore);

            $state->update([
                'last_transaction_id' => $sinceId,
                'last_reconciled_at' => now(),
                'last_status' => 'success',
                'last_error' => null,
                'last_processed_count' => $processed,
            ]);

            return compact('processed', 'matched', 'unmatched');
        } catch (Throwable $exception) {
            $state->update([
                'last_reconciled_at' => now(),
                'last_status' => 'failed',
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
                'last_processed_count' => $processed,
            ]);
            throw $exception;
        }
    }
}
