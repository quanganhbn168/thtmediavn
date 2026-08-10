<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_status', true);
        $this->migrator->add('general.timezone', 'Asia/Ho_Chi_Minh');
        $this->migrator->add('general.copyright', [
            'vi' => '© 2026 THT MEDIA VN. Tất cả quyền được bảo lưu.',
        ]);
        $this->migrator->add('general.site_name', [
            'vi' => 'THT MEDIA VN',
        ]);
        $this->migrator->add('general.site_description', [
            'vi' => 'Nền tảng truyền thông, nội dung và giải pháp thương hiệu của THT MEDIA VN.',
        ]);

        $this->migrator->add('contact.company_name', 'THT MEDIA VN');
        $this->migrator->add('contact.address', null);
        $this->migrator->add('contact.phone', null);
        $this->migrator->add('contact.email', null);
        $this->migrator->add('contact.tax_code', null);
        $this->migrator->add('contact.map_embed', null);
        $this->migrator->add('contact.working_hours', null);
        $this->migrator->add('contact.facebook', null);
        $this->migrator->add('contact.instagram', null);
        $this->migrator->add('contact.youtube', null);
        $this->migrator->add('contact.tiktok', null);
        $this->migrator->add('contact.zalo', null);

        $this->migrator->add('seo.seo_title', [
            'vi' => 'THT MEDIA VN',
        ]);
        $this->migrator->add('seo.seo_description', [
            'vi' => 'Thông tin, nội dung và các giải pháp truyền thông được phát triển bởi THT MEDIA VN.',
        ]);
        $this->migrator->add('seo.seo_keywords', [
            'vi' => 'THT MEDIA VN, truyền thông, nội dung, thương hiệu',
        ]);
        $this->migrator->add('seo.google_analytics_code', null);

        $this->migrator->add('homepage.homepage_banner_type', 'slider');
        $this->migrator->add('homepage.homepage_sections', ['posts']);
        $this->migrator->add('homepage.homepage_section_titles', [
            'categories' => ['vi' => 'Danh mục'],
            'flash_sale' => ['vi' => 'Chương trình nổi bật'],
            'featured_products' => ['vi' => 'Nội dung nổi bật'],
            'brands' => ['vi' => 'Đối tác'],
            'posts' => ['vi' => 'Tin tức và góc nhìn'],
        ]);

        $this->migrator->add('about.about_story', [
            'vi' => '<p>THT MEDIA VN xây dựng nội dung và giải pháp truyền thông trên một nền tảng quản trị thống nhất, minh bạch và dễ mở rộng.</p>',
        ]);
        $this->migrator->add('about.about_mission', [
            'vi' => 'Tạo ra nội dung rõ ràng, hữu ích và phù hợp với mục tiêu của từng dự án.',
        ]);
        $this->migrator->add('about.about_vision', [
            'vi' => 'Phát triển một hệ sinh thái truyền thông linh hoạt, bền vững và có khả năng mở rộng.',
        ]);

        $this->migrator->add('media.media_allowed_extensions', 'jpg,jpeg,png,webp,gif,pdf,doc,docx');
        $this->migrator->add('media.media_max_size', 10);
        $this->migrator->add('media.media_webp_conversion', true);
        $this->migrator->add('media.media_quality', 85);
    }
};
