<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public bool $site_status;
    public string $timezone;
    public array $copyright; // Đa ngôn ngữ

    public array $site_name; // Đa ngôn ngữ
    public array $site_description; // Đa ngôn ngữ
    
    public static function group(): string
    {
        return 'general';
    }
}
