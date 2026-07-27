<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Slider;
use App\Models\SliderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteMenuSliderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_selected_header_mega_and_footer_menus_are_rendered(): void
    {
        $header = $this->menu('Điều hướng chính', 'header', [
            ['Trang chủ mới', '/'],
        ]);
        $mega = $this->menu('Danh mục mega', 'header', [
            ['Chăm sóc da', '/danh-muc/cham-soc-da', [['Sữa rửa mặt', '/danh-muc/sua-rua-mat']]],
        ]);
        $footerOne = $this->menu('Chính sách mua hàng', 'footer', [
            ['Chính sách đổi trả', '/chinh-sach-doi-tra'],
        ]);
        $footerTwo = $this->menu('Hỗ trợ khách hàng', 'footer', [
            ['Liên hệ tư vấn', '/lien-he'],
        ]);

        $admin = User::role('admin')->firstOrFail();
        $this->actingAs($admin, 'admin')->get(route('admin.settings.menu'))
            ->assertOk()
            ->assertSee('Điều hướng chính')
            ->assertSee('Danh mục mega')
            ->assertSee('Chính sách mua hàng')
            ->assertSee('Hỗ trợ khách hàng');

        $this->actingAs($admin, 'admin')->post(route('admin.settings.menu.update'), [
            'header_menu_id' => $header->id,
            'mega_menu_id' => $mega->id,
            'footer_menu_1_id' => $footerOne->id,
            'footer_menu_2_id' => $footerTwo->id,
        ])->assertSessionHasNoErrors();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Trang chủ mới')
            ->assertSee('data-mega-menu', false)
            ->assertSee('Chăm sóc da')
            ->assertSee('Sữa rửa mặt')
            ->assertSee('Chính sách mua hàng')
            ->assertSee('Chính sách đổi trả')
            ->assertSee('Hỗ trợ khách hàng')
            ->assertSee('Liên hệ tư vấn');
    }

    public function test_homepage_uses_active_homepage_slider_items(): void
    {
        Storage::fake('public_media');

        $slider = Slider::create([
            'name' => ['vi' => 'Slider trang chủ'],
            'key' => 'homepage_hero',
            'is_active' => true,
        ]);
        $item = SliderItem::create([
            'slider_id' => $slider->id,
            'title' => ['vi' => 'Làn da khỏe đẹp'],
            'sub_title' => ['vi' => 'Chăm sóc khoa học mỗi ngày'],
            'buttons' => [[
                'text' => ['vi' => 'Khám phá ngay'],
                'link' => '/san-pham',
            ]],
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $item->addMedia(UploadedFile::fake()->image('hero.jpg', 1920, 720))
            ->toMediaCollection('slide_image', 'public_media');
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-home-hero-swiper', false)
            ->assertSee('class="swiper-wrapper"', false)
            ->assertSee('vendor/swiper/swiper-bundle.min.js', false)
            ->assertSee('Làn da khỏe đẹp')
            ->assertSee('Chăm sóc khoa học mỗi ngày')
            ->assertSee('Khám phá ngay')
            ->assertDontSee('RHEA SKINLAB đồng hành cùng phụ nữ xây dựng vẻ đẹp khỏe mạnh, tự nhiên và bền vững.');

        $this->assertStringContainsString("effect: 'fade'", file_get_contents(public_path('assets/js/app.js')));
        $this->assertStringContainsString('left: max(7vw', file_get_contents(public_path('assets/css/style.css')));
    }

    public function test_domain_slug_urls_are_resolved_by_slug_controller(): void
    {
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $category = ProductCategory::query()->where('is_active', true)->firstOrFail();
        $page = Page::create([
            'template' => 'default',
            'name' => ['vi' => 'Hướng dẫn chọn sản phẩm'],
            'sub_title' => ['vi' => 'Trang nội dung kiểm thử'],
            'content' => ['vi' => '<p>Nội dung trang tĩnh.</p>'],
            'is_active' => true,
            'published_at' => now(),
            'sort_order' => 0,
        ]);

        $this->get(route('content.show', ['domain' => 'san-pham', 'slug' => $product->slug]))
            ->assertOk()
            ->assertSee($product->name);
        $this->get(route('content.show', ['domain' => 'danh-muc', 'slug' => $category->slug]))
            ->assertOk();
        $this->get(route('content.show', ['domain' => 'trang', 'slug' => $page->getSlug('vi')]))
            ->assertOk()
            ->assertSee('Hướng dẫn chọn sản phẩm')
            ->assertSee('Nội dung trang tĩnh.');
        $this->get(route('content.show', ['domain' => 'trang', 'slug' => $product->slug]))
            ->assertNotFound();
    }

    public function test_product_category_menu_source_saves_a_domain_slug_url(): void
    {
        $menu = Menu::create([
            'name' => ['vi' => 'Danh mục sản phẩm'],
            'location' => 'header',
            'is_active' => true,
        ]);
        $category = ProductCategory::query()->where('is_active', true)->firstOrFail();
        $admin = User::role('admin')->firstOrFail();

        $this->actingAs($admin, 'admin')->post(route('admin.menus.items.add', $menu), [
            'type' => 'product_categories',
            'ids' => [$category->id],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('menu_items', [
            'menu_id' => $menu->id,
            'url' => route('content.show', ['domain' => 'danh-muc', 'slug' => $category->slug]),
        ]);
    }

    private function menu(string $name, string $location, array $items): Menu
    {
        $menu = Menu::create([
            'name' => ['vi' => $name],
            'location' => $location,
            'is_active' => true,
        ]);

        foreach ($items as $sort => $itemData) {
            [$title, $url] = $itemData;
            $children = $itemData[2] ?? [];
            $parent = MenuItem::create([
                'menu_id' => $menu->id,
                'title' => ['vi' => $title],
                'url' => $url,
                'sort_order' => $sort,
                'is_active' => true,
            ]);

            foreach ($children as $childSort => [$childTitle, $childUrl]) {
                MenuItem::create([
                    'menu_id' => $menu->id,
                    'parent_id' => $parent->id,
                    'title' => ['vi' => $childTitle],
                    'url' => $childUrl,
                    'sort_order' => $childSort,
                    'is_active' => true,
                ]);
            }
        }

        return $menu;
    }
}
