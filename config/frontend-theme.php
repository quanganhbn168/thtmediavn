<?php

return [
    /*
     * Điểm chỉnh duy nhất cho bảng màu website frontend.
     * Đổi giá trị này thành tên một palette bên dưới, ví dụ: 'ocean_mist'.
     * Không có màn hình quản trị để người dùng tự đổi giao diện.
     */
    'active' => 'emerald_champagne',

    'palettes' => [
        'wine_champagne' => [
            // Wine rose & champagne: ấm, tin cậy và tiết chế.
            'primary' => '#8E3154',
            'secondary' => '#C6A56E',
            'ink' => '#292326',
            'muted' => '#756B70',
            'surface' => '#FFFFFF',
            'canvas' => '#F8F4EF',
            'line' => '#E8DDD9',
        ],
        'emerald_champagne' => [
            // Xanh ngọc lục bảo & vàng champagne: bảng màu đang sử dụng.
            'primary' => '#0B7A5A',
            'secondary' => '#C7A55B',
            'ink' => '#21362E',
            'muted' => '#65736C',
            'surface' => '#FFFFFF',
            'canvas' => '#F8FBF9',
            'line' => '#DFEAE4',
        ],
        'rose_violet' => [
            'primary' => '#CF2E70',
            'secondary' => '#7867E3',
            'ink' => '#282631',
            'muted' => '#77727E',
            'surface' => '#FFFFFF',
            'canvas' => '#FAF9FB',
            'line' => '#ECE9EF',
        ],
        'violet_bloom' => [
            'primary' => '#7657D9',
            'secondary' => '#C15DB5',
            'ink' => '#2D2735',
            'muted' => '#756E80',
            'surface' => '#FFFFFF',
            'canvas' => '#FAF9FD',
            'line' => '#EAE6F1',
        ],
        'ocean_mist' => [
            'primary' => '#167E95',
            'secondary' => '#4B70D6',
            'ink' => '#24323A',
            'muted' => '#697982',
            'surface' => '#FFFFFF',
            'canvas' => '#F7FAFB',
            'line' => '#E2ECEF',
        ],
        'botanical' => [
            'primary' => '#2F8A68',
            'secondary' => '#7A9B48',
            'ink' => '#29342D',
            'muted' => '#6E7B72',
            'surface' => '#FFFFFF',
            'canvas' => '#F8FBF8',
            'line' => '#E4ECE4',
        ],
        'coral_sunset' => [
            'primary' => '#D9576E',
            'secondary' => '#8C62D9',
            'ink' => '#35282D',
            'muted' => '#806F75',
            'surface' => '#FFFFFF',
            'canvas' => '#FCF9FA',
            'line' => '#F0E5E8',
        ],
    ],
];
