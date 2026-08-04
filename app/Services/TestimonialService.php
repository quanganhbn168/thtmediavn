<?php

namespace App\Services;

use App\Models\Testimonial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TestimonialService
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Testimonial::query()->with('media');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('label', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%"));
        }

        return $query->orderBy('sort_order')->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function create(array $data): Testimonial
    {
        $testimonial = Testimonial::query()->create($this->payload($data));
        $this->mediaService->syncSingle($testimonial, 'testimonial_avatar', $data['avatar'] ?? null);

        return $testimonial;
    }

    public function update(Testimonial $testimonial, array $data): void
    {
        $testimonial->update($this->payload($data));
        $this->mediaService->syncSingle(
            $testimonial,
            'testimonial_avatar',
            $data['avatar'] ?? null,
            (bool) ($data['avatar_remove'] ?? false),
        );
    }

    public function delete(Testimonial $testimonial): void
    {
        $testimonial->clearMediaCollection('testimonial_avatar');
        $testimonial->delete();
    }

    private function payload(array $data): array
    {
        return [
            'name' => trim((string) $data['name']),
            'label' => filled($data['label'] ?? null) ? trim((string) $data['label']) : null,
            'content' => trim((string) $data['content']),
            'rating' => (int) ($data['rating'] ?? 5),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }
}
