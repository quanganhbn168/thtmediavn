<?php

namespace App\Console\Commands;

use App\Services\SePayExpirationService;
use Illuminate\Console\Command;

class ExpireSePayPayments extends Command
{
    protected $signature = 'sepay:expire {--limit=500 : Số đơn tối đa trong một lượt}';

    protected $description = 'Hết hạn phiên SePay chưa thanh toán và hoàn tồn kho';

    public function handle(SePayExpirationService $expiration): int
    {
        $count = $expiration->expireDue(max(1, (int) $this->option('limit')));
        $this->info("Đã hết hạn và hoàn kho {$count} đơn SePay.");

        return self::SUCCESS;
    }
}
