<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOption extends Model
{
    protected $fillable = ['name', 'slug', 'display_type', 'sort_order', 'is_active'];
    protected $casts = ['sort_order' => 'integer', 'is_active' => 'boolean'];
    public function values(): HasMany { return $this->hasMany(ProductOptionValue::class)->orderBy('sort_order'); }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'product_category_product_option'
        );
    }

    public function products(): BelongsToMany { return $this->belongsToMany(Product::class); }
}
