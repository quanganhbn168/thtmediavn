<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemComboComponent extends Model
{
    protected $fillable = [
        'order_item_id', 'combo_id', 'component_product_id', 'component_variant_id',
        'component_product_name', 'component_variant_name', 'sku', 'quantity', 'stock_reserved',
    ];

    protected $casts = ['quantity' => 'integer', 'stock_reserved' => 'boolean'];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class);
    }

    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id')->withTrashed();
    }

    public function componentVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'component_variant_id');
    }
}
