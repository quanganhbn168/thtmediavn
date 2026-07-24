<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class HomepageSettings extends Settings
{
    public ?string $homepage_banner_type; // slider, video, static_image
    public array $homepage_sections;
    public array $homepage_section_titles;

    public static function group(): string
    {
        return 'homepage';
    }
}
