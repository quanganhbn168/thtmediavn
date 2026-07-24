<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // 1. Nhóm 'general' (Cài đặt chung)
        $this->migrator->add('general.site_status', true);
        $this->migrator->add('general.timezone', 'Asia/Ho_Chi_Minh');
        $this->migrator->add('general.copyright', [
            'vi' => '© 2026 RHEA SKINLAB. Tất cả quyền được bảo lưu.',
        ]);
        $this->migrator->add('general.site_name', [
            'vi' => 'RHEA SKINLAB',
        ]);
        $this->migrator->add('general.site_description', [
            'vi' => 'Sản phẩm mỹ phẩm và chăm sóc cá nhân chính hãng được tuyển chọn từ các thương hiệu uy tín Hàn Quốc, Nhật Bản và Châu Âu.',
        ]);

        // 2. Nhóm 'contact' (Thông tin liên hệ)
        $this->migrator->add('contact.company_name', 'Công ty TNHH Quốc tế RHEA SKINLAB');
        $this->migrator->add('contact.address', 'Khối 5, Sóc Sơn, Hà Nội, Việt Nam');
        $this->migrator->add('contact.phone', '0395 686 598');
        $this->migrator->add('contact.email', 'rheaskinlab@gmail.com');
        $this->migrator->add('contact.tax_code', '0110395713');
        $this->migrator->add('contact.map_embed', null);
        $this->migrator->add('contact.working_hours', null);
        $this->migrator->add('contact.facebook', null);
        $this->migrator->add('contact.instagram', null);
        $this->migrator->add('contact.youtube', null);
        $this->migrator->add('contact.tiktok', null);
        $this->migrator->add('contact.zalo', 'https://zalo.me/0395686598');

        // 3. Nhóm 'seo' (SEO mặc định)
        $this->migrator->add('seo.seo_title', [
            'vi' => 'RHEA SKINLAB - Dược mỹ phẩm Á Âu chính hãng',
        ]);
        $this->migrator->add('seo.seo_description', [
            'vi' => 'RHEA SKINLAB phân phối sản phẩm chăm sóc da, hóa mỹ phẩm và dược mỹ phẩm chính hãng, đồng hành cùng khách hàng lựa chọn giải pháp phù hợp.',
        ]);
        $this->migrator->add('seo.seo_keywords', [
            'vi' => 'chăm sóc da, hóa mỹ phẩm, dược mỹ phẩm, mỹ phẩm chính hãng',
        ]);
        $this->migrator->add('seo.google_analytics_code', null);

        // 4. Nhóm 'homepage' (Trang chủ)
        $this->migrator->add('homepage.homepage_banner_type', 'slider');
        $this->migrator->add('homepage.homepage_sections', ['categories', 'flash_sale', 'featured_products', 'brands', 'posts']);
        $this->migrator->add('homepage.homepage_section_titles', [
            'categories' => ['vi' => 'Danh mục nổi bật'],
            'flash_sale' => ['vi' => 'Flash Sale'],
            'featured_products' => ['vi' => 'Sản phẩm nổi bật'],
            'brands' => ['vi' => 'Thương hiệu đồng hành'],
            'posts' => ['vi' => 'Blog'],
        ]);

        // 5. Nhóm 'about' (Giới thiệu)
        $this->migrator->add('about.about_story', [
            'vi' => '<p>RHEA SKINLAB mang đến sản phẩm mỹ phẩm và chăm sóc cá nhân chính hãng được tuyển chọn từ các thương hiệu uy tín.</p>',
        ]);
        $this->migrator->add('about.about_mission', [
            'vi' => 'Giúp khách hàng lựa chọn sản phẩm chăm sóc cá nhân phù hợp, an toàn và minh bạch.',
        ]);
        $this->migrator->add('about.about_vision', [
            'vi' => 'Trở thành địa chỉ mỹ phẩm uy tín được khách hàng Việt Nam tin chọn.',
        ]);

        // 6. Nhóm 'media' (Quy tắc media)
        $this->migrator->add('media.media_allowed_extensions', 'jpg,jpeg,png,webp,gif,pdf,doc,docx');
        $this->migrator->add('media.media_max_size', 10);
        $this->migrator->add('media.media_webp_conversion', true);
        $this->migrator->add('media.media_quality', 85);
    }
};
