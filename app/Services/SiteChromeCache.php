<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\ContactChannel;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\SiteAsset;
use App\Settings\MenuSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Throwable;

class SiteChromeCache
{
    public const KEY = 'site:chrome:v2';

    /**
     * Dữ liệu dùng lặp lại trong Header, Mega menu và Footer.
     *
     * Cache store hiện tại là database, nên cache này được lưu tại bảng
     * `cache` và luôn được xóa chủ động khi dữ liệu nguồn thay đổi.
     *
     * @return array<string, mixed>
     */
    public function get(): array
    {
        try {
            return Cache::remember(self::KEY, now()->addDay(), fn (): array => $this->build());
        } catch (Throwable) {
            // Cache không được phép làm hỏng website khi đang cài đặt/migrate.
            return $this->build();
        }
    }

    public function forget(): void
    {
        try {
            Cache::forget(self::KEY);
        } catch (Throwable) {
            // Cache là lớp tối ưu hiệu năng; dữ liệu gốc vẫn nằm trong database.
        }
    }

    /** @return array<string, mixed> */
    private function build(): array
    {
        $siteNavigation = collect();
        $siteBrands = collect();
        $attributeMenuGroups = collect();
        $contactChannels = collect();
        $siteAssets = null;
        $headerMenu = null;
        $megaMenu = null;
        $footerMenus = collect();
        $visibleProducts = fn ($query) => $query->where('is_active', true)->visibleOnSite();

        if (Schema::hasTable('product_categories') && Schema::hasTable('products')) {
            $siteNavigation = $this->buildProductCategoryNavigation();
        }

        if (Schema::hasTable('brands')) {
            $siteBrands = Brand::query()
                ->where('is_active', true)
                ->where('is_featured', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug']);
        }

        if (Schema::hasTable('product_attributes') && Schema::hasTable('product_attribute_values')) {
            $attributeMenuGroups = ProductAttribute::query()
                ->where('is_active', true)
                ->where('show_in_product_menu', true)
                ->whereHas('values', fn ($query) => $query->whereHas('products', $visibleProducts))
                ->with(['values' => fn ($query) => $query
                    ->whereHas('products', $visibleProducts)
                    ->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'sort_order']);
        }

        if (Schema::hasTable('contact_channels')) {
            $contactChannels = ContactChannel::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        if (Schema::hasTable('site_assets')) {
            $siteAssets = SiteAsset::current()->loadMissing('media');
        }

        if (Schema::hasTable('settings') && Schema::hasTable('menus') && Schema::hasTable('menu_items')) {
            try {
                $menuSettings = app(MenuSettings::class);
                $availableMenus = Menu::query()
                    ->where('is_active', true)
                    ->whereIn('location', ['header', 'footer'])
                    ->with(['items' => fn ($query) => $query
                        ->where('is_active', true)
                        ->with('childrenRecursive')])
                    ->orderBy('id')
                    ->get();

                $headerMenus = $availableMenus->where('location', 'header')->values();
                $availableFooterMenus = $availableMenus->where('location', 'footer')->values();

                $headerMenu = $menuSettings->header_menu_id
                    ? $headerMenus->firstWhere('id', $menuSettings->header_menu_id)
                    : null;
                $megaMenu = $menuSettings->mega_menu_id
                    ? $headerMenus->firstWhere('id', $menuSettings->mega_menu_id)
                    : null;
                $footerMenus = collect([
                    $menuSettings->footer_menu_1_id
                        ? $availableFooterMenus->firstWhere('id', $menuSettings->footer_menu_1_id)
                        : null,
                    $menuSettings->footer_menu_2_id
                        ? $availableFooterMenus->firstWhere('id', $menuSettings->footer_menu_2_id)
                        : null,
                ])->filter()->values();
            } catch (Throwable) {
                // Settings migrations may not have run yet during install.
            }
        }

        if ($megaMenu) {
            $this->attachAutomaticProductCategoryChildren($megaMenu, $siteNavigation);
        }

        return compact(
            'siteNavigation',
            'siteBrands',
            'attributeMenuGroups',
            'contactChannels',
            'siteAssets',
            'headerMenu',
            'megaMenu',
            'footerMenus',
        );
    }

    /**
     * Build the visible product category tree once for the site chrome.
     *
     * Parent categories are retained when a visible product exists anywhere
     * below them, which keeps the mega menu useful for deeper category trees.
     */
    private function buildProductCategoryNavigation(): Collection
    {
        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'parent_id', 'name', 'slug', 'sort_order', 'is_active']);

        if ($categories->isEmpty()) {
            return collect();
        }

        $visibleCategoryIds = Product::query()
            ->where('is_active', true)
            ->visibleOnSite()
            ->whereNotNull('product_category_id')
            ->pluck('product_category_id')
            ->mapWithKeys(fn ($id): array => [(int) $id => true]);
        $childrenByParent = $categories->groupBy(fn (ProductCategory $category): int => (int) ($category->parent_id ?? 0));

        $walk = function (int $parentId) use (&$walk, $childrenByParent, $visibleCategoryIds): Collection {
            return $childrenByParent->get($parentId, collect())
                ->map(function (ProductCategory $category) use ($walk, $visibleCategoryIds): ?ProductCategory {
                    $children = $walk((int) $category->id);
                    if (! $visibleCategoryIds->has((int) $category->id) && $children->isEmpty()) {
                        return null;
                    }

                    $category->setRelation('children', $children);

                    return $category;
                })
                ->filter()
                ->values();
        };

        return $walk(0);
    }

