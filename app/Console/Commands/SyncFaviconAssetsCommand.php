<?php

namespace App\Console\Commands;

use App\Models\SiteAsset;
use App\Support\Branding\FaviconService;
use Illuminate\Console\Command;

class SyncFaviconAssetsCommand extends Command
{
    protected $signature = 'favicon:sync';

    protected $description = 'Tạo bộ favicon tĩnh từ favicon đã chọn trong SiteAsset.';

    public function handle(FaviconService $favicons): int
    {
        $media = SiteAsset::current()->getFirstMedia('favicon');

        if (! $media) {
            $this->components->info('Chưa có favicon được chọn; website dùng favicon mặc định hoặc fallback trong media.');

            return self::SUCCESS;
        }

        $favicons->sync($media);

        $this->components->info("Đã tạo bộ favicon tĩnh từ media #{$media->getKey()}.");

        return self::SUCCESS;
    }
}
