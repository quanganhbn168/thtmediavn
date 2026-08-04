<?php

namespace App\Services;

use App\Models\SiteAsset;
use App\Settings\AboutSettings;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\HomepageSettings;
use App\Settings\MediaSettings;
use App\Settings\MenuSettings;
use App\Settings\SeoSettings;

class SettingService
{
    public function __construct(
        private readonly MediaService $mediaService,
        private readonly WebsiteSettingsService $websiteSettings,
        private readonly SiteChromeCache $siteChromeCache,
    ) {}

    /**
     * Cập nhật Cài đặt chung
     */
    public function updateGeneral(array $data, GeneralSettings $settings): void
    {
        $settings->site_status = isset($data['site_status']);
        $settings->timezone = $data['timezone'] ?? 'Asia/Ho_Chi_Minh';
        $settings->copyright = $data['copyright'] ?? [];
        $settings->site_name = $data['site_name'] ?? [];
        $settings->site_description = $data['site_description'] ?? [];

        $settings->save();
        $this->websiteSettings->refresh();
        config(['app.timezone' => $settings->timezone]);
        config(['app.name' => $settings->site_name['vi'] ?? config('app.name')]);
        date_default_timezone_set($settings->timezone);
        $this->syncMedia($data, ['logo', 'logo_footer', 'favicon']);
        $this->siteChromeCache->forget();
    }

    /**
     * Cập nhật Thông tin liên lạc
     */
    public function updateContact(array $data, ContactSettings $settings): void
    {
        $settings->company_name = $data['company_name'] ?? null;
        $settings->address = $data['address'] ?? null;
        $settings->phone = $data['phone'] ?? null;
        $settings->email = $data['email'] ?? null;
        $settings->tax_code = $data['tax_code'] ?? null;
        $settings->map_embed = $data['map_embed'] ?? null;
        $settings->working_hours = $data['working_hours'] ?? null;

        $settings->facebook = $data['facebook'] ?? null;
        $settings->instagram = $data['instagram'] ?? null;
        $settings->youtube = $data['youtube'] ?? null;
        $settings->tiktok = $data['tiktok'] ?? null;
        $settings->zalo = $data['zalo'] ?? null;

        $settings->save();
        $this->websiteSettings->refresh();
        $this->siteChromeCache->forget();
    }

    /**
     * Cập nhật Cấu hình SEO
     */
    public function updateSeo(array $data, SeoSettings $settings): void
    {
        $settings->seo_title = $data['seo_title'] ?? [];
        $settings->seo_description = $data['seo_description'] ?? [];
        $settings->seo_keywords = $data['seo_keywords'] ?? [];
        $settings->google_analytics_code = $data['google_analytics_code'] ?? null;

        $settings->save();
        $this->websiteSettings->refresh();
        $this->syncMedia($data, ['seo_image']);
        $this->siteChromeCache->forget();
    }

    /**
     * Cập nhật cấu hình Trang chủ
     */
    public function updateHomepage(array $data, HomepageSettings $settings): void
    {
        $settings->homepage_banner_type = 'slider';
        $settings->homepage_sections = $data['homepage_sections'] ?? [];
        $settings->homepage_section_titles = $data['homepage_section_titles'] ?? [];

        $settings->save();
    }

    /**
     * Cập nhật cấu hình Trang giới thiệu
     */
    public function updateAbout(array $data, AboutSettings $settings): void
    {
        $settings->about_story = $data['about_story'] ?? [];
        $settings->about_history = $data['about_history'] ?? [];
        $settings->about_mission = $data['about_mission'] ?? [];
        $settings->about_vision = $data['about_vision'] ?? [];
        $settings->about_core_values = $data['about_core_values'] ?? [];

        $settings->save();
        $this->syncMedia($data, ['about_image']);
    }

    /**
     * Cập nhật cấu hình Media & Banner mặc định
     */
    public function updateMedia(array $data, MediaSettings $settings): void
    {
        $settings->media_allowed_extensions = $data['media_allowed_extensions'] ?? 'jpg,jpeg,png,webp,gif,pdf,doc,docx,mp4,webm,mov';
        $settings->media_max_size = (int) ($data['media_max_size'] ?? 10);
        $settings->media_webp_conversion = isset($data['media_webp_conversion']);
        $settings->media_quality = (int) ($data['media_quality'] ?? 100);

        $settings->save();
        $this->syncMedia($data, ['default_product_banner', 'default_promotion_banner', 'default_post_banner']);
        $this->siteChromeCache->forget();
    }

    /** Cập nhật các menu được gán cho từng vị trí ngoài website. */
    public function updateMenu(array $data, MenuSettings $settings): void
    {
        foreach (['header_menu_id', 'mega_menu_id', 'footer_menu_1_id', 'footer_menu_2_id'] as $field) {
            $settings->{$field} = filled($data[$field] ?? null) ? (int) $data[$field] : null;
        }

        $settings->save();
        $this->siteChromeCache->forget();
    }

    /** Đồng bộ các ảnh cấu hình vào collection single-file của Spatie Media Library. */
    private function syncMedia(array $data, array $collections): void
    {
        $assets = SiteAsset::current();
        foreach ($collections as $collection) {
            $this->mediaService->syncSingle(
                $assets,
                $collection,
                $data[$collection] ?? null,
                (bool) ($data[$collection.'_remove'] ?? false),
            );
        }
    }
}
