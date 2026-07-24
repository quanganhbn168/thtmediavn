<?php

use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (['preset', 'primary', 'secondary', 'ink', 'muted', 'surface', 'canvas', 'line'] as $property) {
            $this->migrator->deleteIfExists("theme.{$property}");
        }

        // The original 040 migration was removed with the unused admin theme UI.
        // Clear its historical record so migrate:status matches the source tree.
        DB::table('migrations')
            ->where('migration', '2026_07_24_040000_add_frontend_theme_settings')
            ->delete();
    }
};
