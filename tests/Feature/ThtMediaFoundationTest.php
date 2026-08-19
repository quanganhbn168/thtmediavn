<?php

namespace Tests\Feature;

use App\Models\PostCategory;
use App\Models\Service;
use App\Models\CompanyContent;
use App\Settings\CompanySettings;
use App\Settings\ContactSettings;
use App\Settings\HomepageSettings;
use App\Settings\SeoSettings;
use App\Settings\TrackingSettings;
use App\Settings\WebsiteSettings;
use App\Services\CompanyContentService;
use App\Services\WebsiteSettingsService;
use Database\Seeders\ThtMediaFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ThtMediaFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_foundation_seeder_creates_the_tht_media_scaffold(): void
    {
        $this->seed(ThtMediaFoundationSeeder::class);

        $this->assertSame('THT MEDIA VN', app(CompanySettings::class)->company_name);
        $this->assertSame('THT MEDIA VN', app(WebsiteSettings::class)->site_name['vi']);
        $this->assertFalse(app(WebsiteSettings::class)->multilingual_enabled);
        $this->assertNull(app(ContactSettings::class)->phone);
        $this->assertSame('THT MEDIA VN', app(SeoSettings::class)->seo_title['vi']);
        $this->assertContains('services', app(HomepageSettings::class)->homepage_sections);
        $this->assertContains('projects', app(HomepageSettings::class)->homepage_sections);
        $this->assertContains('consultation', app(HomepageSettings::class)->homepage_sections);
        $this->assertSame(8, Service::query()->count());
        $this->assertDatabaseHas('slugs', [
            'slug' => 'tin-tuc',
            'sluggable_type' => PostCategory::class,
        ]);

        foreach (['products', 'brands', 'orders', 'payments', 'carts', 'combos', 'contact_channels'] as $table) {
            $this->assertFalse(Schema::hasTable($table), "Bảng {$table} không thuộc khung THTMedia.");
        }
    }

    public function test_foundation_seeder_is_idempotent_and_preserves_cms_content(): void
    {
        PostCategory::query()->create([
            'name' => ['vi' => 'Danh mục quản trị'],
            'is_active' => true,
            'sort_order' => 20,
        ]);

        $this->seed(ThtMediaFoundationSeeder::class);
        $this->seed(ThtMediaFoundationSeeder::class);

        $this->assertDatabaseCount('post_categories', 2);
        $this->assertSame(1, PostCategory::query()->where('name->vi', 'Danh mục quản trị')->count());
    }

    public function test_public_home_uses_the_tht_media_identity(): void
    {
        $this->seed();

        $this->get(route('home'))->assertOk()->assertSee('THT MEDIA VN');
    }

    public function test_about_page_renders_company_content_records(): void
    {
        $this->seed();

        CompanyContent::query()->create([
            'type' => 'team',
            'slug' => 'doi-ngu-san-xuat',
            'title' => ['vi' => 'Đội ngũ sản xuất'],
            'summary' => ['vi' => 'Phụ trách kết nối tiền kỳ, hiện trường và hậu kỳ.'],
            'is_active' => true,
        ]);
        CompanyContent::query()->create([
            'type' => 'facility',
            'slug' => 'studio-san-xuat',
            'title' => ['vi' => 'Studio sản xuất'],
            'summary' => ['vi' => 'Không gian phục vụ quay, chụp và livestream.'],
            'is_active' => true,
        ]);
        CompanyContent::query()->create([
            'type' => 'faq',
            'slug' => 'tht-media-trien-khai-tu-giai-doan-nao',
            'title' => ['vi' => 'THT Media nhận triển khai từ giai đoạn nào?'],
            'content' => ['vi' => '<p>Từ định hướng, sản xuất đến bàn giao.</p>'],
            'is_active' => true,
        ]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Đội ngũ sản xuất')
            ->assertSee('Studio sản xuất')
            ->assertSee('THT Media nhận triển khai từ giai đoạn nào?')
            ->assertSee('Từ định hướng, sản xuất đến bàn giao.');
    }

    public function test_company_content_has_an_article_like_detail_page(): void
    {
        $this->seed();

        $content = CompanyContent::query()->create([
            'type' => 'article',
            'slug' => 'cau-chuyen-thuong-hieu',
            'title' => ['vi' => 'Câu chuyện thương hiệu'],
            'summary' => ['vi' => 'Một câu chuyện ngắn về THT Media.'],
            'content' => ['vi' => '<p>Nội dung bài viết công ty.</p>'],
            'is_active' => true,
        ]);

        $this->get(route('about.content.show', ['slug' => $content->routeSlug('vi')]))
            ->assertOk()
            ->assertSee('Câu chuyện thương hiệu')
            ->assertSee('Nội dung bài viết công ty.');

        $this->assertSame('cau-chuyen-thuong-hieu', $content->getSlug('vi'));
        $this->assertDatabaseHas('slugs', [
            'sluggable_type' => CompanyContent::class,
            'sluggable_id' => $content->id,
            'locale' => 'vi',
            'slug' => 'cau-chuyen-thuong-hieu',
        ]);
    }

    public function test_company_content_slug_is_unique_across_company_contents(): void
    {
        $this->seed();
        $service = app(CompanyContentService::class);

        $first = $service->create([
            'title' => ['vi' => 'Bài viết trùng đường dẫn'],
            'is_active' => true,
        ]);
        $second = $service->create([
            'title' => ['vi' => 'Bài viết trùng đường dẫn'],
            'is_active' => true,
        ]);

        $this->assertSame('bai-viet-trung-duong-dan', $first->routeSlug('vi'));
        $this->assertSame('bai-viet-trung-duong-dan-1', $second->routeSlug('vi'));
    }

    public function test_contact_directory_renders_multiple_phones_and_active_branches(): void
    {
        $this->seed();

        $contact = app(ContactSettings::class);
        $contact->address = 'Trụ sở chính';
        $contact->phone = '0900000000';
        $contact->phones = [
            ['label' => 'Hotline', 'number' => '0900000000', 'is_primary' => true],
            ['label' => 'Kinh doanh', 'number' => '0911111111', 'is_primary' => false],
        ];
        $contact->branches = [
            ['name' => 'Chi nhánh Hà Nội', 'address' => 'Hà Nội', 'is_active' => true],
            ['name' => 'Chi nhánh ẩn', 'address' => 'Không hiển thị', 'is_active' => false],
        ];
        $contact->save();
        app(WebsiteSettingsService::class)->refresh();

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('0900000000')
            ->assertSee('0911111111')
            ->assertSee('Chi nhánh Hà Nội')
            ->assertDontSee('Chi nhánh ẩn')
            ->assertDontSee('Không hiển thị');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Trụ sở chính:')
            ->assertSee('Chi nhánh Hà Nội')
            ->assertSee('Hà Nội')
            ->assertSee('0911111111');
    }

    public function test_frontend_master_only_renders_generic_metadata(): void
    {
        $this->seed();
        $master = File::get(resource_path('views/layouts/master.blade.php'));

        $this->assertStringNotContainsString('SearchAction', $master);
        $this->assertStringNotContainsString('product:', $master);
        $this->assertStringNotContainsString('san-pham', $master);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<html lang="'.str_replace('_', '-', app()->currentLocale()).'">', false)
            ->assertSee('property="og:type" content="website"', false)
            ->assertSee('schema.org', false);
    }

    public function test_tracking_codes_are_rendered_in_their_frontend_locations(): void
    {
        $this->seed();

        $tracking = app(TrackingSettings::class);
        $tracking->head_code = '<meta name="tracking-head-test" content="1">';
        $tracking->body_open_code = '<div id="tracking-body-open-test"></div>';
        $tracking->body_close_code = '<div id="tracking-body-close-test"></div>';
        $tracking->meta_pixel_code = '<script id="meta-pixel-test">window.__metaPixelTest = true;</script>';
        $tracking->save();

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('tracking-head-test', $html);
        $this->assertStringContainsString('tracking-body-open-test', $html);
        $this->assertStringContainsString('tracking-body-close-test', $html);
        $this->assertStringContainsString('id="meta-pixel-test"', $html);
        $this->assertLessThan(strpos($html, '</head>'), strpos($html, 'tracking-head-test'));
        $this->assertGreaterThan(strpos($html, '<body>'), strpos($html, 'tracking-body-open-test'));
        $this->assertLessThan(strpos($html, '</body>'), strpos($html, 'tracking-body-close-test'));
    }
}
