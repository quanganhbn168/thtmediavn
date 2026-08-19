<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Contact extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'service_id', 'name', 'phone', 'email', 'company', 'subject', 'budget',
        'timeline', 'message', 'status', 'admin_notes',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('brief_attachment')->singleFile()->useDisk('public_media');
    }
}
