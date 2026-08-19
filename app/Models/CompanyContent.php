<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class CompanyContent extends Model implements HasMedia
{
    use HasSlug, HasTranslations, InteractsWithMedia, SoftDeletes;

    public const TYPES = [
        'article' => 'Bài viết công ty',
        'team' => 'Đội ngũ',
        'facility' => 'Cơ sở vật chất',
        'faq' => 'Câu hỏi thường gặp',
    ];

    protected $fillable = [
        'image_id',
        'banner_id',
        'share_image_id',
        'type',
        'slug',
        'title',
        'summary',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'is_featured',
        'is_active',
        'sort_order',
        'published_at',
    ];

    public array $translatable = [
        'title',
        'summary',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $content): void {
            if ($content->getAttribute('sort_order') === null) {
                $content->setAttribute('sort_order', self::nextSortOrder());
            }
        });
    }

    public static function nextSortOrder(): int
    {
        return ((int) static::query()->max('sort_order')) + 1;
    }

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $builder): Builder => $builder
                ->whereNull('published_at')
                ->orWhere('published_at', '<=', now()));
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? self::TYPES['article'];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'banner_id');
    }

    public function shareImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'share_image_id');
    }

    /**
     * Slugs are generated from the translated article title.
     */
    public function getSlugSourceKey(): string
    {
        return 'title';
    }

    public function routeSlug(?string $locale = null): string
    {
        return $this->getSlug($locale) ?: (string) $this->slug;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('company_image')
            ->singleFile()
            ->useDisk('public_media');

        $this->addMediaCollection('company_banner')
            ->singleFile()
            ->useDisk('public_media');

        $this->addMediaCollection('share_image')
            ->singleFile()
            ->useDisk('public_media');
    }
}
