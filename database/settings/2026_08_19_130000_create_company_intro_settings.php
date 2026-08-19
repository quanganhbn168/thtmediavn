<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('about.about_page_label', [
            'vi' => 'Giới thiệu',
        ]);
        $this->migrator->add('about.about_page_title', [
            'vi' => 'Năng lực, cách làm và những giá trị THT Media theo đuổi',
        ]);
        $this->migrator->add('about.about_page_intro', [
            'vi' => 'THT Media đồng hành cùng doanh nghiệp từ định hướng, sản xuất đến bàn giao sản phẩm truyền thông.',
        ]);
    }
};