    /**
     * A configured Mega Menu remains the source of truth for custom links.
     * Category groups without manually nested children receive their current
     * product-category children automatically, so new categories do not need
     * to be added and dragged into the menu again.
     */
    private function attachAutomaticProductCategoryChildren(Menu $megaMenu, Collection $navigation): void
    {
        if ($navigation->isEmpty() || $megaMenu->items->isEmpty()) {
            return;
        }

        $categories = $this->flattenProductCategoryNavigation($navigation);
        $categoriesBySlug = $categories->keyBy('slug');
        $categoriesById = $categories->keyBy('id');
        $rootItems = $megaMenu->items;
        $categorySlugByItemId = $rootItems->mapWithKeys(function (MenuItem $item): array {
            $slug = $this->categorySlugFromMenuItem($item);

            return $slug ? [$item->id => $slug] : [];
        });
        $categoryRootSlugs = $categorySlugByItemId->flip();
        $syntheticId = -1;

        $normalizedItems = $rootItems
            ->filter(function (MenuItem $item) use ($categorySlugByItemId, $categoryRootSlugs, $categoriesBySlug, $categoriesById): bool {
                $slug = $categorySlugByItemId->get($item->id);
                if (! $slug || ! $categoriesBySlug->has($slug)) {
                    return true;
                }

                $category = $categoriesBySlug->get($slug);
                $parentId = (int) ($category->parent_id ?? 0);
                while ($parentId > 0 && $categoriesById->has($parentId)) {
                    $parent = $categoriesById->get($parentId);
                    if ($categoryRootSlugs->has($parent->slug)) {
                        return false;
                    }
                    $parentId = (int) ($parent->parent_id ?? 0);
                }

                return true;
            })
            ->values();

        foreach ($normalizedItems as $group) {
            $slug = $categorySlugByItemId->get($group->id);
            if (! $slug || ! $categoriesBySlug->has($slug) || $group->childrenRecursive->isNotEmpty()) {
                continue;
            }

            $category = $categoriesBySlug->get($slug);
            if ($category->children->isNotEmpty()) {
                $group->setRelation(
                    'childrenRecursive',
                    $this->makeCategoryMenuItems($category->children, $group, $syntheticId),
                );
            }
        }

        $megaMenu->setRelation('items', $normalizedItems);
    }

    private function flattenProductCategoryNavigation(Collection $navigation): Collection
    {
        $flat = collect();
        $walk = function (Collection $categories) use (&$walk, $flat): void {
            foreach ($categories as $category) {
                $flat->push($category);
                $walk($category->children ?? collect());
            }
        };
        $walk($navigation);

        return $flat;
    }

    private function categorySlugFromMenuItem(MenuItem $item): ?string
    {
        $path = parse_url($item->href, PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', trim((string) $path, '/'))));

        return ($segments[0] ?? null) === 'danh-muc' ? ($segments[1] ?? null) : null;
    }

    private function makeCategoryMenuItems(Collection $categories, MenuItem $parent, int &$syntheticId): Collection
    {
        return $categories->values()->map(function (ProductCategory $category, int $index) use ($parent, &$syntheticId): MenuItem {
            $item = new MenuItem([
                'menu_id' => $parent->menu_id,
                'parent_id' => $parent->id,
                'title' => ['vi' => $category->name, 'en' => $category->name],
                'url' => route('content.show', ['domain' => 'danh-muc', 'slug' => $category->slug]),
                'target' => '_self',
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
            $item->setAttribute('id', $syntheticId--);
            $item->setRelation(
                'childrenRecursive',
                $this->makeCategoryMenuItems($category->children ?? collect(), $item, $syntheticId),
            );

            return $item;
        });
    }
}
