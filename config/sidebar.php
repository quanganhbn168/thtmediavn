<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Sidebar Menu Config
    |--------------------------------------------------------------------------
    |
    | Cấu hình tập trung danh sách menu hiển thị ở thanh Sidebar của admin.
    | Hỗ trợ:
    |   - type => 'header': Dòng tiêu đề phân nhóm menu.
    |   - type => 'link': Mục liên kết (Hỗ trợ xác định active tự động dựa trên route).
    |   - children => Mảng các menu con đa cấp (tự động render dropdown & mở rộng active).
    |
    */

    'menu' => [
        // Bảng điều khiển
        [
            'type' => 'link',
            'label' => 'Tổng quan (Dashboard)',
            'route' => 'admin.dashboard',
            'icon' => 'bi bi-speedometer2',
        ],

        // Quản lý đơn hàng
        [
            'type' => 'header',
            'label' => 'BÁN HÀNG',
        ],
        [
            'type' => 'link',
            'label' => 'Đơn hàng',
            'route' => 'admin.orders.index',
            'icon' => 'bi bi-cart3',
        ],
        [
            'type' => 'link',
            'label' => 'Thanh toán',
            'icon' => 'bi bi-wallet2',
            'children' => [
                ['label' => 'Phiếu thanh toán', 'route' => 'admin.payments.index', 'icon' => 'bi bi-circle'],
                ['label' => 'Giao dịch ngân hàng', 'route' => 'admin.payment-transactions.index', 'icon' => 'bi bi-circle'],
            ],
        ],
        [
            'type' => 'header',
            'label' => 'QUẢN LÝ SẢN PHẨM VÀ KHO',
        ],
        [
            'type' => 'link',
            'label' => 'Quản lý sản phẩm',
            'icon' => 'bi bi-box-seam',
            'children' => [
                ['label' => 'Sản phẩm', 'route' => 'admin.products.index', 'icon' => 'bi bi-circle'],
                ['label' => 'Danh mục sản phẩm', 'route' => 'admin.product-categories.index', 'icon' => 'bi bi-circle'],
                ['label' => 'Thương hiệu', 'route' => 'admin.brands.index', 'icon' => 'bi bi-circle'],
                ['label' => 'Bộ lọc', 'route' => 'admin.product-attributes.index', 'icon' => 'bi bi-circle'],
                ['label' => 'Biến thể', 'route' => 'admin.product-options.index', 'icon' => 'bi bi-circle'],
            ],
        ],
        [
            'type' => 'link',
            'label' => 'Quản lý Combo',
            'icon' => 'bi bi-boxes',
            'children' => [
                ['label' => 'Danh mục Combo', 'route' => 'admin.combo-categories.index', 'icon' => 'bi bi-circle'],
                ['label' => 'Combo', 'route' => 'admin.combos.index', 'icon' => 'bi bi-circle'],
            ],
        ],
        [
            'type' => 'header',
            'label' => 'KHUYẾN MÃI',
        ],
        [
            'type' => 'link',
            'label' => 'Flash Sale',
            'route' => 'admin.flash-sales.index',
            'icon' => 'bi bi-lightning-charge',
        ],
        [
            'type' => 'link',
            'label' => 'Mã giảm giá',
            'route' => 'admin.coupons.index',
            'icon' => 'bi bi-ticket-perforated',
        ],

        // Quản trị nội dung CMS
        [
            'type' => 'header',
            'label' => 'QUẢN TRỊ NỘI DUNG (CMS)',
        ],
        [
            'type' => 'link',
            'label' => 'Trang tĩnh',
            'route' => 'admin.pages.index',
            'icon' => 'bi bi-file-earmark-richtext',
        ],
        [
            'type' => 'link',
            'label' => 'Bài viết / Tin tức',
            'icon' => 'bi bi-newspaper',
            'children' => [
                [
                    'label' => 'Danh sách bài viết',
                    'route' => 'admin.posts.index',
                    'icon' => 'bi bi-circle',
                ],
                [
                    'label' => 'Danh mục bài viết',
                    'route' => 'admin.post-categories.index',
                    'icon' => 'bi bi-circle',
                ],
            ],
        ],
        [
            'type' => 'link',
            'label' => 'Trình chiếu (Sliders)',
            'route' => 'admin.sliders.index',
            'icon' => 'bi bi-images',
        ],
        [
            'type' => 'link',
            'label' => 'Cảm nhận khách hàng',
            'route' => 'admin.testimonials.index',
            'icon' => 'bi bi-chat-quote',
        ],
        [
            'type' => 'link',
            'label' => 'Quản lý Menu',
            'route' => 'admin.menus.index',
            'icon' => 'bi bi-menu-button-wide',
        ],

        // Khách hàng & CRM
        [
            'type' => 'header',
            'label' => 'CRM & LIÊN HỆ',
        ],
        [
            'type' => 'link',
            'label' => 'Tin nhắn liên hệ',
            'route' => 'admin.contacts.index',
            'icon' => 'bi bi-envelope',
        ],
        [
            'type' => 'link',
            'label' => 'Đăng ký nhận tin',
            'route' => 'admin.subscribers.index',
            'icon' => 'bi bi-mailbox',
        ],
        [
            'type' => 'link',
            'label' => 'Đánh giá sản phẩm',
            'route' => 'admin.reviews.index',
            'icon' => 'bi bi-star',
        ],
        [
            'type' => 'link',
            'label' => 'Bình luận bài viết',
            'route' => 'admin.comments.index',
            'icon' => 'bi bi-chat-square-text',
        ],

        // Hệ thống & cấu hình
        [
            'type' => 'header',
            'label' => 'HỆ THỐNG & TÀI KHOẢN',
        ],
        [
            'type' => 'link',
            'label' => 'Tài khoản quản trị',
            'route' => 'admin.users.index',
            'icon' => 'bi bi-shield-lock',
        ],
        [
            'type' => 'link',
            'label' => 'Vai trò & Phân quyền',
            'route' => 'admin.roles.index',
            'icon' => 'bi bi-person-workspace',
        ],
        [
            'type' => 'link',
            'label' => 'Cài đặt',
            'route' => 'admin.settings.general',
            'icon' => 'bi bi-gear',
        ],
    ],
];
