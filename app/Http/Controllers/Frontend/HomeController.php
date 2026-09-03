<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\SliderType;
use App\Models\Client;
use App\Models\PricingPlan;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\SiteAsset;
use App\Models\Slider;
use App\Models\Testimonial;
use App\Services\WebsiteSettingsService;
use App\Settings\ContactSettings;
use App\Settings\HomepageSettings;
use App\Support\MapEmbed;
use App\Support\SchemaMarkup;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;

class HomeController extends FrontendController
{
    public function index(): View
    {
        $newsItems = $this->homeNews(3);

        if ($newsItems->isEmpty()) {
            $newsItems = $this->news(3);
        }

        $website = app(WebsiteSettingsService::class)->all();
        $contactPhones = $this->homeContactPhones($website);
        $contactSettings = Schema::hasTable('settings') ? app(ContactSettings::class) : null;
        $homeMapEmbedUrl = MapEmbed::url($contactSettings?->map_embed);
        $siteAssets = Schema::hasTable('site_assets') ? SiteAsset::current() : null;
        $homepageSettings = Schema::hasTable('settings') ? app(HomepageSettings::class) : null;
        $homepageSections = collect($homepageSettings?->homepage_sections ?? []);
        $homepageSectionTitles = $homepageSettings?->homepage_section_titles ?? [];
        $homepageStats = collect($homepageSettings?->homepage_stats ?? [])
            ->filter(fn (mixed $stat): bool => is_array($stat) && filled($stat['value'] ?? null) && filled($stat['label'] ?? null))
            ->values();
        $homepageReasons = collect(preg_split('/\R/u', (string) data_get($homepageSettings?->homepage_reasons, 'vi', '')) ?: [])
            ->map(fn (string $reason): string => trim($reason))
            ->filter()
            ->values();
        $homepageReasonIcons = [
            'fa-solid fa-bullseye',
            'fa-solid fa-people-group',
            'fa-solid fa-list-check',
            'fa-solid fa-circle-check',
        ];
        $homepageAboutTitle = trim((string) data_get($homepageSettings?->homepage_about_title, 'vi', 'Công ty TNHH THT Media'));
        $homepageAboutText = trim((string) data_get($homepageSettings?->homepage_about_text, 'vi', 'THT Media xây dựng một hệ sinh thái sản xuất truyền thông thực tế cho doanh nghiệp, tổ chức và thương hiệu cá nhân.'));
        $homepageAboutSupportingText = trim((string) data_get($homepageSettings?->homepage_about_supporting_text, 'vi', 'Nhân sự in-house, thiết bị chủ động và quy trình rõ ràng giúp mỗi brief được chuyển thành nội dung có thể sử dụng ngay.'));
        $homepageCompanyName = trim((string) ($website['company'] ?? '')) ?: trim((string) ($website['name'] ?? config('app.name')));
        $homepageAboutImage = $siteAssets?->getFirstMediaUrl('about_image') ?: asset('assets/images/home-demo/team.jpg');
        $homeServiceCategories = ServiceCategory::query()
            ->where('is_active', true)
            ->where('is_home', true)
            ->with(['services' => fn ($query) => $query
                ->where('is_active', true)
                ->with('thumbnail')
                ->orderBy('sort_order')
                ->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $heroSlider = Slider::activeFor(SliderType::HomepageHero);
        $homeProjects = Project::query()
            ->visibleOnSite()
            ->with(['client', 'category.slugs', 'cover', 'shareImage', 'media', 'slugs'])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->take(12)
            ->get();
        $projectGroups = collect(['all' => $homeProjects]);
        $projectFilters = $homeProjects
            ->groupBy(fn (Project $project): string => $project->category?->getSlug('vi') ?: 'other')
            ->map(fn (Collection $projects, string $slug): array => [
                'slug' => $slug,
                'label' => $projects->first()?->category?->getTranslation('name', 'vi') ?: 'Khác',
                'projects' => $projects,
            ])
            ->values();

        foreach ($projectFilters as $projectFilter) {
            $projectGroups->put($projectFilter['slug'], $projectFilter['projects']);
        }

        $featuredClients = Client::query()
            ->where('is_active', true)
            ->with('media')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->take(16)
            ->get();
        $clientLogoItems = $featuredClients
            ->filter(fn (Client $client): bool => filled($client->getFirstMediaUrl('logo')))
            ->values();
        $useClientMarquee = $clientLogoItems->count() >= 8;
        $clientMarqueeRows = $useClientMarquee ? $clientLogoItems->split(2)->values() : collect();
        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->with('media')
            ->orderBy('sort_order')
            ->latest('id')
            ->take(6)
            ->get();
        $ratingValues = Testimonial::query()
            ->where('is_active', true)
            ->whereBetween('rating', [1, 5])
            ->pluck('rating');
        $homepageRating = $ratingValues->isEmpty() ? null : [
            'ratingValue' => round((float) $ratingValues->avg(), 1),
            'ratingCount' => $ratingValues->count(),
        ];
        $pricingPlans = Schema::hasTable('pricing_plans')
            ? PricingPlan::query()->where('is_active', true)->orderByDesc('is_featured')->orderBy('sort_order')->get()
            : collect();
        $homepageTitle = trim((string) ($website['seo_title'] ?? '')) ?: 'THT Media | Giải pháp truyền thông và sản xuất nội dung';
        $homepageDescription = trim((string) ($website['seo_description'] ?? '')) ?: 'THT Media đồng hành cùng doanh nghiệp từ ý tưởng đến sản phẩm truyền thông: phim doanh nghiệp, nhiếp ảnh, website, marketing và sự kiện.';

        return view('frontend.home', [
            'heroSlider' => $heroSlider,
            'newsItems' => $newsItems,
            'homeProjects' => $homeProjects,
            'projectGroups' => $projectGroups,
            'projectFilters' => $projectFilters,
            'featuredClients' => $featuredClients,
            'clientLogoItems' => $clientLogoItems,
            'useClientMarquee' => $useClientMarquee,
            'clientMarqueeRows' => $clientMarqueeRows,
            'testimonials' => $testimonials,
            'pricingPlans' => $pricingPlans,
            'contactPhones' => $contactPhones,
            'homeMapEmbedUrl' => $homeMapEmbedUrl,
            'homepageSections' => $homepageSections,
            'homepageSectionTitles' => $homepageSectionTitles,
            'homepageStats' => $homepageStats,
            'homepageReasons' => $homepageReasons,
            'homepageReasonIcons' => $homepageReasonIcons,
            'homepageCompanyName' => $homepageCompanyName,
            'homepageAboutTitle' => $homepageAboutTitle,
            'homepageAboutText' => $homepageAboutText,
            'homepageAboutSupportingText' => $homepageAboutSupportingText,
            'homepageAboutImage' => $homepageAboutImage,
            'homeServiceCategories' => $homeServiceCategories,
            'homepageTitle' => $homepageTitle,
            'homepageDescription' => $homepageDescription,
            'homepageSchema' => SchemaMarkup::homepage(
                $website,
                $siteAssets?->getFirstMediaUrl('logo'),
                $siteAssets?->getFirstMediaUrl('seo_image') ?: $siteAssets?->getFirstMediaUrl('about_image'),
                $homepageTitle,
                $homepageDescription,
                $homepageRating,
            ),
        ]);
    }

    private function homeContactPhones(array $website): Collection
    {
        $phones = $website['phones'] ?? [];

        if (empty($phones) && filled($website['phone'] ?? null)) {
            $phones = [['number' => $website['phone']]];
        }

        return collect($phones)
            ->filter(fn (mixed $phone): bool => is_array($phone) && filled($phone['number'] ?? null))
            ->values();
    }
}
