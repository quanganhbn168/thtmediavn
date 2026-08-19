<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends FrontendController
{
    public function resolve(string $slug): View
    {
        $category = ProjectCategory::query()
            ->where('is_active', true)
            ->whereHas('slugs', fn (Builder $query) => $query
                ->where('slug', $slug)
                ->where('locale', app()->getLocale()))
            ->with(['slugs', 'children' => fn ($query) => $query->where('is_active', true)->with('slugs')])
            ->first();

        return $category ? $this->category($category) : $this->show($slug);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'service' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:150'],
            'industry' => ['nullable', 'string', 'max:100'],
        ]);
        $projects = Project::query()->visibleOnSite()
            ->when(filled($filters['service'] ?? null), fn (Builder $query) => $query->whereHas('services.slugs', fn (Builder $slugQuery) => $slugQuery->where('slug', $filters['service'])))
            ->when(filled($filters['category'] ?? null), fn (Builder $query) => $query->whereHas('category.slugs', fn (Builder $slugQuery) => $slugQuery->where('slug', $filters['category'])))
            ->when(filled($filters['industry'] ?? null), fn (Builder $query) => $query->where('industry', $filters['industry']))
            ->with(['client', 'category.slugs', 'services.slugs', 'cover', 'shareImage', 'media', 'slugs'])
            ->orderBy('sort_order')->latest('published_at')->paginate(12)->withQueryString();

        return view('frontend.projects.index', [
            'projects' => $projects,
            'services' => Service::query()->where('is_active', true)->with('slugs')->orderBy('sort_order')->get(),
            'categories' => ProjectCategory::query()->where('is_active', true)->with('slugs')->orderBy('sort_order')->get(),
            'industries' => Project::query()->visibleOnSite()->whereNotNull('industry')->distinct()->orderBy('industry')->pluck('industry'),
        ]);
    }

    public function category(ProjectCategory $category): View
    {
        $categoryName = $category->getTranslation('name', app()->getLocale());
        $categoryDescription = $category->getTranslation('description', app()->getLocale()) ?: '';
        $projects = Project::query()
            ->visibleOnSite()
            ->where('project_category_id', $category->id)
            ->with(['client', 'category.slugs', 'services.slugs', 'cover', 'shareImage', 'galleryMedia', 'media', 'slugs'])
            ->orderBy('sort_order')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('frontend.projects.category', [
            'category' => $category,
            'categoryName' => $categoryName,
            'categoryDescription' => $categoryDescription,
            'projects' => $projects,
            'projectSchemaItems' => $projects->getCollection()->map(fn (Project $project): array => [
                'name' => $project->getTranslation('name', app()->getLocale()),
                'url' => route('projects.show', $project->getSlug(app()->getLocale())),
            ])->all(),
        ]);
    }

    public function show(string $slug): View
    {
        $project = Project::query()->visibleOnSite()
            ->whereHas('slugs', fn (Builder $query) => $query->where('slug', $slug)->where('locale', app()->getLocale()))
            ->with(['client.media', 'category', 'services.media', 'services.slugs', 'cover', 'shareImage', 'galleryMedia', 'media', 'slugs', 'comments.replies'])
            ->firstOrFail();

        return view('frontend.projects.show', [
            'project' => $project,
            'relatedProjects' => Project::query()->visibleOnSite()->whereKeyNot($project->id)
                ->whereHas('services', fn (Builder $query) => $query->whereKey($project->services->pluck('id')))
                ->with(['client', 'cover', 'shareImage', 'media', 'slugs'])->take(3)->get(),
            'consultingServices' => Service::query()->where('is_active', true)->orderBy('sort_order')->get()
                ->mapWithKeys(fn (Service $service) => [$service->id => $service->getTranslation('name', app()->getLocale())])->all(),
        ]);
    }
}
