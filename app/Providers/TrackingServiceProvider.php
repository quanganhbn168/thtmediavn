<?php

namespace App\Providers;

use App\Support\Tracking\TrackingScripts;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as BladeView;

class TrackingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer(['layouts.master', 'layouts.plain'], function (BladeView $view): void {
            $view->with('trackingMarkup', app(TrackingScripts::class)->render($view->getData()['landingTracking'] ?? []));
        });
    }
}
