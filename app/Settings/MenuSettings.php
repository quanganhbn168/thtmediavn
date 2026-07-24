<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MenuSettings extends Settings
{
    public ?int $header_menu_id;

    public ?int $mega_menu_id;

    public ?int $footer_menu_1_id;

    public ?int $footer_menu_2_id;

    public static function group(): string
    {
        return 'menu';
    }
}
