<?php

namespace App\Models;

use App\Enums\SliderType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Slider extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'key',
        'is_active',
    ];

    public $translatable = [
        'name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SliderItem::class)->orderBy('sort_order');
    }

    public function scopeOfType(Builder $query, SliderType $type): Builder
    {
        return $query->where('key', $type->value);
    }

    public static function activeFor(SliderType $type): ?self
    {
        return self::query()
            ->ofType($type)
            ->where('is_active', true)
            ->with(['items' => fn ($query) => $query->where('is_active', true)->with('media')->orderBy('sort_order')])
            ->first();
    }

    public function getTypeLabelAttribute(): string
    {
        return SliderType::tryFrom((string) $this->key)?->label() ?? (string) $this->key;
    }
}
