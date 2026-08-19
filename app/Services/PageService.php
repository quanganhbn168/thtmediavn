<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PageService
{
    public const TEMPLATES = ['default' => 'Mặc định', 'landing' => 'Landing page', 'full-width' => 'Toàn chiều rộng'];

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Page::query();
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $langs = Language::getActiveLanguages()->pluck('code');
            if ($langs->isEmpty()) {
                $langs = collect(['vi', 'en']);
            }$query->where(function (Builder $q) use ($langs, $search) {
                foreach ($langs as $lang) {
                    $q->orWhere("name->{$lang}", 'like', "%{$search}%");
                }
            });
        }if (! empty($filters['template'])) {
            $query->where('template', $filters['template']);
        }if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        }if (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }$columns = ['manual' => 'sort_order', 'name' => 'name->vi', 'published_at' => 'published_at', 'created_at' => 'created_at'];
        $direction = $filters['direction'] ?? 'asc';

        return $query->orderBy($columns[$filters['sort'] ?? 'manual'] ?? 'sort_order', $direction)->orderBy('id', $direction)->paginate((int) ($filters['per_page'] ?? 10))->withQueryString();
    }

    public function create(array $data): Page
    {
        return Page::create($this->payload($data));
    }

    public function update(Page $page, array $data): void
    {
        $page->update($this->payload($data));
    }

    public function delete(Page $page): void
    {
        $page->clearMediaCollection();
        $page->delete();
    }

    private function payload(array $data): array
    {
        $payload = collect($data)->only(['template', 'name', 'sub_title', 'content', 'seo_title', 'seo_description', 'seo_keywords', 'published_at', 'sort_order'])->all();
        $payload['is_active'] = (bool) ($data['is_active'] ?? false);
        $payload['sort_order'] = $payload['sort_order'] ?? 0;

        return $payload;
    }
}
