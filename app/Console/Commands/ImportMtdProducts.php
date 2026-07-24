<?php

namespace App\Console\Commands;

use App\Services\Imports\MtdProductImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportMtdProducts extends Command
{
    protected $signature = 'mtd:import
        {--path= : Đường dẫn products.json}
        {--dry-run : Chỉ phân tích, không ghi dữ liệu}
        {--limit=0 : Giới hạn số bản ghi đầu vào}
        {--only= : Danh sách slug, phân cách bằng dấu phẩy}
        {--with-images : Đồng bộ ảnh vào Media Library}
        {--adopt-existing : Liên kết sản phẩm đang có cùng slug}
        {--refresh-content : Cập nhật tên, mô tả và SEO của bản ghi đã liên kết}';

    protected $description = 'Nhập và đồng bộ dữ liệu sản phẩm từ MTD Product Crawler';

    public function handle(MtdProductImporter $importer): int
    {
        $path = (string) ($this->option('path') ?: config('mtd_import.path'));

        try {
            $report = $importer->import($path, [
                'dry_run' => (bool) $this->option('dry-run'),
                'limit' => (int) $this->option('limit'),
                'only' => (string) $this->option('only'),
                'with_images' => (bool) $this->option('with-images'),
                'adopt_existing' => (bool) $this->option('adopt-existing'),
                'refresh_content' => (bool) $this->option('refresh-content'),
            ]);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(['Chỉ số', 'Số lượng'], [
            ['Đầu vào', $report['total']],
            ['Hợp lệ', $report['eligible']],
            ['Tạo mới', $report['created']],
            ['Cập nhật', $report['updated']],
            ['Liên kết bản ghi cũ', $report['adopted']],
            ['Xung đột slug', $report['conflicts']],
            ['Trang danh mục bị loại', $report['invalid_pages']],
            ['Thiếu giá', $report['missing_price']],
            ['Ảnh thêm mới', $report['images_added']],
            ['Ảnh thay thế', $report['images_replaced']],
            ['Ảnh gỡ bỏ', $report['images_removed']],
            ['Lỗi', $report['errors']],
        ]);

        foreach ($report['warnings'] as $warning) {
            $this->warn($warning);
        }

        if ($this->option('dry-run')) {
            $this->info('Dry-run hoàn tất, database không bị thay đổi.');
        }

        return $report['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
