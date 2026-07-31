<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\ContactChannel;
use App\Models\Menu;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\SiteAsset;
use App\Settings\MenuSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SiteChromeCache
{
    public const KEY = 'site:chrome:v1';

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

        if (Schema::hasTable('product_categories')) {
            $siteNavigation = ProductCategory::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->where(fn ($query) => $query
                    ->whereHas('products', $visibleProducts)
                    ->orWhereHas('children.products', $visibleProducts))
                ->with(['children' => fn ($query) => $query
                    ->where('is_active', true)
                    ->whereHas('products', $visibleProducts)])
                ->orderBy('sort_order')
                ->get();
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
}
