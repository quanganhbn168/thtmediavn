<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (['about_section_titles', 'about_team', 'about_facilities', 'about_faqs'] as $property) {
            $this->migrator->deleteIfExists('about.'.$property);
        }
    }
};
