<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->update('media.media_allowed_extensions', static function (?string $extensions): string {
            $values = collect(explode(',', (string) $extensions))
                ->map(fn (string $extension): string => strtolower(ltrim(trim($extension), '.')))
                ->filter()
                ->merge(['mp4', 'webm', 'mov'])
                ->unique()
                ->implode(',');

            return $values;
        });
    }
};
