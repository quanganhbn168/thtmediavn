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
use App\Services\SiteChromeCache;
use App\Settings\MenuSettings;
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
            ['Dịch vụ', '/dich-vu', [['Tư vấn', '/tu-van']]],
        ]);
        $headerChild = $header->allItems()->whereNotNull('parent_id')->firstOrFail();
        MenuItem::create([
            'menu_id' => $header->id,
            'parent_id' => $headerChild->id,
            'title' => ['vi' => 'Tư vấn chuyên sâu'],
            'url' => '/tu-van/chuyen-sau',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $mega = $this->menu('Danh mục mega', 'header', [
            ['Chăm sóc da', '/danh-muc/cham-soc-da', [['Sữa rửa mặt', '/danh-muc/sua-rua-mat']]],
        ]);
        $megaChild = $mega->allItems()->whereNotNull('parent_id')->firstOrFail();
        MenuItem::create([
            'menu_id' => $mega->id,
            'parent_id' => $megaChild->id,
            'title' => ['vi' => 'Sữa rửa mặt dịu nhẹ'],
            'url' => '/danh-muc/sua-rua-mat-diu-nhe',
            'sort_order' => 1,
            'is_active' => true,
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
            ->assertSee('Dịch vụ')
            ->assertSee('Tư vấn chuyên sâu')
            ->assertSee('dropdown-submenu-menu', false)
            ->assertSee('data-mega-menu', false)
            ->assertSee('Chăm sóc da')
            ->assertSee('Sữa rửa mặt')
            ->assertSee('Sữa rửa mặt dịu nhẹ')
            ->assertSee('Chính sách mua hàng')
            ->assertSee('Chính sách đổi trả')
            ->assertSee('Hỗ trợ khách hàng')
            ->assertSee('Liên hệ tư vấn');
    }

    public function test_mega_menu_auto_nests_product_category_children(): void
    {
        $rootCategory = ProductCategory::query()->where('slug', 'cham-soc-mat')->firstOrFail();
        $childCategory = ProductCategory::query()->where('slug', 'sua-rua-mat')->firstOrFail();
        $this->assertSame($rootCategory->id, $childCategory->parent_id);
        $this->assertTrue($childCategory->products()->where('is_active', true)->exists());

        $mega = $this->menu('Mega menu tự động', 'header', []);
        $rootItem = MenuItem::create([
            'menu_id' => $mega->id,
            'title' => ['vi' => $rootCategory->name],
            'url' => route('content.show', ['domain' => 'danh-muc', 'slug' => $rootCategory->slug]),
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $childItem = MenuItem::create([
            'menu_id' => $mega->id,
            'title' => ['vi' => $childCategory->name],
            'url' => route('content.show', ['domain' => 'danh-muc', 'slug' => $childCategory->slug]),
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $settings = app(MenuSettings::class);
        $settings->mega_menu_id = $mega->id;
        $settings->save();
        app(SiteChromeCache::class)->forget();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Chăm sóc da')
            ->assertSee('Sữa rửa mặt')
            ->assertDontSee('data-mega-tab="menu-'.$childItem->id.'"', false);

        $this->assertDatabaseHas('menu_items', [
            'id' => $rootItem->id,
            'parent_id' => null,
        ]);
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

    public function test_menu_builder_rejects_foreign_items_and_a_fourth_level(): void
    {
        $menu = $this->menu('Header kiểm thử', 'header', [
            ['Cấp 1', '/cap-1'],
        ]);
        $root = $menu->allItems()->firstOrFail();
        $levelTwo = MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $root->id,
            'title' => ['vi' => 'Cấp 2'],
            'url' => '/cap-2',
            'sort_order' => 2,
            'is_active' => true,
        ]);
        $levelThree = MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $levelTwo->id,
            'title' => ['vi' => 'Cấp 3'],
            'url' => '/cap-3',
            'sort_order' => 3,
            'is_active' => true,
        ]);
        $levelFour = MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $levelThree->id,
            'title' => ['vi' => 'Cấp 4'],
            'url' => '/cap-4',
            'sort_order' => 4,
            'is_active' => true,
        ]);
        $foreignItem = $this->menu('Header khác', 'header', [['Ngoài menu', '/ngoai-menu']])->allItems()->firstOrFail();
        $admin = User::role('admin')->firstOrFail();

        $this->actingAs($admin, 'admin')->postJson(route('admin.menus.items.order', $menu), [
            'structure' => [[
                'id' => $root->id,
                'children' => [[
                    'id' => $levelTwo->id,
                    'children' => [[
                        'id' => $levelThree->id,
                        'children' => [['id' => $levelFour->id]],
                    ]],
                ]],
            ]],
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Menu chỉ hỗ trợ tối đa 3 cấp.');

        $this->actingAs($admin, 'admin')->postJson(route('admin.menus.items.order', $menu), [
            'structure' => [['id' => $foreignItem->id]],
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Phát hiện liên kết không thuộc menu này. Vui lòng tải lại trang.');
    }

    public function test_menu_settings_require_separate_header_and_mega_menus(): void
    {
        $menu = $this->menu('Menu Header duy nhất', 'header', [['Trang chủ', '/']]);
        $admin = User::role('admin')->firstOrFail();

        $this->actingAs($admin, 'admin')->post(route('admin.settings.menu.update'), [
            'header_menu_id' => $menu->id,
            'mega_menu_id' => $menu->id,
        ])->assertSessionHasErrors('mega_menu_id');
    }

    public function test_unassigned_menus_are_not_chosen_automatically(): void
    {
        $this->menu('Header chưa gán', 'header', [['Chỉ hiện khi được gán', '/chi-hien-khi-duoc-gan']]);

        $settings = app(MenuSettings::class);
        $settings->header_menu_id = null;
        $settings->mega_menu_id = null;
        $settings->footer_menu_1_id = null;
        $settings->footer_menu_2_id = null;
        $settings->save();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Chỉ hiện khi được gán');
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
