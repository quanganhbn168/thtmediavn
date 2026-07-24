<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MtdProductSource extends Model
{
    protected $fillable = [
        'product_id',
        'external_id',
        'source_url',
        'payload_hash',
        'source_stock_status',
        'is_adopted',
        'scraped_at',
        'last_synced_at',
    ];

    protected $casts = [
        'is_adopted' => 'boolean',
        'scraped_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MtdVariantSource::class);
    }
}
