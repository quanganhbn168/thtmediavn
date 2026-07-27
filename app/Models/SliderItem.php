<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class SliderItem extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'slider_id',
        'title',
        'sub_title',
        'buttons',
        'sort_order',
        'is_active',
    ];

    public $translatable = [
        'title',
        'sub_title',
    ];

    protected $casts = [
        'buttons' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function slider(): BelongsTo
    {
        return $this->belongsTo(Slider::class);
    }

    /**
     * Đăng ký Media Collection cho ảnh nền slide.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('slide_image')
            ->singleFile()
            ->useDisk('public_media');

        $this->addMediaCollection('slide_image_mobile')
            ->singleFile()
            ->useDisk('public_media');
    }
}
