<?php

namespace App\Console\Commands;

use App\Services\SePayReconciliationService;
use Illuminate\Console\Command;
use Throwable;

class ReconcileSePayTransactions extends Command
{
    protected $signature = 'sepay:reconcile {--limit=1000 : Số giao dịch tối đa trong một lượt}';

    protected $description = 'Đối soát giao dịch SePay API v2 và bù webhook bị thiếu';

    public function handle(SePayReconciliationService $reconciliation): int
    {
        try {
            $result = $reconciliation->reconcile(max(1, (int) $this->option('limit')));
            $this->info("Đã đọc {$result['processed']} giao dịch; khớp {$result['matched']}; cần kiểm tra {$result['unmatched']}.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Đối soát SePay thất bại: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
