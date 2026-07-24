<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Facades\Route;

class MenuItem extends Model
{
    use HasTranslations;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'route',
        'target',
        'icon',
        'sort_order',
        'is_active',
    ];

    public $translatable = [
        'title',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Thuộc bộ Menu nào
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Menu cha
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Các Menu con
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->where('is_active', true)->with('childrenRecursive');
    }

    public function getHrefAttribute(): string
    {
        if ($this->url) {
            return str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://')
                ? $this->url
                : url($this->url);
        }

        return $this->route && Route::has($this->route) ? route($this->route) : '#';
    }

    public function isCurrent(): bool
    {
        return $this->href !== '#' && request()->url() === strtok($this->href, '?');
    }

    public function hasCurrentDescendant(): bool
    {
        return $this->childrenRecursive->contains(fn (self $child) => $child->isCurrent() || $child->hasCurrentDescendant());
    }
}
