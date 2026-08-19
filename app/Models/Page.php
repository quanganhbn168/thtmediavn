<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Models\Concerns\HasComments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Page extends Model implements HasMedia
{
    use HasComments, HasSlug, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'template',
        'name',
        'sub_title',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'is_active',
        'sort_order',
        'published_at',
    ];

    public $translatable = [
        'name',
        'sub_title',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $builder) => $builder->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
