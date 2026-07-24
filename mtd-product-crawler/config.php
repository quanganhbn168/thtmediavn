<?php

declare(strict_types=1);

return [
    'base_url' => 'https://mtd-global.com',
    'collection_path' => '/collections/all',

    // Website hiện có khoảng 41 trang, nhưng crawler vẫn tự dừng khi không còn URL mới.
    'start_page' => 1,
    'max_pages' => 100,

    // Nên để 700-1500ms để tránh tạo tải lớn lên website nguồn.
    'delay_ms' => 1000,
    'timeout_seconds' => 30,
    'retry_times' => 3,

    'user_agent' => 'Mozilla/5.0 (compatible; ProductMigrationBot/1.0; +https://example.com/contact)',

    'storage_path' => __DIR__ . '/storage',
    'image_path' => __DIR__ . '/storage/images',

    // Mặc định không tải ảnh. Chỉ bật bằng CLI khi anh có quyền sử dụng ảnh.
    'download_images' => false,

    // Nếu true, crawler sẽ cố giữ HTML mô tả sản phẩm.
    'keep_description_html' => true,
];
