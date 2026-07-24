<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Menu extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'location',
        'is_active',
    ];

    public $translatable = [
        'name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Lấy các items cấp cha (parent_id = null) của Menu này
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    /**
     * Lấy tất cả items của Menu này
     */
    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }
}
