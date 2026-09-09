<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TrackingSettings extends Settings
{
    public ?string $google_analytics_code = null;

    public ?string $google_tag_manager_head_code = null;

    public ?string $google_tag_manager_body_code = null;

    public ?string $meta_pixel_code = null;

    public ?string $tiktok_pixel_code = null;

    public ?string $head_code = null;

    public ?string $body_open_code = null;

    public ?string $body_close_code = null;

    public static function group(): string
    {
        return 'tracking';
    }
}
