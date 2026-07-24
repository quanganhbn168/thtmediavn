<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashSaleProduct extends Model
{
    public $timestamps = false;
    protected $fillable = ['flash_sale_id', 'product_id', 'product_variant_id', 'sale_price', 'discount_type', 'discount_value', 'quantity', 'sold'];
    protected $casts = ['sale_price' => 'decimal:2', 'discount_value' => 'decimal:2', 'quantity' => 'integer', 'sold' => 'integer'];
    public function flashSale(): BelongsTo { return $this->belongsTo(FlashSale::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}
