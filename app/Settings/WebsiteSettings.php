<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WebsiteSettings extends Settings
{
    public bool $site_status;

    public bool $multilingual_enabled;

    public string $timezone;

    public array $site_name;

    public array $site_description;

    public array $copyright;

    public ?int $header_menu_id;

    public ?int $mega_menu_id;

    public ?int $footer_menu_1_id;

    public ?int $footer_menu_2_id;

    public static function group(): string
    {
        return 'website';
    }
}
