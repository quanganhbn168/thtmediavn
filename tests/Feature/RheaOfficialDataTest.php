<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Services\WebsiteSettingsService;
use App\Settings\AboutSettings;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use Database\Seeders\RheaOfficialDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RheaOfficialDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_rhea_settings_and_catalog_taxonomy_are_seeded_from_official_data(): void
    {
        $general = app(GeneralSettings::class);
        $contact = app(ContactSettings::class);
        $about = app(AboutSettings::class);

        $this->assertSame('RHEA SKINLAB', $general->site_name['vi']);
        $this->assertSame('Công ty TNHH Quốc tế RHEA SKINLAB', $contact->company_name);
        $this->assertSame('0110395713', $contact->tax_code);
        $this->assertSame('https://zalo.me/0395686598', $contact->zalo);
        $this->assertNull($contact->facebook);
        $this->assertNotEmpty($about->about_history['vi']);
        $this->assertStringContainsString('Chính hãng', $about->about_core_values['vi']);

        $this->assertDatabaseHas('product_categories', ['slug' => 'cham-soc-mat', 'name' => 'Chăm sóc da']);
        $this->assertDatabaseHas('product_categories', ['slug' => 'toner', 'name' => 'Toner']);
        $this->assertDatabaseHas('product_categories', ['slug' => 'duong-the', 'name' => 'Kem dưỡng ẩm']);
        $this->assertDatabaseHas('product_categories', ['slug' => 'u-trang', 'name' => 'Ủ trắng']);

        $this->assertSame(
            ['AA Cosmetic', 'Belif', 'Bioderma', 'CNP', "Cok'lear", 'KyungLab', 'Topla'],
            Brand::query()->where('is_featured', true)->orderBy('name')->pluck('name')->all(),
        );
        $this->assertDatabaseHas('product_attribute_values', ['slug' => 'xin-mau', 'value' => 'Xỉn màu']);
        $this->assertFalse(ProductAttributeValue::query()->whereIn('value', ['Da sỉ màu', 'Sỉ màu'])->exists());

        $home = $this->get(route('home'))->assertOk();
        ProductAttributeValue::query()
            ->whereIn('slug', ['da-dau', 'da-kho', 'da-hon-hop', 'da-nhay-cam', 'mun', 'lao-hoa', 'xin-mau', 'thieu-nuoc'])
            ->get()
            ->each(function (ProductAttributeValue $value) use ($home): void {
                $home->assertSee(route('catalog', [
                    'attribute_values' => [$value->product_attribute_id => [$value->id]],
                ]), false);
            });
    }

    public function test_rhea_seeder_is_idempotent_and_preserves_existing_catalog_records(): void
    {
        $before = [
            'products' => Product::count(),
            'variants' => ProductVariant::count(),
            'categories' => ProductCategory::count(),
            'brands' => Brand::count(),
            'settings' => DB::table('settings')->count(),
        ];
        $productIds = Product::orderBy('id')->pluck('id')->all();

        $this->seed(RheaOfficialDataSeeder::class);
        $this->seed(RheaOfficialDataSeeder::class);

        $this->assertSame($before, [
            'products' => Product::count(),
            'variants' => ProductVariant::count(),
            'categories' => ProductCategory::count(),
            'brands' => Brand::count(),
            'settings' => DB::table('settings')->count(),
        ]);
        $this->assertSame($productIds, Product::orderBy('id')->pluck('id')->all());
    }

    public function test_seeded_content_is_available_on_public_pages(): void
    {
        $product = Product::query()->firstOrFail();
        $post = Post::query()->with('slugs')->firstOrFail();

        $this->get(route('catalog'))
            ->assertOk()
            ->assertSee($product->name);
        $this->get(route('product.show', $product->slug))
            ->assertOk();
        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee($post->getTranslation('name', 'vi'));
        $this->get(route('news.show', $post->slug))->assertOk();
        $this->get(route('home'))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_public_pages_do_not_render_previous_company_identity_or_empty_social_links(): void
    {
        foreach ([route('home'), route('about'), route('contact'), route('catalog'), route('news.index')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('Phương Trần')
                ->assertDontSee('MTD GLOBAL')
                ->assertDontSee('href="#"', false);
        }
    }

    public function test_public_identity_is_rendered_from_database_settings(): void
    {
        $general = app(GeneralSettings::class);
        $general->site_name = ['vi' => 'Tên thương hiệu từ database'];
        $general->site_description = ['vi' => 'Mô tả lấy từ database'];
        $general->copyright = ['vi' => 'Bản quyền lấy từ database'];
        $general->save();

        $contact = app(ContactSettings::class);
        $contact->company_name = 'Công ty lấy từ database';
        $contact->phone = '0909 123 456';
        $contact->email = 'database@example.com';
        $contact->save();

        app(WebsiteSettingsService::class)->refresh();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tên thương hiệu từ database')
            ->assertSee('0909 123 456')
            ->assertSee('Bản quyền lấy từ database');

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Công ty lấy từ database')
            ->assertSee('database@example.com');
    }
}
