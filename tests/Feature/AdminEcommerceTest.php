<?php

namespace Tests\Feature;

use App\Models\ProductCategory;
use App\Models\ProductOption;
use App\Models\ProductAttribute;
use App\Models\Product;
use App\Models\Brand;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\BulkActionService;
use App\Services\ThemePaletteService;
use App\Support\AdminPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminEcommerceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void { parent::setUp(); $this->seed(); }

    public function test_every_admin_route_has_an_explicit_permission_mapping(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            $name=$route->getName();
            if(is_string($name)&&str_starts_with($name,'admin.')&&!in_array($name,['admin.login','admin.login.store','admin.common.bulk-action','admin.common.reorder','admin.common.toggle-field','admin.media.upload.editor','admin.media.list','admin.media.upload.temp'],true)) {
                $this->assertNotNull(AdminPermission::requiredForRouteName($name),"Route {$name} chưa có quyền.");
            }
        }
    }

    public function test_every_bulk_resource_has_an_explicit_permission_mapping(): void
    {
        foreach (BulkActionService::resources() as $resource) {
            $this->assertNotEmpty(
                config('admin.resource_permissions.'.$resource),
                "Bulk resource {$resource} chưa có quyền tương ứng.",
            );
        }
    }

    public function test_frontend_theme_palette_is_loaded_from_the_single_config_source(): void
    {
        config()->set('frontend-theme.active', 'ocean_mist');

        $variables = app(ThemePaletteService::class)->currentCssVariables();

        $this->assertSame('#167E95', $variables['--primary']);
        $this->assertSame('22, 126, 149', $variables['--bs-primary-rgb']);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('--primary: #167E95;', false)
            ->assertSee('--bs-primary-rgb: 22, 126, 149;', false);
    }

    public function test_admin_can_manage_ecommerce_catalog(): void
    {
        $admin=User::role('admin')->firstOrFail();
        foreach(['admin.products.index','admin.product-categories.index','admin.brands.index','admin.product-options.index','admin.flash-sales.index','admin.coupons.index'] as $route)$this->actingAs($admin, 'admin')->get(route($route))->assertOk();
        $nextRootOrder = ((int) ProductCategory::query()->whereNull('parent_id')->max('sort_order')) + 1;
        $this->actingAs($admin, 'admin')->post(route('admin.product-categories.store'),[
            'name' => 'Danh mục kiểm thử',
            'description' => 'Mô tả danh mục kiểm thử',
            'is_active' => 1,
        ])->assertRedirect();
        $this->assertDatabaseHas('product_categories',[
            'name' => 'Danh mục kiểm thử',
            'sort_order' => $nextRootOrder,
            'seo_title' => 'Danh mục kiểm thử',
            'seo_description' => 'Mô tả danh mục kiểm thử',
        ]);
    }

    public function test_product_category_create_and_edit_forms_render_and_update(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $category = ProductCategory::query()->where('slug', 'tay-trang')->firstOrFail();
        $originalImage = $category->image;

        $this->actingAs($admin, 'admin')
            ->get(route('admin.product-categories.create'))
            ->assertOk()
            ->assertSee('Thêm danh mục sản phẩm')
            ->assertSee('Đường dẫn')
            ->assertSee('Để trống để tự động xếp cuối')
            ->assertDontSee('data-lte-toggle="card-collapse"', false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.product-categories.edit', $category))
            ->assertOk()
            ->assertSee('Sửa danh mục sản phẩm')
            ->assertSee($category->name)
            ->assertSee('Kéo thả ảnh mới vào đây')
            ->assertSee('Đường dẫn')
            ->assertDontSee('data-lte-toggle="card-collapse"', false);

        $category->update([
            'seo_title' => 'SEO chính chủ danh mục',
            'seo_description' => 'Mô tả SEO chính chủ danh mục',
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.product-categories.update', $category), [
                'parent_id' => $category->parent_id,
                'name' => $category->name,
                'description' => 'Mô tả đã cập nhật',
                'sort_order' => $category->sort_order,
                'is_active' => 0,
                'is_featured' => 1,
                'is_home' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('product_categories', [
            'id' => $category->id,
            'slug' => $category->slug,
            'description' => 'Mô tả đã cập nhật',
            'seo_title' => 'SEO chính chủ danh mục',
            'seo_description' => 'Mô tả SEO chính chủ danh mục',
            'image' => $originalImage,
            'is_active' => false,
            'is_featured' => true,
            'is_home' => true,
        ]);
    }

    public function test_brand_editor_renders_and_persists_uploaded_logo_and_configuration(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $brand = Brand::query()->firstOrFail();
        $filename = 'brand-test-'.Str::uuid().'.png';
        $temporaryPath = 'uploads/tmp/'.$filename;
        File::ensureDirectoryExists(public_path('uploads/tmp'));
        File::put(public_path($temporaryPath), base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.brands.create'))
            ->assertOk()
            ->assertViewIs('admin.brands.create')
            ->assertSee('Thêm thương hiệu')
            ->assertSee('Logo thương hiệu');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.brands.edit', $brand))
            ->assertOk()
            ->assertViewIs('admin.brands.edit')
            ->assertSee('Sửa thương hiệu')
            ->assertSee('Xem trên website')
            ->assertSee(route('catalog', ['brand' => $brand->slug]), false);

        $indexResponse = $this->actingAs($admin, 'admin')->get(route('admin.brands.index'));
        $indexResponse->assertOk()
            ->assertSee('Trang chủ')
            ->assertSee('data-model="Brand"', false)
            ->assertSee('data-field="is_active"', false)
            ->assertSee('data-field="is_featured"', false);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.common.toggle-field'), [
                'model' => 'Brand',
                'id' => $brand->id,
                'field' => 'is_featured',
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'value' => ! (bool) $brand->is_featured]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.brands.update', $brand), [
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => 'Mô tả thương hiệu đã cập nhật',
                'logo' => $temporaryPath,
                'website' => 'https://example.test/brand',
                'sort_order' => 7,
                'is_active' => 0,
                'is_featured' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $permanentPath = 'uploads/brands/'.$filename;
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'description' => 'Mô tả thương hiệu đã cập nhật',
            'logo' => $permanentPath,
            'website' => 'https://example.test/brand',
            'sort_order' => 7,
            'is_active' => false,
            'is_featured' => true,
        ]);
        $this->assertFileExists(public_path($permanentPath));
        $this->assertFileDoesNotExist(public_path($temporaryPath));

        $this->actingAs($admin, 'admin')
            ->put(route('admin.brands.update', $brand), [
                'name' => $brand->name,
                'slug' => $brand->slug,
                'sort_order' => 7,
                'logo_remove' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertNull($brand->fresh()->logo);
        $this->assertFileDoesNotExist(public_path($permanentPath));
    }

    public function test_product_category_image_can_be_replaced_and_removed(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $category = ProductCategory::query()->where('slug', 'tay-trang')->firstOrFail();
        $filename = 'category-test-'.Str::uuid().'.png';
        $temporaryPath = 'uploads/tmp/'.$filename;
        File::ensureDirectoryExists(public_path('uploads/tmp'));
        File::put(public_path($temporaryPath), base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));

        $payload = [
            'parent_id' => $category->parent_id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
            'is_active' => 1,
            'is_featured' => 0,
            'is_home' => 0,
        ];

        $this->actingAs($admin, 'admin')
            ->put(route('admin.product-categories.update', $category), $payload + ['image' => $temporaryPath])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $permanentPath = $category->fresh()->image;
        $this->assertSame('uploads/product-categories/'.$filename, $permanentPath);
        $this->assertFileExists(public_path($permanentPath));
        $this->assertFileDoesNotExist(public_path($temporaryPath));

        $this->actingAs($admin, 'admin')
            ->put(route('admin.product-categories.update', $category), $payload + ['image_remove' => 1])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertNull($category->fresh()->image);
        $this->assertFileDoesNotExist(public_path($permanentPath));
    }

    public function test_product_option_create_and_edit_views_are_independent_and_sync_values(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $name = 'Dung tích kiểm thử '.Str::uuid();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.product-options.create'))
            ->assertOk()
            ->assertViewIs('admin.product_options.create')
            ->assertSee('Thêm thuộc tính sản phẩm')
            ->assertSee('Giá trị thuộc tính');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.product-options.store'), [
                'name' => $name,
                'display_type' => 'color',
                'sort_order' => 4,
                'is_active' => 1,
                'values' => [
                    ['value' => 'Hồng đào', 'color_code' => '#F5B7B1'],
                    ['value' => 'Cam san hô', 'color_code' => '#F39C12'],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $option = ProductOption::query()->where('name', $name)->with('values')->firstOrFail();
        $this->assertSame(2, $option->values->count());
        $this->assertSame('#F5B7B1', $option->values->firstWhere('value', 'Hồng đào')?->color_code);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.product-options.edit', $option))
            ->assertOk()
            ->assertViewIs('admin.product_options.edit')
            ->assertSee('Sửa thuộc tính sản phẩm')
            ->assertSee('Hồng đào');

        $this->actingAs($admin, 'admin')
            ->put(route('admin.product-options.update', $option), [
                'name' => $name,
                'display_type' => 'select',
                'sort_order' => 8,
                'is_active' => 0,
                'values' => [
                    ['id' => $option->values->firstWhere('value', 'Hồng đào')->id, 'value' => 'Hồng đào nhạt'],
                    ['id' => $option->values->firstWhere('value', 'Cam san hô')->id, 'value' => 'Cam san hô'],
                    ['value' => 'Đỏ berry'],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('product_options', [
            'id' => $option->id,
            'display_type' => 'select',
            'sort_order' => 8,
            'is_active' => false,
        ]);
        $this->assertSame(3, $option->fresh()->values()->count());
        $this->assertDatabaseHas('product_option_values', [
            'product_option_id' => $option->id,
            'value' => 'Hồng đào nhạt',
        ]);
    }

    public function test_product_attribute_filter_manager_has_independent_create_and_edit_views(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $name = 'Kết cấu kiểm thử '.Str::uuid();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.product-attributes.create'))
            ->assertOk()
            ->assertViewIs('admin.product_attributes.create')
            ->assertSee('Thêm thuộc tính lọc');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.product-attributes.store'), [
                'name' => $name,
                'values_text' => "Gel\nKem\nSerum",
                'sort_order' => 12,
                'is_active' => 1,
                'show_in_product_menu' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $attribute = ProductAttribute::query()->where('name', $name)->with('values')->firstOrFail();
        $this->assertSame(['Gel', 'Kem', 'Serum'], $attribute->values->pluck('value')->all());

        $this->actingAs($admin, 'admin')
            ->get(route('admin.product-attributes.edit', $attribute))
            ->assertOk()
            ->assertViewIs('admin.product_attributes.edit')
            ->assertSee('Sửa thuộc tính lọc')
            ->assertSee('Serum');

        $this->actingAs($admin, 'admin')
            ->put(route('admin.product-attributes.update', $attribute), [
                'name' => $name,
                'slug' => $attribute->slug,
                'values_text' => "Gel\nKem dưỡng\nSerum",
                'sort_order' => 15,
                'is_active' => 0,
                'show_in_product_menu' => 0,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('product_attributes', [
            'id' => $attribute->id,
            'sort_order' => 15,
            'is_active' => false,
            'show_in_product_menu' => false,
        ]);
        $this->assertDatabaseHas('product_attribute_values', [
            'product_attribute_id' => $attribute->id,
            'value' => 'Kem dưỡng',
        ]);
    }

    public function test_testimonial_manager_is_separate_from_product_reviews(): void
    {
        $admin = User::role('admin')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.testimonials.create'))
            ->assertOk()
            ->assertViewIs('admin.testimonials.create')
            ->assertSee('Thêm cảm nhận khách hàng')
            ->assertSee('Video cảm nhận')
            ->assertDontSee('Feedback trước / sau');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.testimonials.store'), [
                'name' => 'Ngọc Anh',
                'label' => 'Da nhạy cảm · Hà Nội',
                'content' => 'Nội dung testimonial do thương hiệu biên tập.',
                'sort_order' => 4,
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $testimonial = Testimonial::query()->where('name', 'Ngọc Anh')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.testimonials.edit', $testimonial))
            ->assertOk()
            ->assertViewIs('admin.testimonials.edit')
            ->assertSee('Sửa cảm nhận khách hàng')
            ->assertSee('Video cảm nhận');

        $this->assertDatabaseHas('testimonials', [
            'id' => $testimonial->id,
            'rating' => 5,
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.testimonials.update', $testimonial), [
                'name' => 'Ngọc Anh',
                'label' => 'Da nhạy cảm · Hà Nội',
                'content' => 'Cảm nhận đã được cập nhật.',
                'rating' => 4,
                'sort_order' => 8,
                'is_active' => 0,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('testimonials', [
            'id' => $testimonial->id,
            'content' => 'Cảm nhận đã được cập nhật.',
            'rating' => 4,
            'sort_order' => 8,
            'is_active' => false,
        ]);
    }

    public function test_homepage_resolves_uploaded_category_image_paths(): void
    {
        $category = ProductCategory::query()->where('slug', 'tay-trang')->firstOrFail();
        $category->update(['image' => 'uploads/product-categories/category-test.webp', 'is_home' => true]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee(asset('uploads/product-categories/category-test.webp'), false)
            ->assertDontSee('assets/images/categories/uploads/product-categories/category-test.webp', false);
    }

    public function test_product_category_index_uses_active_featured_and_home_toggles(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $category = ProductCategory::query()->where('slug', 'tay-trang')->firstOrFail();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.product-categories.index'));
        $response->assertOk()
            ->assertSee('Kích hoạt')
            ->assertSee('Nổi bật')
            ->assertSee('Trang chủ')
            ->assertSee('product-category-status', false)
            ->assertSee('product-category-featured', false)
            ->assertSee('product-category-home', false)
            ->assertSee('data-model="ProductCategory"', false)
            ->assertSee('data-field="is_active"', false)
            ->assertSee('data-field="is_featured"', false)
            ->assertSee('data-field="is_home"', false);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.common.toggle-field'), [
                'model' => 'ProductCategory',
                'id' => $category->id,
                'field' => 'is_active',
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'value' => false]);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.common.toggle-field'), [
                'model' => 'ProductCategory',
                'id' => $category->id,
                'field' => 'is_featured',
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'value' => true]);

        $homeValue = ! $category->fresh()->is_home;
        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.common.toggle-field'), [
                'model' => 'ProductCategory',
                'id' => $category->id,
                'field' => 'is_home',
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'value' => $homeValue]);

        $this->assertDatabaseHas('product_categories', [
            'id' => $category->id,
            'is_active' => false,
            'is_featured' => true,
            'is_home' => $homeValue,
        ]);
    }

    public function test_product_category_index_filters_by_status(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $active = ProductCategory::query()->create([
            'name' => 'Danh mục đang hiển thị '.Str::uuid(),
            'slug' => 'danh-muc-dang-hien-thi-'.Str::uuid(),
            'sort_order' => 90,
            'is_active' => true,
        ]);
        $inactive = ProductCategory::query()->create([
            'name' => 'Danh mục đang ẩn '.Str::uuid(),
            'slug' => 'danh-muc-dang-an-'.Str::uuid(),
            'sort_order' => 91,
            'is_active' => false,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.product-categories.index', ['status' => 'inactive', 'per_page' => 50]))
            ->assertOk()
            ->assertSee($inactive->name)
            ->assertSee('data-record-id="'.$inactive->id.'"', false)
            ->assertDontSee('data-record-id="'.$active->id.'"', false)
            ->assertSee('data-reorder-enabled="0"', false);
    }

    public function test_product_category_bulk_actions_are_wired_to_selected_rows(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $categories = ProductCategory::query()->whereIn('slug', ['tay-trang', 'sua-rua-mat'])->get();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.product-categories.index'));
        $response->assertOk()
            ->assertSee('value="activate"', false)
            ->assertSee('value="deactivate"', false)
            ->assertSee('value="duplicate"', false)
            ->assertSee('value="delete"', false)
            ->assertSee('data-index-resource="product_category"', false)
            ->assertSee('data-bulk-form-id="admin-bulk-product_category-form"', false)
            ->assertSee('data-reorderable="1"', false);

        $html = $response->getContent();
        foreach ($categories as $category) {
            $this->assertStringContainsString(
                'data-record-id="'.$category->id.'"',
                $html,
            );
            $this->assertStringContainsString(
                'form="admin-bulk-product_category-form"',
                $html,
            );
        }

        $this->actingAs($admin, 'admin')
            ->post(route('admin.common.bulk-action'), [
                'resource' => 'product_category',
                'action' => 'deactivate',
                'ids' => $categories->pluck('id')->all(),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(0, ProductCategory::query()
            ->whereKey($categories->pluck('id'))
            ->where('is_active', true)
            ->count());

        $parentCategory = ProductCategory::query()->where('slug', 'cham-soc-mat')->firstOrFail();
        $this->actingAs($admin, 'admin')
            ->post(route('admin.common.bulk-action'), [
                'resource' => 'product_category',
                'action' => 'delete',
                'ids' => [$parentCategory->id],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('ids');

        $this->assertDatabaseHas('product_categories', ['id' => $parentCategory->id]);
    }

    public function test_product_bulk_checkboxes_are_submitted_with_the_bulk_form(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $product = Product::query()->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee(
                'data-index-resource="product"',
                false,
            )
            ->assertSee(
                'data-bulk-form-id="admin-bulk-product-form"',
                false,
            )
            ->assertSee(
                'data-record-id="'.$product->id.'"',
                false,
            )
            ->assertSee(
                'form="admin-bulk-product-form"',
                false,
            );

        $this->actingAs($admin, 'admin')
            ->post(route('admin.common.bulk-action'), [
                'resource' => 'product',
                'action' => 'deactivate',
                'ids' => [$product->id],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertFalse((bool) $product->fresh()->is_active);
    }

    public function test_product_create_form_renders_clean_variant_assets(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $response = $this->actingAs($admin, 'admin')->get(route('admin.products.create'));

        $response->assertOk()
            ->assertSee('Thông tin bán hàng')
            ->assertSee('Lưu ý về sản phẩm')
            ->assertSee('Sản phẩm có biến thể')
            ->assertDontSee('Cách khách chọn biến thể')
            ->assertSee('name="variant_selection_mode"', false)
            ->assertSee('Hiển thị ở các khối danh mục trang chủ')
            ->assertSee('Lưu &amp; tạo mới', false)
            ->assertSee('value="save_and_create"', false)
            ->assertSee('productVariantManager', false);

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, 'vendor/dropzone/dropzone.min.js'));
        $this->assertStringNotContainsString('fontsource-source-sans-3', $html);
        $this->assertStringContainsString('moneyInput({ initialValue: null, decimals: 0 })', $html);
        $this->assertLessThan(strpos($html, 'alpinejs.min.js'), strpos($html, 'alpinejs-mask.min.js'));
    }

    public function test_product_can_save_and_continue_creating_a_new_product(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $category = ProductCategory::query()->doesntHave('children')->firstOrFail();
        $name = 'Sản phẩm lưu và tạo mới '.Str::uuid();
        $filename = 'product-save-and-create-'.Str::uuid().'.png';
        $temporaryPath = 'uploads/tmp/'.$filename;

        File::ensureDirectoryExists(public_path('uploads/tmp'));
        File::put(public_path($temporaryPath), base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));

        $response = $this->actingAs($admin, 'admin')->post(route('admin.products.store'), [
            'product_category_id' => $category->id,
            'name' => $name,
            'description' => '<p>Thông tin sản phẩm.</p>',
            'product_notes' => '<p>Không dùng khi da đang kích ứng nặng.</p>',
            'status' => 'active',
            'variant_selection_mode' => 'combination',
            'image' => $temporaryPath,
            'has_variants' => 0,
            'variants' => [[
                'name' => 'Mặc định',
                'list_price' => 100000,
                'stock' => 10,
                'is_default' => 1,
                'is_active' => 1,
            ]],
            'submit_action' => 'save_and_create',
        ]);

        $response
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', [
            'name' => $name,
            'product_notes' => '<p>Không dùng khi da đang kích ứng nặng.</p>',
        ]);
    }

    public function test_product_edit_form_renders_existing_product_cleanly(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $product = Product::query()->with(['options.values', 'variants.values', 'media'])->firstOrFail();
        $response = $this->actingAs($admin, 'admin')->get(route('admin.products.edit', $product));

        $response->assertOk()
            ->assertSee('Thông tin bán hàng')
            ->assertSee('Lưu ý về sản phẩm')
            ->assertSee('Xem trên website')
            ->assertSee(route('product.show', $product->slug), false)
            ->assertSee('Phân loại để lọc')
            ->assertSee('không tạo thêm biến thể')
            ->assertSee('Sản phẩm có biến thể')
            ->assertDontSee('Cách khách chọn biến thể')
            ->assertSee('name="variant_selection_mode"', false)
            ->assertSee('productVariantManager', false);

        $response->assertDontSee('Lưu &amp; tạo mới', false)
            ->assertDontSee('name="submit_action" value="save_and_create"', false);

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, 'vendor/dropzone/dropzone.min.js'));
        $this->assertStringNotContainsString('moneyInput({ initialValue: ,', $html);
        $this->assertStringNotContainsString('fontsource-source-sans-3', $html);
    }

    public function test_product_validation_rejects_invalid_sale_price(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $category = ProductCategory::query()->doesntHave('children')->firstOrFail();

        $this->actingAs($admin, 'admin')->post(route('admin.products.store'), [
            'product_category_id' => $category->id,
            'name' => 'Sản phẩm kiểm thử giá',
            'description' => 'Mô tả kiểm thử',
            'status' => 'active',
            'image' => 'uploads/tmp/product-test.jpg',
            'has_variants' => 0,
            'variants' => [[
                'name' => 'Mặc định',
                'list_price' => 100000,
                'sale_price' => 100000,
                'stock' => 0,
                'is_default' => 1,
                'is_active' => 1,
            ]],
        ])->assertSessionHasErrors(['variants.0.sale_price']);
    }

    public function test_product_validation_limits_options_and_rejects_duplicate_combinations(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $category = ProductCategory::query()->doesntHave('children')->firstOrFail();
        $options = ProductOption::query()->with('values')->get();

        while ($options->count() < 4) {
            ProductOption::query()->create([
                'name' => 'Thuộc tính test '.$options->count(),
                'slug' => 'thuoc-tinh-test-'.$options->count(),
                'display_type' => 'select',
                'is_active' => true,
            ]);
            $options = ProductOption::query()->with('values')->get();
        }

        $option = $options->first(fn (ProductOption $item) => $item->values->isNotEmpty());
        $valueId = $option->values->first()->id;
        $payload = [
            'product_category_id' => $category->id,
            'name' => 'Sản phẩm kiểm thử biến thể',
            'description' => 'Mô tả kiểm thử',
            'status' => 'active',
            'image' => 'uploads/tmp/product-test.jpg',
            'has_variants' => 1,
            'option_ids' => $options->take(4)->pluck('id')->all(),
            'variants' => [
                ['name' => 'Biến thể A', 'list_price' => 100000, 'stock' => 0, 'value_ids' => [$valueId], 'is_active' => 1],
                ['name' => 'Biến thể B', 'list_price' => 100000, 'stock' => 0, 'value_ids' => [$valueId], 'is_active' => 1],
            ],
        ];

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.store'), $payload)
            ->assertSessionHasErrors(['option_ids', 'variants.1.value_ids']);
    }

    public function test_flash_sale_editor_searches_products_and_creates_a_variant_specific_sale_price(): void
    {
        $admin = User::role('admin')->firstOrFail();
        [$product, $variant] = $this->flashSaleProductAndVariant();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.flash-sales.create'))
            ->assertOk()
            ->assertSee('Chọn sản phẩm Flash Sale')
            ->assertSee('flash-sale-product-picker', false)
            ->assertSee('flashSaleEditor', false);

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.flash-sales.products', ['q' => $product->name]))
            ->assertOk()
            ->assertJsonPath('data.0.product_id', $product->id)
            ->assertJsonPath('data.0.variants.0.id', $variant->id);

        $product->update(['track_inventory' => true, 'allow_preorder' => false]);
        $variant->update(['stock' => 0]);
        $payload = $this->flashSalePayload($product->id, $variant->id, 'fixed', 10000, 500);
        $this->actingAs($admin, 'admin')
            ->post(route('admin.flash-sales.store'), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $sale = FlashSale::query()->where('name', $payload['name'])->firstOrFail();
        $this->assertDatabaseHas('flash_sale_products', [
            'flash_sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'discount_type' => 'fixed',
            'discount_value' => 10000,
            'quantity' => 500,
            'sale_price' => (float) $variant->price - 10000,
        ]);
    }

    public function test_flash_sale_update_preserves_sold_count_and_requires_at_least_one_item(): void
    {
        $admin = User::role('admin')->firstOrFail();
        [$product, $variant] = $this->flashSaleProductAndVariant();
        $sale = FlashSale::query()->create([
            'name' => 'Flash Sale bảo toàn số đã bán',
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addDays(2),
            'is_active' => true,
        ]);
        $item = $sale->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'sale_price' => (float) $variant->price - 5000,
            'discount_type' => 'fixed',
            'discount_value' => 5000,
            'quantity' => 10,
            'sold' => 4,
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.flash-sales.update', $sale), $this->flashSalePayload($product->id, $variant->id, 'percent', 20, 12))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('flash_sale_products', [
            'id' => $item->id,
            'flash_sale_id' => $sale->id,
            'discount_type' => 'percent',
            'discount_value' => 20,
            'quantity' => 12,
            'sold' => 4,
            'sale_price' => round((float) $variant->price * .8, 2),
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.flash-sales.update', $sale), [
                'name' => 'Không có sản phẩm',
                'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('items');

        $this->assertSame(1, FlashSaleProduct::query()->where('flash_sale_id', $sale->id)->count());
    }

    private function flashSaleProductAndVariant(): array
    {
        $product = Product::query()
            ->where('is_active', true)
            ->with(['variants' => fn ($query) => $query->where('is_active', true)->orderByDesc('is_default')->orderBy('id')])
            ->get()
            ->first(function (Product $product): bool {
                $variant = $product->variants->first();

                return $variant && (float) $variant->price > 10000;
            });

        $this->assertNotNull($product, 'Seeder cần có ít nhất một sản phẩm có giá bán để kiểm thử Flash Sale.');

        return [$product, $product->variants->first()];
    }

    private function flashSalePayload(int $productId, int $variantId, string $discountType, int $discountValue, int $quantity): array
    {
        return [
            'name' => 'Flash Sale kiểm thử '.Str::uuid(),
            'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'is_active' => 1,
            'items' => [[
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'quantity' => $quantity,
            ]],
        ];
    }
}
