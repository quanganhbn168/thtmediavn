<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'name', 'sku', 'barcode', 'price', 'compare_price', 'stock', 'weight', 'image', 'is_default', 'is_active'];

    protected $casts = ['price' => 'decimal:2', 'compare_price' => 'decimal:2', 'stock' => 'integer', 'weight' => 'integer', 'is_default' => 'boolean', 'is_active' => 'boolean'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): BelongsToMany
    {
        return $this->belongsToMany(ProductOptionValue::class, 'product_variant_option_values')->with('option');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function mtdSource(): HasOne
    {
        return $this->hasOne(MtdVariantSource::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        $flashPrice = $this->product?->flashSaleItems
            ->first(fn (FlashSaleProduct $item): bool => $item->flashSale?->isRunning() && (
                is_null($item->product_variant_id) || (int) $item->product_variant_id === (int) $this->id
            ))
            ?->sale_price;

        return (float) ($flashPrice ?? $this->price ?? 0);
    }
}
