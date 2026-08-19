<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('about.about_story', [
            'vi' => '<p>THT MEDIA VN xây dựng nội dung và giải pháp truyền thông trên một nền tảng quản trị thống nhất, minh bạch và dễ mở rộng.</p>',
        ]);
        $this->migrator->add('about.about_history', ['vi' => '']);
        $this->migrator->add('about.about_mission', [
            'vi' => 'Tạo ra nội dung rõ ràng, hữu ích và phù hợp với mục tiêu của từng dự án.',
        ]);
        $this->migrator->add('about.about_vision', [
            'vi' => 'Phát triển một hệ sinh thái truyền thông linh hoạt, bền vững và có khả năng mở rộng.',
        ]);
        $this->migrator->add('about.about_core_values', ['vi' => '']);
    }
};
