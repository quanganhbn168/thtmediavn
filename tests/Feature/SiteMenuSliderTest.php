<?php

namespace Tests\Feature;

use App\Enums\SliderType;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Slider;
use App\Services\SiteChromeCache;
use App\Settings\WebsiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteMenuSliderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_selected_header_and_footer_menus_are_rendered(): void
    {
        $header = $this->menu('Điều hướng chính', 'header', ['Dịch vụ truyền thông' => '/dich-vu']);
        $footer = $this->menu('Thông tin', 'footer', ['Chính sách bảo mật' => '/chinh-sach-bao-mat']);
        $settings = app(WebsiteSettings::class);
        $settings->header_menu_id = $header->id;
        $settings->footer_menu_1_id = $footer->id;
        $settings->save();
        app(SiteChromeCache::class)->forget();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Dịch vụ truyền thông')
            ->assertSee('class="site-service-mega"', false)
            ->assertSee('Chính sách bảo mật');
    }

    public function test_homepage_uses_the_active_homepage_slider(): void
    {
        $slider = Slider::query()->where('key', SliderType::HomepageHero->value)->firstOrFail();
        $slider->items()->create([
            'title' => ['vi' => 'Nội dung nổi bật THTMedia'],
            'sub_title' => ['vi' => 'Khung nội dung truyền thông'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'))->assertOk();
        $this->assertSame($slider->id, $response->viewData('heroSlider')->id);
    }

    public function test_domain_slug_resolves_a_post(): void
    {
        $post = Post::query()->firstOrFail();

        $this->get(route('content.show', ['domain' => 'tin-tuc', 'slug' => $post->slug]))
            ->assertOk()
            ->assertSee($post->getTranslation('name', 'vi'));
    }

    public function test_domain_slug_resolves_a_page(): void
    {
        $page = Page::query()->firstOrFail();

        $this->get(route('content.show', ['domain' => 'trang', 'slug' => $page->slug]))
            ->assertOk()
            ->assertSee($page->getTranslation('name', 'vi'));
    }

    private function menu(string $name, string $location, array $items): Menu
    {
        $menu = Menu::query()->create([
            'name' => ['vi' => $name],
            'location' => $location,
            'is_active' => true,
        ]);

        foreach ($items as $title => $url) {
            $menu->allItems()->create([
                'title' => ['vi' => $title],
                'url' => $url,
                'sort_order' => $menu->allItems()->count() + 1,
                'is_active' => true,
            ]);
        }

        return $menu;
    }
}
