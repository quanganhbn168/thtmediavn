<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('about.about_history', ['vi' => '']);
        $this->migrator->add('about.about_core_values', ['vi' => '']);
    }
};
