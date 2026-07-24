<?php

return [
    'path' => base_path('mtd-product-crawler/storage/products.json'),
    'max_images' => 12,
    'fallback_category' => 'khac',

    /* Keys are normalized with Str::slug before lookup. */
    'category_map' => [
        'makeup' => 'trang-diem',
        'kem-duong' => 'kem-duong',
        'son' => 'trang-diem',
        'tinh-chat' => 'serum',
        'dau-goi-sua-tam' => 'cham-soc-co-the',
        'phu-kien' => 'khac',
        'chong-nang' => 'chong-nang',
        'body' => 'cham-soc-co-the',
        'mat-na' => 'mat-na',
        'sua-rua-mat' => 'sua-rua-mat',
        'tay-trang' => 'tay-trang',
        'tdc' => 'cham-soc-co-the',
        'thuc-pham-chuc-nang' => 'thuc-pham-chuc-nang',
        'thuc-pham' => 'thuc-pham-chuc-nang',
        'cham-soc-vung-kin' => 'cham-soc-co-the',
        'nuoc-hoa' => 'khac',
        'toner' => 'cham-soc-mat',
        'lan-khu-mui' => 'cham-soc-co-the',
        'combo' => 'khac',
        'cham-soc-rang-mieng' => 'khac',
        'bong' => 'khac',
        'mieng-dan-mun' => 'cham-soc-mat',
        'km' => 'khac',
        'dang-cap-nhat' => 'khac',
        'xit-body' => 'cham-soc-co-the',
    ],

    /* Alias key => canonical display name. */
    'brand_aliases' => [
        'ruee' => 'RUEE',
        'relab' => 'RE:LAB',
        'jm-solution' => 'JM SOLUTION',
        'jmsolution' => 'JM SOLUTION',
        'skin-1004' => 'SKIN1004',
        'skin1004' => 'SKIN1004',
        'so-natural' => 'SO NATURAL',
        'sonatural-power-4-room' => 'SO NATURAL',
    ],
];
