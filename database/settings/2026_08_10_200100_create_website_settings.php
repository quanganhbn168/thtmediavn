<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('website.site_status', true);
        $this->migrator->add('website.multilingual_enabled', false);
        $this->migrator->add('website.timezone', 'Asia/Ho_Chi_Minh');
        $this->migrator->add('website.site_name', ['vi' => 'THT MEDIA VN']);
        $this->migrator->add('website.site_description', [
            'vi' => 'Nền tảng truyền thông, nội dung và giải pháp thương hiệu của THT MEDIA VN.',
        ]);
        $this->migrator->add('website.copyright', [
            'vi' => '© 2026 THT MEDIA VN. Tất cả quyền được bảo lưu.',
        ]);
        $this->migrator->add('website.header_menu_id', null);
        $this->migrator->add('website.mega_menu_id', null);
        $this->migrator->add('website.footer_menu_1_id', null);
        $this->migrator->add('website.footer_menu_2_id', null);
    }
};
