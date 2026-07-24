<?php

namespace Tests\Feature;

use App\Models\MtdProductSource;
use App\Models\MtdVariantSource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MtdProductImportTest extends TestCase
{
    use RefreshDatabase;

    private string $jsonPath;

    protected function setUp(): void
    {
        parent::setUp();

        ProductCategory::create([
            'name' => 'Khác',
            'slug' => 'khac',
            'sort_order' => 0,
            'is_featured' => false,
            'is_active' => true,
        ]);

        $directory = storage_path('framework/testing/mtd-import');
        File::ensureDirectoryExists($directory);
        $this->jsonPath = $directory.'/products.json';
        File::put($this->jsonPath, json_encode([$this->productPayload()], JSON_UNESCAPED_UNICODE));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(dirname($this->jsonPath));
        parent::tearDown();
    }

    public function test_dry_run_does_not_write_to_database(): void
    {
        $this->artisan('mtd:import', ['--path' => $this->jsonPath, '--dry-run' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('mtd_product_sources', 0);
    }

    public function test_import_is_idempotent_and_keeps_new_products_in_draft(): void
    {
        $this->artisan('mtd:import', ['--path' => $this->jsonPath])->assertSuccessful();
        $this->artisan('mtd:import', ['--path' => $this->jsonPath])->assertSuccessful();

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('product_variants', 1);
        $this->assertDatabaseCount('mtd_product_sources', 1);
        $this->assertDatabaseCount('mtd_variant_sources', 1);

        $product = Product::firstOrFail();
        $variant = ProductVariant::firstOrFail();
        $source = MtdProductSource::firstOrFail();

        $this->assertSame('draft', $product->status);
        $this->assertFalse($product->is_active);
        $this->assertTrue($product->track_inventory);
        $this->assertSame(0, $variant->stock);
        $this->assertSame('250000.00', $variant->price);
        $this->assertSame('8809755466534', $variant->barcode);
        $this->assertNull($variant->sku);
        $this->assertSame($product->id, $source->product_id);
        $this->assertNotNull(MtdVariantSource::first()->last_synced_at);
        $this->assertStringNotContainsString('<script', (string) $product->description);
    }

    public function test_collection_pages_and_products_without_prices_are_skipped(): void
    {
        $collection = $this->productPayload([
            'slug' => 'cham-soc-mat',
            'brand' => null,
            'sku' => null,
            'price' => null,
            'local_images' => [],
            'variants' => [['name' => 'Default Title', 'price' => null]],
        ]);
        $missingPrice = $this->productPayload([
            'slug' => 'san-pham-thieu-gia',
            'price' => null,
            'variants' => [['name' => 'Default Title', 'price' => null]],
        ]);
        File::put($this->jsonPath, json_encode([$collection, $missingPrice], JSON_UNESCAPED_UNICODE));

        $this->artisan('mtd:import', ['--path' => $this->jsonPath])->assertSuccessful();

        $this->assertDatabaseCount('products', 0);
    }

    public function test_adopted_products_never_receive_import_managed_images(): void
    {
        Storage::fake('public_media');
        $product = Product::create([
            'product_category_id' => ProductCategory::firstOrFail()->id,
            'name' => 'Kem dưỡng thử nghiệm',
            'slug' => 'kem-duong-thu-nghiem',
            'description' => '<p>Nội dung thủ công</p>',
            'status' => 'active',
            'track_inventory' => true,
            'is_active' => true,
        ]);
        $product->variants()->create([
            'name' => 'Mặc định',
            'price' => 250000,
            'stock' => 12,
            'is_default' => true,
            'is_active' => true,
        ]);

        $manualImage = dirname($this->jsonPath).'/manual.png';
        File::put($manualImage, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));
        $product->addMedia($manualImage)->preservingOriginal()->toMediaCollection('product_images', 'public_media');

        $sourceImage = dirname($this->jsonPath).'/images/01.png';
        File::ensureDirectoryExists(dirname($sourceImage));
        File::put($sourceImage, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));
        $payload = $this->productPayload([
            'local_images' => ['mtd-import/images/01.png'],
            'images' => ['https://example.com/01.png'],
        ]);
        File::put($this->jsonPath, json_encode([$payload], JSON_UNESCAPED_UNICODE));

        $arguments = [
            '--path' => $this->jsonPath,
            '--with-images' => true,
            '--adopt-existing' => true,
        ];
        $this->artisan('mtd:import', $arguments)->assertSuccessful();
        $this->artisan('mtd:import', $arguments)->assertSuccessful();

        $product->refresh();
        $this->assertTrue($product->mtdSource->is_adopted);
        $this->assertSame(12, $product->variants()->firstOrFail()->stock);
        $this->assertCount(1, $product->getMedia('product_images'));
        $this->assertSame(0, $product->getMedia('product_images')->filter(
            fn ($media) => $media->getCustomProperty('import_source') === 'mtd'
        )->count());
    }

    private function productPayload(array $overrides = []): array
    {
        return array_replace([
            'source_url' => 'https://mtd-global.com/kem-duong-thu-nghiem',
            'canonical_url' => 'https://mtd-global.com/kem-duong-thu-nghiem',
            'slug' => 'kem-duong-thu-nghiem',
            'name' => 'Kem dưỡng thử nghiệm',
            'brand' => 'DERMAXILIC',
            'sku' => '8809755466534',
            'product_type' => 'Không xác định',
            'stock_status' => 'in_stock',
            'price' => 250000,
            'compare_at_price' => 300000,
            'currency' => 'VND',
            'meta_description' => 'Mô tả ngắn',
            'description_html' => '<p>Mô tả <strong>sản phẩm</strong></p><script>alert(1)</script>',
            'images' => [],
            'local_images' => [],
            'variants' => [[
                'source_variant_id' => '212029298',
                'name' => 'Default Title',
                'sku' => '8809755466534',
                'price' => 250000,
                'compare_at_price' => 300000,
                'available' => true,
                'is_default' => true,
            ]],
            'scraped_at' => '2026-07-12T23:38:00+07:00',
        ], $overrides);
    }
}
