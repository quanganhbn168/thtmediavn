<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends Model
{
    protected $fillable = ['name', 'slug', 'is_active', 'show_in_product_menu', 'sort_order'];
    protected $casts = ['is_active' => 'boolean', 'show_in_product_menu' => 'boolean', 'sort_order' => 'integer'];

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class)->orderBy('sort_order');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'product_category_product_attribute'
        );
    }
}
