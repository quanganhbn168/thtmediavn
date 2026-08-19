<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Models\Concerns\HasComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Service extends Model implements HasMedia
{
    use HasComments, HasSlug, HasTranslations, InteractsWithMedia, SoftDeletes;

    public const GROUPS = [
        'production' => 'Sản xuất hình ảnh',
        'marketing' => 'Truyền thông và marketing',
        'event_brand' => 'Sự kiện và thương hiệu',
    ];

    protected $fillable = [
        'thumbnail_id', 'banner_id', 'share_image_id', 'group', 'service_category_id', 'name', 'summary', 'intro', 'problems', 'audiences', 'work_items',
        'deliverables', 'benefits', 'process_steps', 'faqs', 'video_url', 'seo_title',
        'seo_description', 'seo_keywords', 'is_featured', 'is_active', 'sort_order',
    ];

    public array $translatable = [
        'name', 'summary', 'intro', 'problems', 'audiences', 'work_items',
        'deliverables', 'benefits', 'process_steps', 'faqs', 'seo_title',
        'seo_description', 'seo_keywords',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->orderBy('sort_order');
    }

    public function pricingPlans(): BelongsToMany
    {
        return $this->belongsToMany(PricingPlan::class)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order');
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'thumbnail_id');
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'banner_id');
    }

    public function shareImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'share_image_id');
    }

    public function getSlugSourceKey(): string
    {
        return 'name';
    }

    public function relatedServices(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'related_services', 'service_id', 'related_service_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function registerMediaCollections(): void
    {
        foreach (['thumbnail', 'banner', 'share_image'] as $collection) {
            $this->addMediaCollection($collection)->singleFile()->useDisk('public_media');
        }
    }
}
