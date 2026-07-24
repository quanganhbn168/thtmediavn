<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->update('homepage.homepage_section_titles', function ($titles): array {
            $titles = is_array($titles) ? $titles : [];
            $titles['posts'] = ['vi' => 'Blog'];

            return $titles;
        });
    }
};
