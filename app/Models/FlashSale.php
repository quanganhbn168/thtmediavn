<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlashSale extends Model
{
    protected $fillable = ['name', 'starts_at', 'ends_at', 'is_active'];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];
    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query;
    }
    public function items(): HasMany { return $this->hasMany(FlashSaleProduct::class); }
    public function isRunning(): bool { return $this->is_active && now()->between($this->starts_at, $this->ends_at); }
}
