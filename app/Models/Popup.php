<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Popup extends Model
{
    public const DISPLAY_SCOPES = [
        'all' => 'Toàn website',
        'home' => 'Chỉ trang chủ',
    ];

    protected $fillable = [
        'image_id',
        'title',
        'subtitle',
        'content',
        'button_text',
        'button_url',
        'button_target_blank',
        'display_scope',
        'display_delay',
        'show_once',
        'is_active',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'button_target_blank' => 'boolean',
            'display_delay' => 'integer',
            'show_once' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $builder) use ($now): void {
                $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $builder) use ($now): void {
                $builder->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function scopeForPage(Builder $query, bool $isHome): Builder
    {
        return $query->where(function (Builder $builder) use ($isHome): void {
            $builder->where('display_scope', 'all');

            if ($isHome) {
                $builder->orWhere('display_scope', 'home');
            }
        });
    }

    public function safeButtonUrl(): ?string
    {
        $url = trim((string) $this->button_url);

        if ($url === '' || str_starts_with($url, '//')) {
            return null;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return $url;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }
}
