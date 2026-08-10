<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\PostCategory;
use App\Models\ProductCategory;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\HomepageSettings;
use App\Settings\SeoSettings;
use Database\Seeders\ThtMediaFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThtMediaFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_foundation_seeder_creates_only_tht_media_content(): void
    {
        $this->seed(ThtMediaFoundationSeeder::class);

        $general = app(GeneralSettings::class);
        $contact = app(ContactSettings::class);
        $seo = app(SeoSettings::class);
        $homepage = app(HomepageSettings::class);

        $this->assertSame('THT MEDIA VN', $general->site_name['vi']);
        $this->assertSame('THT MEDIA VN', $contact->company_name);
        $this->assertNull($contact->phone);
        $this->assertNull($contact->email);
        $this->assertSame('THT MEDIA VN', $seo->seo_title['vi']);
        $this->assertSame(['posts'], $homepage->homepage_sections);
        $this->assertDatabaseHas('slugs', [
            'slug' => 'tin-tuc',
            'sluggable_type' => PostCategory::class,
        ]);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('brands', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('contact_channels', 0);
    }

    public function test_foundation_seeder_is_idempotent_and_preserves_existing_catalog_records(): void
    {
        ProductCategory::query()->create([
            'name' => 'Danh mục do quản trị tạo',
            'slug' => 'danh-muc-quan-tri',
            'is_active' => true,
        ]);
        Brand::query()->create([
            'name' => 'Thương hiệu do quản trị tạo',
            'slug' => 'thuong-hieu-quan-tri',
            'is_active' => true,
        ]);

        $this->seed(ThtMediaFoundationSeeder::class);
        $this->seed(ThtMediaFoundationSeeder::class);

        $this->assertDatabaseCount('post_categories', 1);
        $this->assertDatabaseHas('product_categories', ['slug' => 'danh-muc-quan-tri']);
        $this->assertDatabaseHas('brands', ['slug' => 'thuong-hieu-quan-tri']);
    }

    public function test_public_home_uses_the_tht_media_identity(): void
    {
        $this->seed(ThtMediaFoundationSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('THT MEDIA VN');
    }
}
