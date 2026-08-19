<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Service;
use App\Support\StructuredContent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ServiceService
{
    private const LINE_FIELDS = ['problems', 'audiences', 'work_items', 'deliverables', 'benefits', 'process_steps'];

    public function __construct(private readonly MediaService $mediaService) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Service::query()->withCount('projects');
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $languages = Language::getActiveLanguages()->pluck('code')->whenEmpty(fn ($items) => $items->push('vi'));
            $query->where(function ($builder) use ($languages, $search): void {
                foreach ($languages as $language) {
                    $builder->orWhere("name->{$language}", 'like', "%{$search}%")
                        ->orWhere("summary->{$language}", 'like', "%{$search}%");
                }
            });
        }
        if (filled($filters['group'] ?? null)) {
            $query->where('group', $filters['group']);
        }
        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->orderBy('sort_order')->orderBy('id')->paginate((int) ($filters['per_page'] ?? 10))->withQueryString();
    }

    public function create(array $data): Service
    {
        return DB::transaction(function () use ($data): Service {
            $service = new Service($this->payload($data));
            $service->setSlugOverride($data['slug'] ?? null)->save();
            $this->syncRelationsAndMedia($service, $data);

            return $service;
        });
    }

    public function update(Service $service, array $data): void
    {
        DB::transaction(function () use ($service, $data): void {
            $service->setSlugOverride($data['slug'] ?? null);
            $service->update($this->payload($data));
            $this->syncRelationsAndMedia($service, $data);
        });
    }

    public function delete(Service $service): void
    {
        $service->clearMediaCollection('thumbnail');
        $service->clearMediaCollection('banner');
        $service->clearMediaCollection('share_image');
        $service->relatedServices()->detach();
        $service->delete();
    }

    public function structuredFormValues(Service $service): array
    {
        $values = collect(self::LINE_FIELDS)
            ->mapWithKeys(fn (string $field): array => [$field => StructuredContent::linesForForm($service, $field)])
            ->all();
        $values['faqs'] = StructuredContent::faqsForForm($service);
        $values['pricing_plan_ids'] = $service->pricingPlans()->pluck('pricing_plans.id')->all();

        return $values;
    }

    private function payload(array $data): array
    {
        $payload = Arr::only($data, [
            'thumbnail_id', 'banner_id', 'share_image_id', 'group', 'service_category_id', 'name', 'summary', 'intro', 'video_url', 'seo_title', 'seo_description',
            'seo_keywords', 'sort_order',
        ]);
        foreach (self::LINE_FIELDS as $field) {
            $payload[$field] = StructuredContent::translatedLines($data[$field] ?? []);
        }
        $payload['faqs'] = StructuredContent::translatedFaqs($data['faqs'] ?? []);
        $payload['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $payload['is_active'] = (bool) ($data['is_active'] ?? false);
        $payload['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $payload;
    }

    private function syncRelationsAndMedia(Service $service, array $data): void
    {
        $relatedIds = collect($data['related_service_ids'] ?? [])->map(fn ($id): int => (int) $id)->reject(fn (int $id): bool => $id === $service->id)->all();
        $service->relatedServices()->sync($relatedIds);
        $pricingPlanIds = collect($data['pricing_plan_ids'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->all();
        $service->pricingPlans()->sync($pricingPlanIds);
        foreach (['thumbnail', 'banner', 'share_image'] as $collection) {
            $this->mediaService->syncSingle(
                $service,
                $collection,
                $data[$collection] ?? null,
                (bool) ($data[$collection.'_remove'] ?? false),
            );
        }
    }
}
