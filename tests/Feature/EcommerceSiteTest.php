<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use App\Models\Brand;
use App\Models\Slider;
use App\Models\SliderItem;
use App\Models\SiteAsset;
use App\Models\Testimonial;
use App\Enums\SliderType;
use App\Settings\HomepageSettings;
use App\Settings\ContactSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EcommerceSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_site_renders_the_approved_vietnamese_design(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Flash Sale')
            ->assertSee('Danh mục sản phẩm')
            ->assertSee('style.css', false)
            ->assertSee('assets/fonts/be-vietnam-pro/font.css', false)
            ->assertDontSee('fonts.googleapis.com', false)
            ->assertSee('RHEA SKINLAB')
            ->assertDontSee('Vì sao chọn chúng tôi?')
            ->assertDontSee('section-heading-kicker', false)
            ->assertSee('home-value-card', false)
            ->assertSee('bi-patch-check', false)
            ->assertSee('bi-heart', false)
            ->assertSee('home-featured-products', false)
            ->assertSee('flash-sale-swiper', false)
            ->assertSee('data-flash-sale-swiper', false);
        $this->get(route('catalog'))->assertOk()->assertSee('Bộ lọc sản phẩm')->assertSee(Product::first()->name);
        $this->get(route('product.show', Product::first()->slug))
            ->assertOk()
            ->assertSee('Đánh giá sản phẩm')
            ->assertSee('Thêm vào giỏ hàng')
            ->assertSee('vendor/swiper/swiper-bundle.min.css', false)
            ->assertSee('vendor/swiper/swiper-bundle.min.js', false)
            ->assertSee('data-product-gallery-main', false);

        $this->assertStringContainsString(
            'data-product-gallery-thumbs',
            file_get_contents(resource_path('views/frontend/products/detail.blade.php')),
        );
    }

    public function test_catalog_filters_by_brand_and_search(): void
    {
        $product = Product::with('brand')->firstOrFail();
        $this->get(route('catalog', ['brand' => $product->brand->slug]))->assertOk()->assertSee($product->name);
        $this->get(route('catalog', ['q' => $product->sku]))->assertOk()->assertSee($product->name);
    }

    public function test_catalog_keeps_filter_group_keys_and_rejects_an_unknown_category(): void
    {
        $value = ProductAttributeValue::query()->whereHas('products')->with('attribute')->firstOrFail();

        $response = $this->get(route('catalog', [
            'attribute_values' => [$value->product_attribute_id => [$value->id]],
            'sort' => 'newest',
        ]))->assertOk()
            ->assertSee('Đang lọc:')
            ->assertSee('Mới nhất');

        $html = $response->getContent();
        $this->assertStringContainsString('name="attribute_values['.$value->product_attribute_id.'][]"', $html);
        $this->assertMatchesRegularExpression('/name="attribute_values\['.$value->product_attribute_id.'\]\[\]" value="'.$value->id.'"[^>]*checked/', $html);

        $this->get(route('catalog', ['category' => 'danh-muc-khong-ton-tai']))->assertNotFound();
    }

    public function test_product_cards_require_variant_selection_and_hide_quick_add_when_unavailable(): void
    {
        $product = Product::query()->where('is_active', true)->with('variants')->firstOrFail();
        $baseVariant = $product->variants->firstOrFail();
        $product->variants()->create([
            'name' => 'Phân loại kiểm thử',
            'sku' => 'TEST-VARIANT-'.$product->id,
            'price' => $baseVariant->price,
            'stock' => 5,
            'is_default' => false,
            'is_active' => true,
        ]);

        $response = $this->get(route('catalog', ['q' => $product->name]))
            ->assertOk()
            ->assertSee('Chọn phân loại');
        $presented = collect($response->viewData('products')->items())->firstWhere('id', $product->id);
        $this->assertTrue($presented['requires_variant_selection']);
        $this->assertFalse($presented['can_quick_add']);

        $product->variants()->where('id', '!=', $baseVariant->id)->update(['is_active' => false]);
        $product->update(['track_inventory' => true, 'allow_preorder' => false]);
        $baseVariant->update(['stock' => 0, 'is_active' => true]);
        $response = $this->get(route('catalog', ['q' => $product->name]))->assertOk()->assertSee('Tạm hết hàng');
        $this->assertStringNotContainsString('data-add-cart data-product-id="'.$product->id.'"', $response->getContent());
    }

    public function test_product_detail_has_real_purchase_actions_sticky_bar_and_approved_review_scope(): void
    {
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update([
            'description' => '<p>Thông tin sản phẩm kiểm thử.</p>',
            'ingredients' => '<p>Niacinamide và Panthenol.</p>',
            'usage' => '<p>Dùng sau bước làm sạch.</p>',
            'product_notes' => '<p>Ngưng sử dụng nếu có dấu hiệu kích ứng.</p>',
        ]);

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee('name="action" value="buy_now"', false)
            ->assertSee('data-buy-now', false)
            ->assertSee('product-mobile-buybar', false)
            ->assertSee('Thông tin sản phẩm')
            ->assertSee('Thành phần cấu tạo')
            ->assertSee('Hướng dẫn sử dụng')
            ->assertSee('Lưu ý về sản phẩm')
            ->assertSee('Niacinamide và Panthenol.')
            ->assertSee('Dùng sau bước làm sạch.')
            ->assertSee('Ngưng sử dụng nếu có dấu hiệu kích ứng.')
            ->assertSee('Đánh giá được kiểm duyệt')
            ->assertDontSee('Vui lòng đăng nhập để đánh giá sản phẩm.')
            ->assertDontSee('description-product-callout', false)
            ->assertDontSee('Cách đưa vào routine')
            ->assertDontSee('nên được đồng bộ với chính sách vận hành thực tế');
    }

    public function test_login_and_register_use_the_configured_site_logo(): void
    {
        Storage::fake('public_media');
        $assets = SiteAsset::current();
        $assets->addMedia(UploadedFile::fake()->image('auth-logo.png', 310, 92))
            ->toMediaCollection('logo', 'public_media');

        foreach ([route('login'), route('register')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('auth-brand-logo', false)
                ->assertSee($assets->getFirstMediaUrl('logo'), false);
        }
    }

    public function test_contact_page_renders_the_saved_google_map_iframe(): void
    {
        $settings = app(ContactSettings::class);
        $settings->map_embed = '<iframe src="https://www.google.com/maps/embed?pb=testing-map"></iframe>';
        $settings->save();

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('contact-map-embed', false)
            ->assertSee('https://www.google.com/maps/embed?pb=testing-map', false)
            ->assertDontSee('Bản đồ sẽ được hiển thị khi doanh nghiệp cung cấp liên kết vị trí chính thức.');
    }

    public function test_homepage_renders_configured_cta_curated_testimonial_and_consultation_form(): void
    {
        Storage::fake('public_media');
        $homepageSettings = app(HomepageSettings::class);
        $homepageSettings->homepage_sections = array_values(array_unique([
            ...$homepageSettings->homepage_sections,
            'testimonials',
        ]));
        $homepageSettings->homepage_section_titles = [
            ...$homepageSettings->homepage_section_titles,
            'testimonials' => ['vi' => 'Khách hàng nói gì về chúng tôi'],
        ];
        $homepageSettings->save();

        $slider = Slider::query()->create([
            'name' => ['vi' => 'CTA trang chủ'],
            'key' => SliderType::HomePromotion->value,
            'is_active' => true,
        ]);
        $cta = SliderItem::query()->create([
            'slider_id' => $slider->id,
            'title' => ['vi' => 'Chăm da đúng cách, đẹp theo cách của bạn'],
            'sub_title' => ['vi' => 'Nhận gợi ý routine từ đội ngũ Rhea Skinlab.'],
            'buttons' => [[
                'text' => ['vi' => 'Nhận tư vấn'],
                'link' => '#tu-van',
            ]],
            'sort_order' => 0,
            'is_active' => true,
        ]);
        $cta->addMedia(UploadedFile::fake()->image('cta.jpg', 1920, 700))->toMediaCollection('slide_image', 'public_media');

        Testimonial::query()->create([
            'name' => 'Khách hàng kiểm thử',
            'label' => 'Da hỗn hợp · Hà Nội',
            'rating' => 5,
            'content' => 'Sản phẩm phù hợp và đội ngũ tư vấn rất tận tâm.',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Chăm da đúng cách, đẹp theo cách của bạn')
            ->assertSee('Nhận tư vấn')
            ->assertSee('Khách hàng nói gì về chúng tôi')
            ->assertSee('Khách hàng kiểm thử')
            ->assertSee('Nhận tư vấn nhanh')
            ->assertSee('action="'.route('contact.submit').'"', false);

        $this->from(route('home'))
            ->post(route('contact.submit'), [
                'name' => 'Khách tư vấn kiểm thử',
                'phone' => '0900000000',
                'email' => 'tu-van@example.test',
                'subject' => 'Tư vấn chọn sản phẩm',
                'message' => 'Đăng ký nhận tư vấn từ form trang chủ.',
                'website' => '',
            ])
            ->assertRedirect(route('home'));

        $this->assertDatabaseHas('contacts', [
            'name' => 'Khách tư vấn kiểm thử',
            'email' => 'tu-van@example.test',
            'subject' => 'Tư vấn chọn sản phẩm',
        ]);
    }

    public function test_homepage_renders_a_before_after_feedback_card_when_a_customer_has_shared_both_images(): void
    {
        Storage::fake('public_media');
        $homepageSettings = app(HomepageSettings::class);
        $homepageSettings->homepage_sections = array_values(array_unique([
            ...$homepageSettings->homepage_sections,
            'testimonials',
        ]));
        $homepageSettings->save();

        $testimonial = Testimonial::query()->create([
            'name' => 'Minh Anh',
            'label' => 'Chia sẻ sau 6 tuần',
            'rating' => 5,
            'content' => 'Da trông đều màu hơn và cảm giác chăm sóc cũng dễ duy trì hơn.',
            'sort_order' => 0,
            'is_active' => true,
        ]);
        $before = $testimonial->addMedia(UploadedFile::fake()->image('before.jpg', 1200, 1500))
            ->toMediaCollection('testimonial_before', 'public_media');
        $after = $testimonial->addMedia(UploadedFile::fake()->image('after.jpg', 1200, 1500))
            ->toMediaCollection('testimonial_after', 'public_media');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Feedback thực tế, xem rõ từng thay đổi')
            ->assertSee('data-before-after', false)
            ->assertSee('Kéo thanh so sánh', false)
            ->assertSee($before->getUrl(), false)
            ->assertSee($after->getUrl(), false)
            ->assertSee('Ảnh do khách hàng chia sẻ');
    }

    public function test_homepage_advice_section_only_uses_home_post_categories_and_has_a_side_slider(): void
    {
        Storage::fake('public_media');

        $homeCategory = PostCategory::query()->create([
            'name' => ['vi' => 'Chăm da chuyên sâu'],
            'is_home' => true,
            'is_active' => true,
        ]);
        $hiddenCategory = PostCategory::query()->create([
            'name' => ['vi' => 'Không hiển thị trang chủ'],
            'is_home' => false,
            'is_active' => true,
        ]);

        foreach (range(1, 5) as $position) {
            $post = Post::query()->create([
                'post_category_id' => $homeCategory->id,
                'name' => ['vi' => "Bài tư vấn trang chủ {$position}"],
                'summary' => ['vi' => "Mô tả ngắn cho bài tư vấn {$position}."],
                'content' => ['vi' => '<p>Nội dung bài viết.</p>'],
                'is_featured' => $position === 1,
                'is_active' => true,
                'published_at' => now()->subDays($position),
            ]);
            $post->addMedia(UploadedFile::fake()->image("advice-{$position}.jpg", 1200, 800))
                ->toMediaCollection('post_image', 'public_media');
        }

        Post::query()->create([
            'post_category_id' => $hiddenCategory->id,
            'name' => ['vi' => 'Bài viết không lên trang chủ'],
            'summary' => ['vi' => 'Không được hiển thị trong khối tư vấn.'],
            'content' => ['vi' => '<p>Nội dung bài viết.</p>'],
            'is_active' => true,
            'published_at' => now(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('home-advice-layout', false)
            ->assertSee('data-home-advice-swiper', false)
            ->assertSee('Bài tư vấn trang chủ 1')
            ->assertSee('Bài tư vấn trang chủ 5')
            ->assertDontSee('Bài viết không lên trang chủ');
    }

    public function test_catalog_filters_and_mega_menu_are_driven_by_the_attribute_configuration(): void
    {
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $menuAttribute = ProductAttribute::query()->create([
            'name' => 'Điều hướng kiểm thử',
            'slug' => 'dieu-huong-kiem-thu',
            'sort_order' => 99,
            'is_active' => true,
            'show_in_product_menu' => true,
        ]);
        $menuValue = $menuAttribute->values()->create([
            'value' => 'Giá trị điều hướng kiểm thử',
            'slug' => 'gia-tri-dieu-huong-kiem-thu',
            'sort_order' => 0,
        ]);
        $product->attributeValues()->syncWithoutDetaching([$menuValue->id]);

        $this->get(route('catalog', [
            'attribute_values' => [$menuAttribute->id => [$menuValue->id]],
        ]))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee($menuAttribute->name)
            ->assertSee($menuValue->value);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($menuAttribute->name)
            ->assertSee($menuValue->value);
    }

    public function test_product_cards_distinguish_preorder_from_out_of_stock(): void
    {
        $product = Product::query()
            ->with(['variants' => fn ($query) => $query->where('is_active', true)->orderByDesc('is_default')->orderBy('id')])
            ->where('is_active', true)
            ->get()
            ->first(fn (Product $product) => $product->variants->isNotEmpty());
        $this->assertNotNull($product);
        $variant = $product->variants->first();

        $product->update(['track_inventory' => true, 'allow_preorder' => false]);
        $variant->update(['stock' => 0]);

        $this->get(route('catalog', ['q' => $product->name]))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Tạm hết hàng')
            ->assertDontSee('Hàng đặt trước');

        $product->update(['allow_preorder' => true]);

        $this->get(route('catalog', ['q' => $product->name]))
            ->assertOk()
            ->assertSee('Nhận đặt trước');
    }

    public function test_zalo_uses_the_local_svg_icon_in_the_footer_and_floating_contact_button(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $this->assertSame(2, substr_count($response->getContent(), 'assets/images/zalo.svg'));
    }

    public function test_homepage_uses_the_featured_product_flag_and_caps_featured_grid_at_fifteen_products(): void
    {
        [$featuredProduct, $nonFeaturedHomeProduct] = Product::query()
            ->where('is_active', true)
            ->visibleOnSite()
            ->take(2)
            ->get()
            ->all();
        $hiddenCategory = ProductCategory::query()
            ->where('is_home', true)
            ->whereHas('products')
            ->firstOrFail();

        $featuredProduct->update(['is_featured' => true, 'is_home' => false]);
        $nonFeaturedHomeProduct->update(['is_featured' => false, 'is_home' => true]);
        $hiddenCategory->update(['is_home' => false]);

        $response = $this->get(route('home'))->assertOk();
        $featuredProducts = collect($response->viewData('featuredProducts'));
        $categories = collect($response->viewData('categories'));

        $this->assertLessThanOrEqual(15, $featuredProducts->count());
        $this->assertContains($featuredProduct->id, $featuredProducts->pluck('id')->all());
        $this->assertNotContains($nonFeaturedHomeProduct->id, $featuredProducts->pluck('id')->all());
        $this->assertNotContains($hiddenCategory->slug, $categories->pluck('slug')->all());
    }

    public function test_homepage_only_shows_category_pills_that_have_visible_products(): void
    {
        $parent = ProductCategory::query()->where('slug', 'cham-soc-mat')->firstOrFail();
        $emptyCategory = ProductCategory::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Danh mục không có sản phẩm',
            'slug' => 'danh-muc-khong-co-san-pham',
            'sort_order' => 999,
            'is_active' => true,
        ]);
        $nonHomeProduct = Product::query()
            ->whereHas('category', fn ($query) => $query->where('slug', 'tay-trang'))
            ->firstOrFail();
        $nonHomeCategorySlug = $nonHomeProduct->category->slug;
        $nonHomeProduct->update(['is_home' => false]);
        $hiddenRootCategory = ProductCategory::query()->where('slug', 'cham-soc-co-the')->firstOrFail();
        $hiddenRootCategory->update(['is_home' => false]);

        $response = $this->get(route('home'))->assertOk();
        $homeProductSections = collect($response->viewData('homeProductSections'));
        $faceSection = $homeProductSections->firstWhere('slug', $parent->slug);
        $categoryTabs = collect(data_get($faceSection, 'tabs', []));
        $allProductsTab = $categoryTabs->first();
        $expectedHomeProductCount = Product::query()
            ->where('is_active', true)
            ->where('is_home', true)
            ->visibleOnSite()
            ->whereIn('product_category_id', ProductCategory::query()
                ->where('id', $parent->id)
                ->orWhere('parent_id', $parent->id)
                ->pluck('id'))
            ->count();

        $this->assertNotNull($faceSection);
        $this->assertTrue($homeProductSections->every(fn (array $section): bool => ProductCategory::query()->where('slug', $section['slug'])->whereNull('parent_id')->exists()));
        $this->assertNotContains($hiddenRootCategory->slug, $homeProductSections->pluck('slug')->all());
        $this->assertSame('Tất cả', data_get($allProductsTab, 'name'));
        $this->assertCount($expectedHomeProductCount, data_get($allProductsTab, 'products', []));
        $this->assertNotContains($emptyCategory->slug, $categoryTabs->pluck('slug')->all());
        $this->assertNotContains($nonHomeCategorySlug, $categoryTabs->pluck('slug')->all());
        $this->assertTrue($categoryTabs->every(fn (array $tab): bool => collect($tab['products'])->isNotEmpty()));
        $response->assertDontSee($emptyCategory->name);
    }

    public function test_homepage_loads_and_displays_the_brand_logo(): void
    {
        $brand = Brand::query()->create([
            'name' => 'Thương hiệu không có sản phẩm',
            'slug' => 'thuong-hieu-khong-co-san-pham',
            'logo' => 'uploads/brands/logo-trang-chu.webp',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $response = $this->get(route('home'))->assertOk();
        $homepageBrands = collect($response->viewData('brands'));

        $this->assertSame('uploads/brands/logo-trang-chu.webp', $homepageBrands->firstWhere('id', $brand->id)?->logo);
        $response->assertSee(asset('uploads/brands/logo-trang-chu.webp'), false);
    }

    public function test_news_detail_uses_nine_three_layout_with_sidebar_and_cms_content(): void
    {
        $post = Post::query()->with('category')->where('is_active', true)->firstOrFail();
        $domain = $post->category?->slug ?: 'tin-tuc';

        $response = $this->get(route('content.show', ['domain' => $domain, 'slug' => $post->slug]));

        $response->assertOk()
            ->assertSee('class="col-lg-9"', false)
            ->assertSee('class="col-lg-3"', false)
            ->assertSee('news-detail-sidebar', false)
            ->assertSee('Bài viết mới')
            ->assertSee('Nhận bài viết mới')
            ->assertSee($post->getTranslation('content', 'vi'), false)
            ->assertDontSee('Nội dung này là dữ liệu mẫu cho giao diện.');

        if ($post->category) {
            $response
                ->assertSee($post->category->getTranslation('name', 'vi'))
                ->assertSee(route('content.show', ['domain' => 'tin-tuc', 'slug' => $post->category->slug]), false);
        }

        $this->assertStringContainsString(
            "@extends('layouts.master')",
            file_get_contents(resource_path('views/frontend/posts/detail.blade.php')),
        );
    }

    public function test_only_vietnamese_is_available_and_travel_tables_are_removed(): void
    {
        $this->assertSame(['vi'], Language::pluck('code')->all());
        $this->assertSame(0, DB::table('settings')->where('payload', 'like', '%\"en\"%')->count());
        foreach (['tours', 'tour_categories', 'tour_itineraries', 'destinations', 'services', 'service_categories', 'service_packages'] as $table) {
            $this->assertFalse(Schema::hasTable($table), "Bảng {$table} phải được xóa.");
        }
        $this->assertFalse(collect(app('router')->getRoutes())->contains(fn ($route) => str_contains((string) $route->getName(), 'languages')));
    }
}
