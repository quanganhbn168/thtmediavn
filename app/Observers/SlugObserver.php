<?php

namespace App\Observers;

use App\Models\Slug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugObserver
{
    /**
     * Chuẩn hóa slug vật lý và bảo đảm không trùng trước khi model được ghi.
     */
    public function saving(Model $model): void
    {
        if (!method_exists($model, 'getSlugSourceKey') || $model->getSlugSourceKey() !== 'slug') {
            return;
        }

        $value = $model->getAttribute('slug') ?: $model->getAttribute('name');
        $slug = Str::slug((string) $value);
        if ($slug === '') {
            return;
        }

        $model->setAttribute(
            'slug',
            $this->makeUniqueSlug($slug, $model, app()->getLocale()),
        );
    }

    /**
     * Handle the model "saved" event.
     */
    public function saved(Model $model): void
    {
        $sourceField = method_exists($model, 'getSlugSourceKey') 
            ? $model->getSlugSourceKey() 
            : ($model->slugSource ?? 'name');

        // Kiểm tra xem model có sử dụng Spatie Translatable không
        if (method_exists($model, 'getTranslations')) {
            $translations = $model->getTranslations($sourceField) ?: [];
            
            // Lấy danh sách locale hiện có của slug trong DB để so sánh
            $existingSlugs = $model->slugs()->pluck('id', 'locale')->toArray();
            $processedLocales = [];

            foreach ($translations as $locale => $value) {
                if (empty($value)) {
                    continue;
                }

                $slugText = Str::slug($value);
                if (empty($slugText)) {
                    continue;
                }

                // Đảm bảo slug là độc nhất (unique) trong ngôn ngữ đó
                $finalSlug = $this->makeUniqueSlug($slugText, $model, $locale);

                if (isset($existingSlugs[$locale])) {
                    // Cập nhật slug hiện tại
                    $model->slugs()->where('locale', $locale)->update([
                        'slug' => $finalSlug,
                    ]);
                } else {
                    // Tạo slug mới
                    $model->slugs()->create([
                        'slug' => $finalSlug,
                        'locale' => $locale,
                    ]);
                }
                $processedLocales[] = $locale;
            }

            // Xóa các slug của những locale không còn tồn tại trong bản dịch
            $model->slugs()->whereNotIn('locale', $processedLocales)->delete();
        } else {
            // Trường hợp không đa ngôn ngữ, lấy giá trị đơn
            $value = $model->getAttribute($sourceField);
            if (!empty($value)) {
                $slugText = Str::slug($value);
                $locale = app()->getLocale();
                $finalSlug = $this->makeUniqueSlug($slugText, $model, $locale);

                $model->slugs()->updateOrCreate(
                    ['locale' => $locale],
                    ['slug' => $finalSlug]
                );
            }
        }
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $model->slugs()->delete();
    }

    /**
     * Generate a unique slug by appending counters if already exists.
     */
    protected function makeUniqueSlug(string $slug, Model $model, string $locale): string
    {
        $originalSlug = $slug;
        $count = 1;

        while (true) {
            $query = Slug::where('slug', $slug)
                ->where('locale', $locale);

            // Loại trừ chính bản ghi hiện tại khi đang cập nhật
            if ($model->exists) {
                $query->where(function ($q) use ($model) {
                    $q->where('sluggable_type', '!=', $model->getMorphClass())
                      ->orWhere('sluggable_id', '!=', $model->getKey());
                });
            }

            $physicalSlugExists = false;
            if (method_exists($model, 'getSlugSourceKey') && $model->getSlugSourceKey() === 'slug') {
                $physicalQuery = $model->newQueryWithoutScopes()->where('slug', $slug);
                if ($model->exists) {
                    $physicalQuery->whereKeyNot($model->getKey());
                }
                $physicalSlugExists = $physicalQuery->exists();
            }

            if (!$query->exists() && !$physicalSlugExists) {
                return $slug;
            }

            $slug = $originalSlug . '-' . $count;
            $count++;
        }
    }
}
