<?php

namespace App\Providers;

use App\Models\SiteAsset;
use App\Support\Branding\FaviconService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class FaviconServiceProvider extends ServiceProvider
{
    public function boot(FaviconService $favicons): void
    {
        View::composer('layouts.master', function ($view) use ($favicons): void {
            $siteAssets = Schema::hasTable('site_assets') ? SiteAsset::current() : null;

            $view->with('faviconLinks', $favicons->links($siteAssets?->getFirstMedia('favicon')));
        });
    }
}
