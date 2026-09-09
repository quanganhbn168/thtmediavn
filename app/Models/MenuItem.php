<?php

namespace App\Models;

use App\Services\SiteChromeCache;
use App\Support\Menus\MenuSources;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Spatie\Translatable\HasTranslations;

class MenuItem extends Model
{
    use HasTranslations;

    protected $fillable = [
        'linked_source_type', 'linked_source_id',
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

    /**
     * Cây item dành cho Builder: giữ cả liên kết đang ẩn để quản trị viên
     * vẫn có thể kéo thả, sửa hoặc xóa chúng.
     */
    public function childrenTree(): HasMany
    {
        return $this->children()->with('childrenTree');
    }

    public function getHrefAttribute(): string
    {
        if ($this->linked_source_type && $this->linked_source_id) {
            return MenuSources::url($this->linked_source_type, (int) $this->linked_source_id);
        }
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

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            if (! $item->menu_id && $item->parent_id) {
                $item->menu_id = self::query()->whereKey($item->parent_id)->value('menu_id');
            }
        });
        static::saved(fn () => app(SiteChromeCache::class)->forget());
        static::deleted(fn () => app(SiteChromeCache::class)->forget());
    }
}
