<?php

use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {

        $legacy = [];

        foreach (['google_analytics_id', 'google_analytics_code', 'google_tag_manager_id', 'google_tag_manager_head_code', 'google_tag_manager_body_code', 'meta_pixel_code', 'tiktok_pixel_code', 'head_code', 'body_open_code', 'body_close_code'] as $field) {
            if (! $this->migrator->exists('tracking.'.$field)) {
                $value = null;
                if (isset($legacy[$field])) {
                    $payload = DB::table('settings')->where('group', 'website')->where('name', $legacy[$field])->value('payload');
                    $value = $payload !== null ? json_decode($payload, true) : null;
                }
                $this->migrator->add('tracking.'.$field, $value);
            }
        }
    }
};
