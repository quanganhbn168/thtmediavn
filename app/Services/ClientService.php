<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Language;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class ClientService
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Client::query()->withCount('projects');
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $languages = Language::getActiveLanguages()->pluck('code')->whenEmpty(fn ($items) => $items->push('vi'));
            $query->where(function ($builder) use ($languages, $search): void {
                foreach ($languages as $language) {
                    $builder->orWhere("name->{$language}", 'like', "%{$search}%");
                }
            });
        }
        if (filled($filters['industry'] ?? null)) {
            $query->where('industry', $filters['industry']);
        }
        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->orderBy('sort_order')->orderBy('id')->paginate((int) ($filters['per_page'] ?? 10))->withQueryString();
    }

    public function create(array $data): Client
    {
        $client = Client::query()->create($this->payload($data));
        $this->syncMedia($client, $data);

        return $client;
    }

    public function update(Client $client, array $data): void
    {
        $client->update($this->payload($data));
        $this->syncMedia($client, $data);
    }

    public function delete(Client $client): void
    {
        $client->clearMediaCollection('logo');
        $client->clearMediaCollection('cover');
        $client->delete();
    }

    private function payload(array $data): array
    {
        return [
            ...Arr::only($data, ['name', 'industry', 'website_url', 'description', 'quote', 'quote_author']),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }

    private function syncMedia(Client $client, array $data): void
    {
        foreach (['logo', 'cover'] as $collection) {
            $this->mediaService->syncSingle($client, $collection, $data[$collection] ?? null, (bool) ($data[$collection.'_remove'] ?? false));
        }
    }
}
