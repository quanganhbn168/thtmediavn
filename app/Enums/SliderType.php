<?php

namespace App\Enums;

enum SliderType: string
{
    case HomepageHero = 'homepage_hero';
    case HomeGalleryPrimary = 'home_collection_1';
    case HomeGallerySecondary = 'home_collection_2';
    case HomeAdvertisementLeft = 'home_ad_left';
    case HomeAdvertisementRight = 'home_ad_right';
    case HomePromotion = 'home_promotion';
    case ProductsBanner = 'products_banner';
    case PostsBanner = 'posts_banner';
    case PromotionBanner = 'promotion_banner';
    case AboutBanner = 'about_banner';
    case ContactBanner = 'contact_banner';
    case ContactAdvertisement = 'contact_advertisement';

    public function label(): string
    {
        return match ($this) {
            self::HomepageHero => 'Slider chính trang chủ',
            self::HomeGalleryPrimary => 'Bộ sưu tập ảnh thực tế 1',
            self::HomeGallerySecondary => 'Bộ sưu tập ảnh thực tế 2',
            self::HomeAdvertisementLeft => 'Ảnh quảng cáo trái trang chủ',
            self::HomeAdvertisementRight => 'Ảnh quảng cáo phải trang chủ',
            self::HomePromotion => 'CTA / khuyến mãi nổi bật trang chủ',
            self::ProductsBanner => 'Banner trang sản phẩm',
            self::PostsBanner => 'Banner trang Tin tức',
            self::PromotionBanner => 'Banner chương trình khuyến mãi',
            self::AboutBanner => 'Banner trang Giới thiệu',
            self::ContactBanner => 'Banner trang Liên hệ',
            self::ContactAdvertisement => 'Ảnh quảng cáo trang Liên hệ',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::HomepageHero => 'Ảnh lớn đầu trang chủ, hỗ trợ tiêu đề, phụ đề và tối đa hai nút.',
            self::HomeGalleryPrimary, self::HomeGallerySecondary => 'Ảnh thực tế dùng trong khối bộ sưu tập masonry trên trang chủ.',
            self::HomeAdvertisementLeft => 'Vị trí quảng cáo cột trái hoặc nửa trái trên trang chủ.',
            self::HomeAdvertisementRight => 'Vị trí quảng cáo cột phải hoặc nửa phải trên trang chủ.',
            self::HomePromotion => 'CTA toàn chiều rộng trên trang chủ, hỗ trợ ảnh nền, tiêu đề, phụ đề và tối đa hai nút.',
            self::ProductsBanner => 'Ảnh banner đầu trang danh sách sản phẩm.',
            self::PostsBanner => 'Ảnh banner đầu trang danh sách Tin tức/Cẩm nang.',
            self::PromotionBanner => 'Ảnh banner dùng cho chương trình khuyến mãi.',
            self::AboutBanner => 'Ảnh banner đầu trang Giới thiệu.',
            self::ContactBanner => 'Ảnh banner đầu trang Liên hệ.',
            self::ContactAdvertisement => 'Ảnh quảng cáo bổ sung trên trang Liên hệ.',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $type) {
            $options[$type->value] = $type->label();
        }

        return $options;
    }
}
