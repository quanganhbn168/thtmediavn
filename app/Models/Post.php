<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model implements HasMedia
{
    use HasTranslations, HasSlug, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'post_category_id',
        'name',
        'summary',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'is_featured',
        'is_active',
        'view_count',
        'created_by',
        'published_at',
    ];

    public $translatable = [
        'name',
        'summary',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'view_count' => 'integer',
        'published_at' => 'datetime',
    ];

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /**
     * Đăng ký Media Collection cho ảnh đại diện bài viết.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('post_image')
            ->singleFile()
            ->useDisk('public_media');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }
}
