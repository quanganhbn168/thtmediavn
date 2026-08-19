<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Project;
use App\Support\StructuredContent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    private const LINE_FIELDS = ['work_items', 'results'];

    public function __construct(private readonly MediaService $mediaService) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Project::query()->with(['client', 'services']);
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
        if (filled($filters['service_id'] ?? null)) {
            $query->whereHas('services', fn ($builder) => $builder->whereKey($filters['service_id']));
        }
        if (filled($filters['industry'] ?? null)) {
            $query->where('industry', $filters['industry']);
        }
        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->orderBy('sort_order')->latest('id')->paginate((int) ($filters['per_page'] ?? 10))->withQueryString();
    }

    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data): Project {
            $project = new Project($this->payload($data));
            $project->setSlugOverride($data['slug'] ?? null)->save();
            $this->syncRelationsAndMedia($project, $data);

            return $project;
        });
    }

    public function update(Project $project, array $data): void
    {
        DB::transaction(function () use ($project, $data): void {
            $project->setSlugOverride($data['slug'] ?? null);
            $project->update($this->payload($data));
            $this->syncRelationsAndMedia($project, $data);
        });
    }

    public function delete(Project $project): void
    {
        foreach (['cover', 'gallery', 'share_image'] as $collection) {
            $project->clearMediaCollection($collection);
        }
        $project->services()->detach();
        $project->delete();
    }

    public function structuredFormValues(Project $project): array
    {
        return collect(self::LINE_FIELDS)
            ->mapWithKeys(fn (string $field): array => [$field => StructuredContent::linesForForm($project, $field)])
            ->all();
    }

    private function payload(array $data): array
    {
        $payload = Arr::only($data, [
            'cover_id', 'share_image_id', 'client_id', 'project_category_id', 'name', 'summary', 'context', 'solution', 'industry', 'completed_year',
            'video_url', 'seo_title', 'seo_description', 'seo_keywords', 'published_at', 'sort_order',
        ]);
        foreach (self::LINE_FIELDS as $field) {
            $payload[$field] = StructuredContent::translatedLines($data[$field] ?? []);
        }
        $payload['is_featured'] = (bool) ($data['is_featured'] ?? false);
        $payload['is_active'] = (bool) ($data['is_active'] ?? false);
        $payload['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $payload;
    }

    private function syncRelationsAndMedia(Project $project, array $data): void
    {
        $project->services()->sync($data['service_ids'] ?? []);
        foreach (['cover', 'share_image'] as $collection) {
            $this->mediaService->syncSingle($project, $collection, $data[$collection] ?? null, (bool) ($data[$collection.'_remove'] ?? false));
        }
        $this->mediaService->syncMultiple($project, 'gallery', $data['gallery'] ?? null, (bool) ($data['gallery_remove'] ?? false));
    }
}
