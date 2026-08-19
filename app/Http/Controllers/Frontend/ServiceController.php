<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends FrontendController
{
    public function resolve(string $slug): View
    {
        $category = ServiceCategory::query()
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
            'group' => ['nullable', 'string', Rule::in(array_keys(Service::GROUPS))],
            'category' => ['nullable', 'integer', 'exists:service_categories,id'],
        ]);
        $services = Service::query()->where('is_active', true)->with(['media', 'thumbnail', 'banner', 'shareImage', 'slugs', 'category'])->withCount('projects')
            ->when(filled($filters['group'] ?? null), fn (Builder $query) => $query->where('group', $filters['group']))
            ->when(filled($filters['category'] ?? null), fn (Builder $query) => $query->where('service_category_id', $filters['category']))
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('frontend.services.index', [
            'serviceGroups' => $services->groupBy(fn (Service $service): string => (string) ($service->service_category_id ?: 'legacy-'.$service->group)),
            'serviceCategories' => ServiceCategory::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function category(ServiceCategory $category): View
    {
        $categoryName = $category->getTranslation('name', app()->getLocale());
        $categoryDescription = $category->getTranslation('description', app()->getLocale()) ?: '';
        $services = Service::query()
            ->where('is_active', true)
            ->where('service_category_id', $category->id)
            ->with(['media', 'thumbnail', 'banner', 'shareImage', 'slugs', 'category'])
            ->withCount('projects')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('frontend.services.category', [
            'category' => $category,
            'categoryName' => $categoryName,
            'categoryDescription' => $categoryDescription,
            'services' => $services,
            'serviceSchemaItems' => $services->map(fn (Service $service): array => [
                'name' => $service->getTranslation('name', app()->getLocale()),
                'url' => route('services.show', $service->getSlug(app()->getLocale())),
            ])->all(),
        ]);
    }

    public function show(string $slug): View
    {
        $service = Service::query()->where('is_active', true)
            ->whereHas('slugs', fn (Builder $query) => $query->where('slug', $slug)->where('locale', app()->getLocale()))
            ->with(['media', 'thumbnail', 'banner', 'shareImage', 'slugs', 'category', 'relatedServices.media', 'relatedServices.thumbnail', 'relatedServices.slugs', 'pricingPlans'])
            ->firstOrFail();

        $projects = Project::query()->visibleOnSite()->whereHas('services', fn (Builder $query) => $query->whereKey($service->id))
            ->with(['client', 'services', 'cover', 'shareImage', 'media', 'slugs'])->orderByDesc('completed_year')->take(6)->get();
        $faqs = collect($service->getTranslation('faqs', app()->getLocale(), false) ?: []);
        $pricingPlans = $service->pricingPlans->where('is_active', true)->values();

        return view('frontend.services.show', [
            'service' => $service,
            'projects' => $projects,
            'pricingPlans' => $pricingPlans,
            'consultingServices' => $this->serviceOptions(),
            'faqSchema' => $faqs->isEmpty() ? null : [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn (array $faq): array => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
                ])->all(),
            ],
        ]);
    }

    private function serviceOptions(): array
    {
        return Service::query()->where('is_active', true)->orderBy('sort_order')->get()
            ->mapWithKeys(fn (Service $service) => [$service->id => $service->getTranslation('name', app()->getLocale())])->all();
    }
}
