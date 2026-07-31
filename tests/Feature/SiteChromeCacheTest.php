<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Services\SettingService;
use App\Services\SiteChromeCache;
use App\Services\WebsiteSettingsService;
use App\Settings\MenuSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteChromeCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_site_chrome_and_website_settings_are_cached_and_invalidated(): void
    {
        $chromeCache = app(SiteChromeCache::class);
        $websiteSettings = app(WebsiteSettingsService::class);

        $chromeCache->forget();
        $websiteSettings->refresh();

        $chrome = $chromeCache->get();
        $websiteSettings->all();

        $this->assertArrayHasKey('headerMenu', $chrome);
        $this->assertTrue(Cache::has(SiteChromeCache::KEY));
        $this->assertTrue(Cache::has(WebsiteSettingsService::CACHE_KEY));

        Menu::create([
            'name' => ['vi' => 'Menu cache kiểm thử'],
            'location' => 'header',
            'is_active' => true,
        ]);

        $this->assertFalse(Cache::has(SiteChromeCache::KEY));

        $chromeCache->get();
        app(SettingService::class)->updateMenu([], app(MenuSettings::class));

        $this->assertFalse(Cache::has(SiteChromeCache::KEY));

        $websiteSettings->refresh();
        $this->assertFalse(Cache::has(WebsiteSettingsService::CACHE_KEY));
    }
}
