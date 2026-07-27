<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\SliderType;
use App\Models\Brand;
use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Slider;
use App\Models\Testimonial;
use App\Settings\AboutSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends FrontendController
{
    public function index(): View
    {
        $homePromotionSlider = Slider::activeFor(SliderType::HomePromotion);

        $flashSale = FlashSale::query()
            ->where('is_active', true)
            ->visibleOnSite()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->with(['items' => fn ($query) => $query->whereHas('product', fn ($productQuery) => $productQuery->where('is_active', true)->visibleOnSite()), 'items.product' => $this->productRelations()])
            ->first();

        $flashProducts = $flashSale
            ? $flashSale->items->map(fn ($item) => $this->presentProduct($item->product, (float) $item->sale_price, $item->variant))->filter()
            : collect();

        $faceProducts = $this->productsForCategory('cham-soc-mat');
        $makeupProducts = $this->productsForCategory('trang-diem');
        $bodyProducts = $this->productsForCategory('cham-soc-co-the');

        $featuredProducts = Product::query()
            ->where('is_active', true)
            ->where('is_home', true)
            ->visibleOnSite()
            ->with($this->productRelations())
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->take(15)
            ->get()
            ->map(fn (Product $product) => $this->presentProduct($product));

        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->with('media')
            ->orderBy('sort_order')
            ->latest('id')
            ->take(12)
            ->get();

        return view('frontend.home', [
            'heroSlider' => Slider::activeFor(SliderType::HomepageHero),
            'homeCta' => $homePromotionSlider?->items
                ->first(fn ($item) => filled($item->getFirstMediaUrl('slide_image'))),
            'coreValues' => $this->coreValues(),
            'categories' => $this->categoriesForView(true),
            'flashProducts' => $flashProducts,
            'flashSale' => $flashSale,
            'featuredProducts' => $featuredProducts,
            'faceProducts' => $faceProducts,
            'makeupProducts' => $makeupProducts,
            'bodyProducts' => $bodyProducts,
            'brands' => Brand::query()
                ->where('is_active', true)
                ->where('is_featured', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->take(12)
                ->get(['id', 'name', 'slug', 'logo']),
            'activeCoupons' => Coupon::query()
                ->where('is_active', true)
                ->visibleOnSite()
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->orderBy('id')
                ->take(2)
                ->get(),
            'homePosts' => $this->homeNews(),
            'testimonials' => $testimonials->take(6),
            'beforeAfterTestimonials' => $testimonials
                ->filter(fn (Testimonial $testimonial): bool => filled($testimonial->getFirstMediaUrl('testimonial_before')) && filled($testimonial->getFirstMediaUrl('testimonial_after')))
                ->take(3)
                ->values(),
        ]);
    }

    private function productsForCategory(string $slug, int $limit = 8): Collection
    {
        $category = ProductCategory::query()->with('children:id,parent_id')->where('slug', $slug)->first();
        if (! $category) {
            return collect();
        }

        $categoryIds = $category->children->pluck('id')->prepend($category->id);

        return Product::query()
            ->where('is_active', true)
            ->visibleOnSite()
            ->whereIn('product_category_id', $categoryIds)
            ->with($this->productRelations())
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->take($limit)
            ->get()
            ->map(fn (Product $product) => $this->presentProduct($product));
    }

    private function coreValues(): Collection
    {
        $html = (string) data_get(app(AboutSettings::class)->about_core_values, 'vi', '');

        if ($html === '') {
            return collect();
        }

        preg_match_all('/<h3\b[^>]*>(.*?)<\/h3>\s*<p\b[^>]*>(.*?)<\/p>/is', $html, $matches, PREG_SET_ORDER);

        return collect($matches)->map(function (array $match): array {
            $title = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $description = trim(html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $normalizedTitle = Str::lower(Str::ascii($title));

            $icon = match (true) {
                str_contains($normalizedTitle, 'chinh hang') => 'bi-patch-check',
                str_contains($normalizedTitle, 'tan tam') => 'bi-heart',
                str_contains($normalizedTitle, 'chat luong') => 'bi-gem',
                str_contains($normalizedTitle, 'trung thuc') => 'bi-shield-check',
                str_contains($normalizedTitle, 'ben vung') => 'bi-flower1',
                default => 'bi-stars',
            };

            return compact('title', 'description', 'icon');
        })->filter(fn (array $value) => $value['title'] !== '')->values();
    }
}
