<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AboutSettings extends Settings
{
    public array $about_page_label; // Đa ngôn ngữ

    public array $about_page_title; // Đa ngôn ngữ

    public array $about_page_intro; // Đa ngôn ngữ

    public array $about_story; // Đa ngôn ngữ (HTML)

    public array $about_history; // Đa ngôn ngữ (HTML)

    public array $about_mission; // Đa ngôn ngữ

    public array $about_vision; // Đa ngôn ngữ

    public array $about_core_values; // Đa ngôn ngữ (HTML)

    public static function group(): string
    {
        return 'about';
    }
}
