<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('homepage.homepage_stats', [
            ['value' => '8', 'suffix' => '+', 'label' => 'Năm kinh nghiệm', 'icon' => 'fa-solid fa-calendar-check'],
            ['value' => '2000', 'suffix' => '+', 'label' => 'Khách hàng', 'icon' => 'fa-solid fa-users'],
            ['value' => '1000', 'suffix' => '+', 'label' => 'Dự án truyền thông', 'icon' => 'fa-solid fa-film'],
            ['value' => '100', 'suffix' => '%', 'label' => 'Đồng hành xuyên suốt', 'icon' => 'fa-solid fa-handshake'],
        ]);
        $this->migrator->add('homepage.homepage_about_title', [
            'vi' => 'Công ty TNHH THT Media',
        ]);
        $this->migrator->add('homepage.homepage_about_text', [
            'vi' => 'THT Media xây dựng một hệ sinh thái sản xuất truyền thông thực tế cho doanh nghiệp, tổ chức và thương hiệu cá nhân.',
        ]);
        $this->migrator->add('homepage.homepage_about_supporting_text', [
            'vi' => 'Nhân sự in-house, thiết bị chủ động và quy trình rõ ràng giúp mỗi brief được chuyển thành nội dung có thể sử dụng ngay.',
        ]);
    }
};
