<?php

namespace App\Services;

use App\Models\CompanyContent;
use App\Models\Slug;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyContentService
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function create(array $data): CompanyContent
    {
        return DB::transaction(function () use ($data): CompanyContent {
            $content = CompanyContent::query()->create($this->payload($data));
            $this->syncMedia($content, $data);

            return $content;
        });
    }

    public function update(CompanyContent $content, array $data): void
    {
        DB::transaction(function () use ($content, $data): void {
            $content->update($this->payload($data, $content));
            $this->syncMedia($content, $data);
        });
    }

    public function delete(CompanyContent $content): void
    {
        foreach (['company_image', 'company_banner', 'share_image'] as $collection) {
            $content->clearMediaCollection($collection);
        }

        $content->delete();
    }

    private function payload(array $data, ?CompanyContent $existing = null): array
    {
        $payload = Arr::only($data, [
            'image_id',
            'banner_id',
            'share_image_id',
            'type',
            'title',
            'summary',
            'content',
            'seo_title',
            'seo_description',
            'seo_keywords',
            'published_at',
        ]);

        $payload['type'] = array_key_exists('type', $data)
            ? (string) $data['type']
            : ($existing?->type ?: 'article');
        $payload['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['title']['vi'] ?? '', $existing);
        $payload['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $payload['is_active'] = (bool) ($data['is_active'] ?? false);
        $payload['sort_order'] = array_key_exists('sort_order', $data)
            ? (int) $data['sort_order']
            : ($existing?->sort_order ?? CompanyContent::nextSortOrder());

        return $payload;
    }

    private function uniqueSlug(mixed $value, string $title, ?CompanyContent $existing = null): string
    {
        $base = Str::slug(trim((string) $value)) ?: Str::slug($title) ?: 'noi-dung-cong-ty';
        $slug = $base;
        $suffix = 1;

        while (
            CompanyContent::query()
                ->when($existing, fn ($query): mixed => $query->whereKeyNot($existing->getKey()))
                ->where('slug', $slug)
                ->exists()
            || Slug::query()
                ->where('slug', $slug)
                ->where('locale', 'vi')
                ->when($existing, fn ($query): mixed => $query->where(function ($builder) use ($existing): void {
                    $builder
                        ->where('sluggable_type', '!=', $existing->getMorphClass())
                        ->orWhere('sluggable_id', '!=', $existing->getKey());
                }))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function syncMedia(CompanyContent $content, array $data): void
    {
        $this->mediaService->syncSingle(
            $content,
            'company_image',
            $data['image'] ?? null,
            (bool) ($data['image_remove'] ?? false),
        );

        $this->mediaService->syncSingle(
            $content,
            'company_banner',
            $data['banner'] ?? null,
            (bool) ($data['banner_remove'] ?? false),
        );

        $this->mediaService->syncSingle(
            $content,
            'share_image',
            $data['share_image'] ?? null,
            (bool) ($data['share_image_remove'] ?? false),
        );
    }
}
