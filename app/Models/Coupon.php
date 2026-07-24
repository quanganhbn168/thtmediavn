<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = ['code', 'name', 'type', 'value', 'max_discount', 'minimum_order', 'usage_limit', 'usage_limit_per_user', 'used_count', 'starts_at', 'ends_at', 'is_active'];
    protected $casts = ['value' => 'decimal:2', 'max_discount' => 'decimal:2', 'minimum_order' => 'decimal:2', 'usage_limit' => 'integer', 'usage_limit_per_user' => 'integer', 'used_count' => 'integer', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];
    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query;
    }
    public function products(): BelongsToMany { return $this->belongsToMany(Product::class); }
    public function categories(): BelongsToMany { return $this->belongsToMany(ProductCategory::class); }
    public function usages(): HasMany { return $this->hasMany(CouponUsage::class); }
    public function isAvailable(float $subtotal, ?int $userId = null): bool
    {
        $available = $this->is_active
            && (! $this->starts_at || now()->gte($this->starts_at))
            && (! $this->ends_at || now()->lte($this->ends_at))
            && (! $this->usage_limit || $this->used_count < $this->usage_limit)
            && $subtotal >= (float) $this->minimum_order;
        if (! $available || ! $userId || ! $this->usage_limit_per_user) return $available;
        return $this->usages()->where('user_id', $userId)->count() < $this->usage_limit_per_user;
    }
    public function discountFor(float $subtotal): float
    {
        if ($this->type === 'free_shipping') return 0;
        $discount = $this->type === 'percent' ? $subtotal * ((float) $this->value / 100) : (float) $this->value;
        return min($subtotal, $this->max_discount ? min($discount, (float) $this->max_discount) : $discount);
    }
}
