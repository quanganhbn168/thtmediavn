<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MtdVariantSource extends Model
{
    protected $fillable = [
        'mtd_product_source_id',
        'product_variant_id',
        'external_id',
        'source_sku',
        'source_available',
        'payload_hash',
        'last_synced_at',
    ];

    protected $casts = [
        'source_available' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function productSource(): BelongsTo
    {
        return $this->belongsTo(MtdProductSource::class, 'mtd_product_source_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
