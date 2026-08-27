<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PricingPlan extends Model
{
    protected $fillable = [
        'name', 'summary', 'price', 'price_amount', 'is_price_from', 'price_unit', 'price_note', 'features', 'is_featured', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price_amount' => 'decimal:2',
            'is_price_from' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->orderBy('sort_order');
    }

    public function getDisplayPriceAttribute(): string
    {
        if ($this->price_amount !== null) {
            $price = number_format((float) $this->price_amount, 0, ',', '.') . 'đ';

            if ($this->is_price_from) {
                $price = 'Từ ' . $price;
            }

            return $this->price_unit ? $price . '/' . $this->price_unit : $price;
        }

        return trim((string) ($this->price ?: 'Liên hệ'));
    }
}
