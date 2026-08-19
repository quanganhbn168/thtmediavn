<?php

namespace App\Traits;

use App\Models\Slug;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSlug
{
    protected ?string $slugOverride = null;

    /**
     * Get all of the model's slugs.
     */
    public function slugs(): MorphMany
    {
        return $this->morphMany(Slug::class, 'sluggable');
    }

    /**
     * Get the slug for a specific locale or current application locale.
     */
    public function getSlug(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        if ($this->relationLoaded('slugs')) {
            return $this->slugs->firstWhere('locale', $locale)?->slug;
        }

        return $this->slugs()->where('locale', $locale)->first()?->slug;
    }

    /**
     * Attribute helper to get current locale slug.
     */
    public function getSlugAttribute(mixed $value = null): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        return $this->getSlug();
    }

    public function setSlugOverride(mixed $slug): static
    {
        $this->slugOverride = filled($slug) ? (string) $slug : null;

        return $this;
    }

    public function getSlugOverride(): ?string
    {
        return $this->slugOverride;
    }

    public function clearSlugOverride(): static
    {
        $this->slugOverride = null;

        return $this;
    }
}
