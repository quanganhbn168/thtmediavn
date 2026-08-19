<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('seo.seo_title', ['vi' => 'THT MEDIA VN']);
        $this->migrator->add('seo.seo_description', [
            'vi' => 'Thông tin, nội dung và các giải pháp truyền thông được phát triển bởi THT MEDIA VN.',
        ]);
        $this->migrator->add('seo.seo_keywords', [
            'vi' => 'THT MEDIA VN, truyền thông, nội dung, thương hiệu',
        ]);
        $this->migrator->add('seo.google_analytics_code', null);
    }
};
