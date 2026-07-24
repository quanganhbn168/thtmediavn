<?php

declare(strict_types=1);

require __DIR__ . '/src/HttpClient.php';
require __DIR__ . '/src/HtmlDocument.php';
require __DIR__ . '/src/ProductScraper.php';

use App\HttpClient;
use App\ProductScraper;

$config = require __DIR__ . '/config.php';

$options = getopt('', [
    'start::',
    'end::',
    'download-images',
    'confirm-rights',
    'no-resume',
    'refresh-existing',
    'help',
]);

if (isset($options['help'])) {
    echo <<<TXT
MTD Product Crawler

Cách chạy cơ bản:
  php run.php --start=1 --end=41

Tải cả ảnh khi đã được chủ sở hữu cho phép:
  php run.php --start=1 --end=41 --download-images --confirm-rights

Chạy lại từ đầu, không dùng dữ liệu đã lưu:
  php run.php --start=1 --end=41 --no-resume

Cập nhật lại sản phẩm đã có, vẫn giữ ảnh cục bộ nếu không tải ảnh:
  php run.php --start=1 --end=41 --refresh-existing

Tham số:
  --start=N            Trang bắt đầu.
  --end=N              Trang kết thúc tối đa.
  --download-images    Tải ảnh sản phẩm về storage/images.
  --confirm-rights     Xác nhận anh có quyền sử dụng/tái xuất bản ảnh.
  --no-resume          Không bỏ qua sản phẩm đã có trong products.json.
  --refresh-existing   Crawl lại và cập nhật sản phẩm đã có.
  --help               Hiện hướng dẫn.

TXT;
    exit(0);
}

$start = max(1, (int) ($options['start'] ?? $config['start_page']));
$end = max($start, (int) ($options['end'] ?? $config['max_pages']));
$downloadImages = isset($options['download-images']);
$resume = !isset($options['no-resume']);
$refreshExisting = isset($options['refresh-existing']);

if ($downloadImages && !isset($options['confirm-rights'])) {
    fwrite(
        STDERR,
        "Từ chối tải ảnh: cần thêm --confirm-rights để xác nhận anh có quyền sử dụng ảnh.\n"
    );
    exit(1);
}

$http = new HttpClient(
    userAgent: $config['user_agent'],
    timeoutSeconds: $config['timeout_seconds'],
    retryTimes: $config['retry_times'],
    delayMs: $config['delay_ms'],
);

$scraper = new ProductScraper($config, $http);

try {
    $products = $scraper->crawl(
        startPage: $start,
        endPage: $end,
        downloadImages: $downloadImages,
        resume: $resume,
        refreshExisting: $refreshExisting,
    );

    echo PHP_EOL;
    echo 'Hoàn thành: ' . count($products) . ' sản phẩm.' . PHP_EOL;
    echo 'JSON: storage/products.json' . PHP_EOL;
    echo 'CSV: storage/products.csv' . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'Lỗi nghiêm trọng: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
