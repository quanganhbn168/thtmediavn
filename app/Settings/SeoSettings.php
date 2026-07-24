<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SeoSettings extends Settings
{
    public array $seo_title; // Đa ngôn ngữ
    public array $seo_description; // Đa ngôn ngữ
    public array $seo_keywords; // Đa ngôn ngữ
    public ?string $google_analytics_code;

    public static function group(): string
    {
        return 'seo';
    }
}
