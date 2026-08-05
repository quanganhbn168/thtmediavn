<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\ContactChannel;
use App\Models\Combo;
use App\Models\ComboCategory;
use App\Models\ComboItem;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use App\Models\SiteAsset;
use App\Services\CartService;
use App\Services\SiteChromeCache;
use App\Services\WebsiteSettingsService;
use App\Settings\AboutSettings;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\HomepageSettings;
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
        $this->app->singleton(SiteChromeCache::class);
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
            $aboutSettings = null;
            $homepageSettings = null;
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

            $chrome = app(SiteChromeCache::class)->get();
            $siteNavigation = $chrome['siteNavigation'];
            $siteComboCategories = $chrome['siteComboCategories'];
            $siteBrands = $chrome['siteBrands'];
            $siteCombos = $chrome['siteCombos'];
            $attributeMenuGroups = $chrome['attributeMenuGroups'];
            $contactChannels = $chrome['contactChannels'];
            $siteAssets = $chrome['siteAssets'];
            $headerMenu = $chrome['headerMenu'];
            $megaMenu = $chrome['megaMenu'];
            $footerMenus = $chrome['footerMenus'];

            if (Schema::hasTable('settings')) {
                try {
                    $aboutSettings = app(AboutSettings::class);
                    $homepageSettings = app(HomepageSettings::class);
                } catch (\Throwable) {
                    // Settings migrations may not have run yet during install.
                }
            }
            $view->with(compact(
                'cartCount',
                'wishlistCount',
                'siteNavigation',
                'siteComboCategories',
                'siteBrands',
                'siteCombos',
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

        foreach ([
            Brand::class,
            ContactChannel::class,
            Menu::class,
            MenuItem::class,
            Product::class,
            Combo::class,
            ComboCategory::class,
            ComboItem::class,
            ProductAttribute::class,
            ProductAttributeValue::class,
            ProductCategory::class,
        ] as $model) {
            $model::saved(fn () => app(SiteChromeCache::class)->forget());
            $model::deleted(fn () => app(SiteChromeCache::class)->forget());
        }
    }
}
