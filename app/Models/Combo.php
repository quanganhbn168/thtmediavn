<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Combo extends Model implements HasMedia
{
    use HasSlug, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'combo_category_id', 'name', 'slug', 'summary', 'description', 'price', 'compare_price',
        'sold_count', 'status', 'allow_preorder', 'is_featured', 'is_active', 'seo_title',
        'seo_description', 'published_at', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'sold_count' => 'integer',
        'allow_preorder' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComboCategory::class, 'combo_category_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ComboItem::class)->orderBy('sort_order');
    }

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('status', 'active')
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function isVisibleOnSite(): bool
    {
        return $this->is_active
            && $this->status === 'active'
            && ! $this->trashed()
            && (! $this->published_at || $this->published_at->isPast());
    }

    public function availableQuantity(): ?int
    {
        $this->loadMissing('items.product.variants', 'items.variant');
        if ($this->items->isEmpty()) {
            return 0;
        }

        $limits = [];
        foreach ($this->items as $item) {
            $product = $item->product;
            $variant = $item->variant ?: $product?->default_variant;
            if (! $product || ! $product->isVisibleOnSite() || ! $variant || ! $variant->is_active) {
                return 0;
            }

            if (! $product->track_inventory || $product->allow_preorder) {
                continue;
            }

            $limits[] = intdiv(max(0, (int) $variant->stock), max(1, (int) $item->quantity));
        }

        return $limits === [] ? null : min($limits);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('combo_images')->useDisk('public_media');
    }

    public function getSlugSourceKey(): string
    {
        return 'slug';
    }

    public function getImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('combo_images') ?: asset('images/no-image.png');
    }
}
