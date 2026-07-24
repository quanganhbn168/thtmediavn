<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Slider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SliderService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Slider::query()->withCount('items');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $languageCodes = Language::getActiveLanguages()->pluck('code');

            if ($languageCodes->isEmpty()) {
                $languageCodes = collect(['vi', 'en']);
            }

            $query->where(function ($builder) use ($languageCodes, $search) {
                $builder->where('key', 'like', "%{$search}%");

                foreach ($languageCodes as $code) {
                    $builder->orWhere("name->{$code}", 'like', "%{$search}%");
                }
            });
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        if (! empty($filters['type'])) {
            $query->where('key', $filters['type']);
        }

        return $query
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function items(Slider $slider): Collection
    {
        return $slider->items()->with('media')->get();
    }

    /**
     * Create a new Slider.
     */
    public function create(array $data): Slider
    {
        return Slider::create($this->payload($data));
    }

    /**
     * Update an existing Slider.
     */
    public function update(Slider $slider, array $data): void
    {
        $slider->update($this->payload($data));
    }

    /**
     * Delete a Slider and its items.
     */
    public function delete(Slider $slider): void
    {
        $slider->loadMissing('items.media');

        foreach ($slider->items as $item) {
            $item->clearMediaCollection('slide_image');
        }

        $slider->delete();
    }

    private function payload(array $data): array
    {
        return [
            'name' => $data['name'],
            'key' => $data['key'],
            'is_active' => (bool) $data['is_active'],
        ];
    }
}
