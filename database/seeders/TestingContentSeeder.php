<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\ComboCategory;
use App\Models\ContactChannel;
use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductOption;
use App\Models\User;
use App\Settings\ContactSettings;
use App\Settings\HomepageSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Dữ liệu giả lập trung tính chỉ phục vụ automated tests.
 * Seeder này không chạy ở local/staging/production.
 */
class TestingContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedLanguage();
        [$categories, $brands] = $this->seedCatalog();
        $this->seedContent();
        $this->seedCommerce($categories, $brands);
        $this->enableTestHomepageSections();
    }

    private function seedUsers(): void
    {
        $admin = User::query()->firstOrCreate(['email' => 'admin@thtmedia.test'], [
            'name' => 'Quản trị kiểm thử',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole(Role::query()->where('name', 'admin')->firstOrFail());

        $customer = User::query()->firstOrCreate(['email' => 'customer@thtmedia.test'], [
            'name' => 'Khách hàng kiểm thử',
            'phone' => '0900000000',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $customer->assignRole(Role::query()->where('name', 'customer')->firstOrFail());
    }

    private function seedLanguage(): void
    {
        Language::query()->updateOrCreate(['code' => 'vi'], [
            'name' => 'Vietnamese',
            'native_name' => 'Tiếng Việt',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    /** @return array{0: array<string, ProductCategory>, 1: array<int, Brand>} */
    private function seedCatalog(): array
    {
        $equipment = ProductCategory::query()->create([
            'name' => 'Thiết bị truyền thông',
            'slug' => 'thiet-bi-truyen-thong',
            'sort_order' => 10,
            'is_featured' => true,
            'is_home' => true,
            'is_active' => true,
        ]);
        $publications = ProductCategory::query()->create([
            'name' => 'Ấn phẩm',
            'slug' => 'an-pham',
            'sort_order' => 20,
            'is_featured' => true,
            'is_home' => true,
            'is_active' => true,
        ]);
        $camera = ProductCategory::query()->create([
            'parent_id' => $equipment->id,
            'name' => 'Máy quay',
            'slug' => 'may-quay',
            'sort_order' => 10,
            'is_home' => true,
            'is_active' => true,
        ]);
        $accessory = ProductCategory::query()->create([
            'parent_id' => $equipment->id,
            'name' => 'Phụ kiện',
            'slug' => 'phu-kien',
            'sort_order' => 20,
            'is_home' => true,
            'is_active' => true,
        ]);

        $brands = collect([
            ['name' => 'THT Studio', 'slug' => 'tht-studio', 'sort_order' => 10],
            ['name' => 'Media Partner', 'slug' => 'media-partner', 'sort_order' => 20],
        ])->map(fn (array $data) => Brand::query()->create($data + ['is_featured' => true, 'is_active' => true]))->all();

        $options = collect([
            ['dinh-dang', 'Định dạng', ['Tiêu chuẩn', 'Nâng cao']],
            ['mau-sac', 'Màu sắc', ['Đen', 'Trắng']],
            ['kich-thuoc', 'Kích thước', ['Nhỏ', 'Lớn']],
            ['goi-dich-vu', 'Gói dịch vụ', ['Cơ bản', 'Mở rộng']],
        ])->map(function (array $definition, int $index) {
            [$slug, $name, $values] = $definition;
            $option = ProductOption::query()->create([
                'name' => $name,
                'slug' => $slug,
                'display_type' => 'button',
                'sort_order' => ($index + 1) * 10,
                'is_active' => true,
            ]);
            foreach ($values as $valueIndex => $value) {
                $option->values()->create(['value' => $value, 'slug' => str($value)->ascii()->slug(), 'sort_order' => $valueIndex]);
            }

            return $option;
        });

        $format = ProductAttribute::query()->create([
            'name' => 'Loại nội dung',
            'slug' => 'loai-noi-dung',
            'show_in_product_menu' => true,
            'is_active' => true,
            'sort_order' => 10,
        ]);
        $videoValue = $format->values()->create(['value' => 'Video', 'slug' => 'video', 'sort_order' => 10]);
        $format->values()->create(['value' => 'Hình ảnh', 'slug' => 'hinh-anh', 'sort_order' => 20]);

        foreach ([$camera, $accessory] as $category) {
            $category->options()->sync($options->pluck('id'));
            $category->attributes()->sync([$format->id]);
        }

        $productDefinitions = [
            [$camera, $brands[0], 'Bộ máy quay kiểm thử', 'THT-CAM-01', 1200000, true, true],
            [$accessory, $brands[1], 'Thiết bị ghi hình kiểm thử', 'THT-CAM-02', 900000, true, true],
            [$accessory, $brands[0], 'Phụ kiện ghi âm kiểm thử', 'THT-AUD-01', 450000, true, false],
            [$accessory, $brands[1], 'Bộ đèn kiểm thử', 'THT-LIGHT-01', 650000, false, true],
            [$publications, $brands[0], 'Ấn phẩm kiểm thử', 'THT-PRINT-01', 150000, false, true],
        ];

        foreach ($productDefinitions as $index => [$category, $brand, $name, $sku, $price, $featured, $home]) {
            $product = Product::query()->create([
                'product_category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => $name,
                'slug' => str($name)->ascii()->slug(),
                'summary' => 'Dữ liệu trung tính phục vụ kiểm thử chức năng.',
                'description' => '<p>Nội dung sản phẩm dùng trong môi trường kiểm thử.</p>',
                'sold_count' => 10 + $index,
                'status' => 'active',
                'track_inventory' => true,
                'is_featured' => $featured,
                'is_home' => $home,
                'is_active' => true,
                'published_at' => now()->subDays($index),
            ]);
            $product->variants()->create([
                'name' => 'Mặc định',
                'sku' => $sku,
                'price' => $price,
                'compare_price' => $featured ? $price + 100000 : null,
                'stock' => 20,
                'is_default' => true,
                'is_active' => true,
            ]);
            $product->attributeValues()->sync([$videoValue->id]);
        }

        return [[
            'equipment' => $equipment,
            'publications' => $publications,
            'camera' => $camera,
            'accessory' => $accessory,
        ], $brands];
    }

    private function seedContent(): void
    {
        $category = PostCategory::query()->where('name->vi', 'Tin tức')->firstOrFail();
        foreach ([
            'Xu hướng nội dung số',
            'Quy trình sản xuất truyền thông',
            'Xây dựng nhận diện nhất quán',
            'Tối ưu điểm chạm thương hiệu',
        ] as $index => $title) {
            Post::query()->create([
                'post_category_id' => $category->id,
                'name' => ['vi' => $title],
                'summary' => ['vi' => 'Nội dung trung tính phục vụ kiểm thử.'],
                'content' => ['vi' => '<p>Bài viết phục vụ kiểm thử chức năng CMS.</p>'],
                'is_featured' => $index === 0,
                'is_active' => true,
                'published_at' => now()->subDays($index),
            ]);
        }
    }

    private function seedCommerce(array $categories, array $brands): void
    {
        $contact = app(ContactSettings::class);
        $contact->zalo = 'https://zalo.me/0900000000';
        $contact->save();

        ContactChannel::query()->create([
            'name' => 'Zalo kiểm thử',
            'type' => 'zalo',
            'value' => '0900000000',
            'url' => 'https://zalo.me/0900000000',
            'show_footer' => true,
            'show_floating' => true,
            'is_active' => true,
            'sort_order' => 10,
        ]);

        ComboCategory::query()->create([
            'name' => 'Gói nội dung',
            'slug' => 'goi-noi-dung',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        Coupon::query()->create([
            'code' => 'THT10',
            'name' => 'Ưu đãi kiểm thử',
            'type' => 'percent',
            'value' => 10,
            'minimum_order' => 100000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $sale = FlashSale::query()->create([
            'name' => 'Flash Sale kiểm thử',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'is_active' => true,
        ]);
        $product = Product::query()->firstOrFail();
        $sale->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $product->default_variant->id,
            'sale_price' => 1000000,
            'quantity' => 10,
            'sold' => 1,
        ]);
    }

    private function enableTestHomepageSections(): void
    {
        $homepage = app(HomepageSettings::class);
        $homepage->homepage_sections = ['categories', 'flash_sale', 'featured_products', 'brands', 'testimonials', 'posts'];
        $homepage->homepage_section_titles = [
            'categories' => ['vi' => 'Danh mục sản phẩm'],
            'flash_sale' => ['vi' => 'Flash Sale'],
            'featured_products' => ['vi' => 'Sản phẩm nổi bật'],
            'brands' => ['vi' => 'Thương hiệu đồng hành'],
            'testimonials' => ['vi' => 'Khách hàng nói về chúng tôi'],
            'posts' => ['vi' => 'Tin tức và góc nhìn'],
        ];
        $homepage->save();
    }
}
