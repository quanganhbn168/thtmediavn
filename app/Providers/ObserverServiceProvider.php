<?php

namespace App\Providers;

use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Observers\SlugObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Đăng ký SlugObserver cho các Model liên quan
        Page::observe(SlugObserver::class);
        Post::observe(SlugObserver::class);
        PostCategory::observe(SlugObserver::class);
        Product::observe(SlugObserver::class);
    }
}
