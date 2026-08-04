<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Testimonial extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['name', 'label', 'content', 'rating', 'sort_order', 'is_active'];

    protected $casts = [
        'rating' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('testimonial_avatar')
            ->singleFile()
            ->useDisk('public_media');

        $this->addMediaCollection('testimonial_video')
            ->singleFile()
            ->useDisk('public_media');
    }
}
