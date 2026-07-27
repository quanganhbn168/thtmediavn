<?php

return [
    'shipping' => [
        'flat_fee' => (int) env('SHIPPING_FLAT_FEE', 30000),
        'free_threshold' => (int) env('FREE_SHIPPING_THRESHOLD', 1000000),
    ],

    'sepay' => [
        'enabled' => filter_var(env('SEPAY_ENABLED', false), FILTER_VALIDATE_BOOL),
        'mode' => env('SEPAY_MODE', 'test'),
        'payment_timeout_minutes' => max(5, (int) env('SEPAY_PAYMENT_TIMEOUT_MINUTES', 20)),
        'payment_prefix' => strtoupper(trim((string) env('SEPAY_PAYMENT_PREFIX', 'RHEA'))),
        'bank_name' => trim((string) env('SEPAY_BANK_NAME', '')),
        'bank_code' => trim((string) env('SEPAY_BANK_CODE', '')),
        'account_name' => trim((string) env('SEPAY_ACCOUNT_NAME', '')),
        'account_number' => trim((string) env('SEPAY_ACCOUNT_NUMBER', '')),
        'branch' => trim((string) env('SEPAY_BANK_BRANCH', '')),
        'webhook_secret' => trim((string) env('SEPAY_WEBHOOK_SECRET', '')),
        'api_token' => trim((string) env('SEPAY_API_TOKEN', '')),
        'api_base_url' => rtrim((string) (env('SEPAY_API_BASE_URL') ?: (
            env('SEPAY_MODE', 'test') === 'live'
                ? 'https://userapi.sepay.vn/v2'
                : 'https://userapi-sandbox.sepay.vn/v2'
        )), '/'),
        'initial_reconciliation_days' => max(1, (int) env('SEPAY_INITIAL_RECONCILIATION_DAYS', 7)),
        'allow_underpayment' => filter_var(env('SEPAY_ALLOW_UNDERPAYMENT', false), FILTER_VALIDATE_BOOL),
        'allow_late_payment' => filter_var(env('SEPAY_ALLOW_LATE_PAYMENT', false), FILTER_VALIDATE_BOOL),
    ],

    /*
     * Việt Nam vận hành mô hình hành chính hai cấp từ 01/07/2025.
     * Mã tỉnh dùng để tải danh mục phường/xã hiện hành từ Province Open API v2.
     */
    'provinces' => [
        1 => 'Thành phố Hà Nội',
        4 => 'Tỉnh Cao Bằng',
        8 => 'Tỉnh Tuyên Quang',
        11 => 'Tỉnh Điện Biên',
        12 => 'Tỉnh Lai Châu',
        14 => 'Tỉnh Sơn La',
        15 => 'Tỉnh Lào Cai',
        19 => 'Tỉnh Thái Nguyên',
        20 => 'Tỉnh Lạng Sơn',
        22 => 'Tỉnh Quảng Ninh',
        24 => 'Tỉnh Bắc Ninh',
        25 => 'Tỉnh Phú Thọ',
        31 => 'Thành phố Hải Phòng',
        33 => 'Tỉnh Hưng Yên',
        37 => 'Tỉnh Ninh Bình',
        38 => 'Tỉnh Thanh Hóa',
        40 => 'Tỉnh Nghệ An',
        42 => 'Tỉnh Hà Tĩnh',
        44 => 'Tỉnh Quảng Trị',
        46 => 'Thành phố Huế',
        48 => 'Thành phố Đà Nẵng',
        51 => 'Tỉnh Quảng Ngãi',
        52 => 'Tỉnh Gia Lai',
        56 => 'Tỉnh Khánh Hòa',
        66 => 'Tỉnh Đắk Lắk',
        68 => 'Tỉnh Lâm Đồng',
        75 => 'Tỉnh Đồng Nai',
        79 => 'Thành phố Hồ Chí Minh',
        80 => 'Tỉnh Tây Ninh',
        82 => 'Tỉnh Đồng Tháp',
        86 => 'Tỉnh Vĩnh Long',
        91 => 'Tỉnh An Giang',
        92 => 'Thành phố Cần Thơ',
        96 => 'Tỉnh Cà Mau',
    ],
];
