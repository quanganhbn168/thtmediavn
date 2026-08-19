<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class UploadSettings extends Settings
{
    public ?string $media_allowed_extensions;

    public int $media_max_size;

    public bool $media_webp_conversion;

    public int $media_quality;

    public static function group(): string
    {
        return 'upload';
    }
}
