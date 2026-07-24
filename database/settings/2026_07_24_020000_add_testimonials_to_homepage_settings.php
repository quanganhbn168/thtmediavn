<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->update('homepage.homepage_sections', function ($sections): array {
            $sections = is_array($sections) ? $sections : [];

            return array_values(array_unique([...$sections, 'testimonials']));
        });

        $this->migrator->update('homepage.homepage_section_titles', function ($titles): array {
            $titles = is_array($titles) ? $titles : [];
            $titles['testimonials'] ??= ['vi' => 'Khách hàng nói gì về chúng tôi'];

            return $titles;
        });
    }
};
