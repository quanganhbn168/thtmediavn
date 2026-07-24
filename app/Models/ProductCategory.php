<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    protected $fillable = ['parent_id', 'name', 'slug', 'description', 'image', 'seo_title', 'seo_description', 'sort_order', 'is_featured', 'is_home', 'is_active'];
    protected $casts = ['sort_order' => 'integer', 'is_featured' => 'boolean', 'is_home' => 'boolean', 'is_active' => 'boolean'];

    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order'); }
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(ProductOption::class, 'product_category_product_option');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_category_product_attribute');
    }

    public function products(): HasMany { return $this->hasMany(Product::class); }
}
