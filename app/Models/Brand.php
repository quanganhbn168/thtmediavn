<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'logo', 'website', 'sort_order', 'is_featured', 'is_active'];
    protected $casts = ['sort_order' => 'integer', 'is_featured' => 'boolean', 'is_active' => 'boolean'];
    public function products(): HasMany { return $this->hasMany(Product::class); }
}
