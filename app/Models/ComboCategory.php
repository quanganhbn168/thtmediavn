<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComboCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'image', 'seo_title', 'seo_description', 'sort_order', 'is_active'];

    protected $casts = ['sort_order' => 'integer', 'is_active' => 'boolean'];

    public function combos(): HasMany
    {
        return $this->hasMany(Combo::class)->orderBy('sort_order');
    }
}
