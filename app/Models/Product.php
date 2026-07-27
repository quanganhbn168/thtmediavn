<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasSlug, InteractsWithMedia, SoftDeletes;

    protected $fillable = ['product_category_id', 'brand_id', 'name', 'slug', 'summary', 'description', 'ingredients', 'usage', 'sold_count', 'status', 'variant_selection_mode', 'track_inventory', 'allow_preorder', 'is_featured', 'is_home', 'is_active', 'seo_title', 'seo_description', 'published_at'];

    protected $casts = ['sold_count' => 'integer', 'track_inventory' => 'boolean', 'allow_preorder' => 'boolean', 'is_featured' => 'boolean', 'is_home' => 'boolean', 'is_active' => 'boolean', 'published_at' => 'datetime'];

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query;
    }

    public function isVisibleOnSite(): bool
    {
        return $this->is_active && ! $this->trashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(ProductOption::class)->with('values')->orderBy('sort_order');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_attribute_value_product');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function flashSaleItems(): HasMany
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    public function mtdSource(): HasOne
    {
        return $this->hasOne(MtdProductSource::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_images')->useDisk('public_media');
    }

    public function getSlugSourceKey(): string
    {
        return 'slug';
    }

    public function getImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('product_images') ?: asset('images/no-image.png');
    }

    public function getDefaultVariantAttribute(): ?ProductVariant
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->firstWhere(fn (ProductVariant $variant) => $variant->is_active && $variant->is_default)
                ?: $this->variants->firstWhere('is_active', true)
                ?: $this->variants->first();
        }

        return $this->variants()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first()
            ?: $this->variants()->where('is_active', true)->first()
            ?: $this->variants()->first();
    }

    public function getCurrentPriceAttribute(): float
    {
        $variant = $this->default_variant;
        if (! $variant) {
            return 0.0;
        }

        return (float) $variant->effective_price;
    }

    public function getCurrentComparePriceAttribute(): ?float
    {
        $variant = $this->default_variant;
        if (! $variant) {
            return null;
        }

        return $variant->compare_price !== null ? (float) $variant->compare_price : null;
    }
}
