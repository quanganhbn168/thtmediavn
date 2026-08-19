<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Client extends Model implements HasMedia
{
    use HasSlug, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name', 'industry', 'website_url', 'description', 'quote', 'quote_author',
        'is_featured', 'is_active', 'sort_order',
    ];

    public array $translatable = ['name', 'description', 'quote'];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class)->orderByDesc('completed_year');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile()->useDisk('public_media');
        $this->addMediaCollection('cover')->singleFile()->useDisk('public_media');
    }
}
