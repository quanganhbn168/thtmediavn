<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('upload.media_allowed_extensions', 'jpg,jpeg,png,webp,gif,pdf,doc,docx,mp4,webm,mov');
        $this->migrator->add('upload.media_max_size', 10);
        $this->migrator->add('upload.media_webp_conversion', true);
        $this->migrator->add('upload.media_quality', 100);
    }
};
