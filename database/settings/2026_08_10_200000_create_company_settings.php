<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('company.company_name', 'THT MEDIA VN');
        $this->migrator->add('company.tax_code', null);
    }
};
