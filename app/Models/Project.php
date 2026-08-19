<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Models\Concerns\HasComments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Project extends Model implements HasMedia
{
    use HasComments, HasSlug, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'cover_id', 'share_image_id', 'client_id', 'project_category_id', 'name', 'summary', 'context', 'solution', 'work_items', 'results',
        'industry', 'completed_year', 'video_url', 'seo_title', 'seo_description',
        'seo_keywords', 'is_featured', 'is_active', 'sort_order', 'published_at',
    ];

    public array $translatable = [
        'name', 'summary', 'context', 'solution', 'work_items', 'results',
        'seo_title', 'seo_description', 'seo_keywords',
    ];

    protected function casts(): array
    {
        return [
            'completed_year' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $builder) => $builder->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_id');
    }

    public function shareImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'share_image_id');
    }

    public function getSlugSourceKey(): string
    {
        return 'name';
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->orderBy('sort_order');
    }

    public function galleryMedia(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'project_gallery_media', 'project_id', 'media_id')
            ->withPivot('sort_order')
            ->orderBy('project_gallery_media.sort_order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->useDisk('public_media');
        $this->addMediaCollection('gallery')->useDisk('public_media');
        $this->addMediaCollection('share_image')->singleFile()->useDisk('public_media');
    }
}
