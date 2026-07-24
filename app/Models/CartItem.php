<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'product_variant_id', 'quantity'];
    protected $casts = ['quantity' => 'integer'];
    public function cart(): BelongsTo { return $this->belongsTo(Cart::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
    public function getUnitPriceAttribute(): float
    {
        if ($this->variant) return (float) $this->variant->effective_price;

        $defaultVariant = $this->product->default_variant;
        if ($defaultVariant) return (float) $defaultVariant->effective_price;

        return (float) $this->product->current_price;
    }
}
