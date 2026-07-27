<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\ContactChannel;
use App\Models\Menu;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\SiteAsset;
use App\Services\CartService;
use App\Services\WebsiteSettingsService;
use App\Settings\AboutSettings;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\HomepageSettings;
use App\Settings\MenuSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WebsiteSettingsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (Schema::hasTable('settings')) {
                $general = app(GeneralSettings::class);
                $timezone = $general->timezone;
                if (in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
                    config(['app.timezone' => $timezone]);
                    date_default_timezone_set($timezone);
                }

                config(['app.name' => $general->site_name['vi'] ?? config('app.name')]);
            }
        } catch (\Throwable $exception) {
            // Cho phép artisan migrate/install chạy khi bảng settings chưa sẵn sàng.
            report($exception);
        }

        Paginator::useBootstrapFive();
        RateLimiter::for('frontend-forms', fn (Request $request) => Limit::perMinute(5)->by($request->ip().'|'.$request->route()?->getName()));
        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(5)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('sepay-webhook', fn (Request $request) => Limit::perMinute(120)
            ->by($request->ip()));

        View::composer('*', function ($view): void {
            $view->with('website', app(WebsiteSettingsService::class)->all());
        });

        View::composer('layouts.master', function ($view) {
            $cartCount = 0;
            $wishlistCount = 0;
            $siteNavigation = collect();
            $siteBrands = collect();
            $attributeMenuGroups = collect();
            $contactChannels = collect();
            $siteAssets = null;
            $aboutSettings = null;
            $homepageSettings = null;
            $headerMenu = null;
            $megaMenu = null;
            $footerMenus = collect();
            if (Schema::hasTable('carts')) {
                $cartCount = app(CartService::class)->current()?->items->sum('quantity') ?? 0;
            }
            if (auth()->check() && Schema::hasTable('wishlists')) {
                $wishlistQuery = DB::table('wishlists')
                    ->join('products', 'products.id', '=', 'wishlists.product_id')
                    ->where('wishlists.user_id', auth()->id())
                    ->where('products.is_active', true);
                $wishlistCount = $wishlistQuery->count();
            }
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
                $siteAssets = SiteAsset::current();
            }
            if (Schema::hasTable('settings')) {
                try {
                    $aboutSettings = app(AboutSettings::class);
                    $homepageSettings = app(HomepageSettings::class);

                    if (Schema::hasTable('menus') && Schema::hasTable('menu_items')) {
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

                        $headerMenu = $headerMenus->firstWhere('id', $menuSettings->header_menu_id)
                            ?? $headerMenus->first();
                        $megaMenu = $headerMenus->firstWhere('id', $menuSettings->mega_menu_id)
                            ?? $headerMenus->get(1);
                        $footerMenus = collect([
                            $availableFooterMenus->firstWhere('id', $menuSettings->footer_menu_1_id)
                                ?? $availableFooterMenus->first(),
                            $availableFooterMenus->firstWhere('id', $menuSettings->footer_menu_2_id)
                                ?? $availableFooterMenus->get(1),
                        ])->filter()->values();
                    }
                } catch (\Throwable) {
                    // Settings migrations may not have run yet during install.
                }
            }
            $view->with(compact(
                'cartCount',
                'wishlistCount',
                'siteNavigation',
                'siteBrands',
                'attributeMenuGroups',
                'contactChannels',
                'siteAssets',
                'aboutSettings',
                'homepageSettings',
                'headerMenu',
                'megaMenu',
                'footerMenus',
            ));
        });

        View::composer(['frontend.home', 'frontend.about', 'frontend.contact', 'frontend.auth', 'pages.auth', 'pages.contact'], function ($view): void {
            $aboutSettings = null;
            $homepageSettings = null;
            $contactSettings = null;
            $siteAssets = Schema::hasTable('site_assets') ? SiteAsset::current() : null;

            if (Schema::hasTable('settings')) {
                try {
                    $aboutSettings = app(AboutSettings::class);
                    $homepageSettings = app(HomepageSettings::class);
                    $contactSettings = app(ContactSettings::class);
                } catch (\Throwable) {
                    // Cho phép hiển thị fallback trong lúc settings migrations chưa hoàn tất.
                }
            }

            $view->with(compact('aboutSettings', 'homepageSettings', 'contactSettings', 'siteAssets'));
        });
    }
}
