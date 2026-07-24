<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContactSettings extends Settings
{
    public ?string $company_name;
    public ?string $address;
    public ?string $phone;
    public ?string $email;
    public ?string $tax_code;
    public ?string $map_embed;
    public ?string $working_hours;
    public ?string $facebook;
    public ?string $instagram;
    public ?string $youtube;
    public ?string $tiktok;
    public ?string $zalo;

    public static function group(): string
    {
        return 'contact';
    }
}
