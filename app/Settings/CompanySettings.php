<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CompanySettings extends Settings
{
    public ?string $company_name;

    public ?string $tax_code;

    public static function group(): string
    {
        return 'company';
    }
}
