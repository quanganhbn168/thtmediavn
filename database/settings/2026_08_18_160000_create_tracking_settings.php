<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Move values from the first draft, where tracking was incorrectly
        // registered under the SEO group, without losing existing codes.
        if ($this->migrator->exists('seo.google_analytics_code')) {
            $this->migrator->rename('seo.google_analytics_code', 'tracking.google_analytics_code');
        } else {
            $this->migrator->add('tracking.google_analytics_code', null);
        }

        if ($this->migrator->exists('seo.meta_pixel_code')) {
            $this->migrator->rename('seo.meta_pixel_code', 'tracking.meta_pixel_code');
        } else {
            $this->migrator->add('tracking.meta_pixel_code', null);
        }
    }
};
