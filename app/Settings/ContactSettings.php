<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContactSettings extends Settings
{
    public ?string $address;

    public ?string $phone;

    public array $phones = [];

    public array $branches = [];

    public ?string $email;

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
