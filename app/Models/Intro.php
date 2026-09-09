<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Intro extends Model
{
    protected $guarded = [];

    protected $attributes = ['kind' => 'article', 'is_active' => false];

    protected $casts = ['is_active' => 'boolean', 'published_at' => 'datetime'];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('kind', 'article')->where('is_active', true)
            ->whereNotNull('slug')->where('slug', '!=', '')
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('kind', 'block')->where('is_active', true)->orderBy('sort_order');
    }

    public function getUrlAttribute(): string
    {
        return route('intros.show', ['slug' => $this->slug]);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }
}
