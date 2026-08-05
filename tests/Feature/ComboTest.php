<?php

namespace Tests\Feature;

use App\Models\Combo;
use App\Models\ComboCategory;
use App\Models\ComboItem;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ComboTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_combo_domain_is_separate_from_products(): void
    {
        $this->assertTrue(Schema::hasTable('combos'));
        $this->assertTrue(Schema::hasTable('combo_categories'));
        $this->assertTrue(Schema::hasTable('combo_items'));
        $this->assertFalse(Schema::hasColumn('products', 'product_type'));
        $this->assertDatabaseHas('combo_categories', ['slug' => 'combo-tri-mun']);
        $this->assertDatabaseHas('combo_categories', ['slug' => 'combo-duong-trang']);
        $admin = \App\Models\User::role('admin')->firstOrFail();
        $category = ComboCategory::query()->firstOrFail();
        $this->actingAs($admin, 'admin')->get(route('admin.combo-categories.index'))->assertOk()->assertSee('Danh mục Combo');
        $this->actingAs($admin, 'admin')->postJson(route('admin.common.toggle-field'), ['model' => 'ComboCategory', 'id' => $category->id, 'field' => 'is_active'])->assertOk();
        $category->refresh()->update(['is_active' => true]);
        $this->actingAs($admin, 'admin')->get(route('admin.combos.create'))->assertOk()->assertSee('Thành phần Combo');
        $this->get(route('combos.index'))->assertOk()->assertSee('Chưa có Combo phù hợp');
    }

    public function test_admin_can_create_combo_without_changing_product_data(): void
    {
        $admin = \App\Models\User::role('admin')->firstOrFail();
        $product = $this->singleVariantProduct();
        $category = ComboCategory::query()->where('slug', 'combo-tri-mun')->firstOrFail();

        $this->actingAs($admin, 'admin')->post(route('admin.combos.store'), [
            'combo_category_id' => $category->id,
            'name' => 'Combo kiểm thử riêng '.uniqid(),
            'description' => '<p>Combo kiểm thử.</p>',
            'price' => 199000,
            'compare_price' => 249000,
            'status' => 'active',
            'is_active' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $combo = Combo::query()->where('name', 'like', 'Combo kiểm thử riêng%')->latest('id')->firstOrFail();
        $this->actingAs($admin, 'admin')->post(route('admin.combos.components.store', $combo), [
            'product_id' => $product->id,
            'product_variant_id' => $product->default_variant->id,
            'quantity' => 2,
            'sort_order' => 1,
        ])->assertRedirect(route('admin.combos.components.index', $combo))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('combo_items', ['combo_id' => $combo->id, 'product_id' => $product->id, 'quantity' => 2]);
        $this->actingAs($admin, 'admin')->get(route('admin.combos.components.index', $combo))->assertOk()->assertSee($product->name);
        $component = ComboItem::query()->where('combo_id', $combo->id)->firstOrFail();
        $this->actingAs($admin, 'admin')->get(route('admin.combos.components.edit', [$combo, $component]))->assertOk()->assertSee('Số lượng trong một Combo');
        $this->actingAs($admin, 'admin')->put(route('admin.combos.components.update', [$combo, $component]), [
            'product_id' => $product->id,
            'product_variant_id' => $product->default_variant->id,
            'quantity' => 3,
            'sort_order' => 2,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('combo_items', ['id' => $component->id, 'quantity' => 3, 'sort_order' => 2]);
        $this->actingAs($admin, 'admin')->delete(route('admin.combos.components.destroy', [$combo, $component]))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('combo_items', ['id' => $component->id]);
        $this->assertSame('active', $product->fresh()->status);
    }

    public function test_combo_cart_checkout_reserves_and_releases_component_stock(): void
    {
        $product = $this->singleVariantProduct();
        $product->update(['track_inventory' => true, 'allow_preorder' => false]);
        $product->default_variant->update(['stock' => 5]);
        $combo = Combo::create([
            'combo_category_id' => ComboCategory::query()->firstOrFail()->id,
            'name' => 'Combo tồn kho '.uniqid(),
            'slug' => 'combo-ton-kho-'.uniqid(),
            'description' => '<p>Combo kiểm thử tồn kho.</p>',
            'price' => 299000,
            'status' => 'active',
            'is_active' => true,
        ]);
        $combo->items()->create(['product_id' => $product->id, 'product_variant_id' => $product->default_variant->id, 'quantity' => 2]);

        $this->postJson(route('cart.store'), ['combo_id' => $combo->id, 'quantity' => 2])->assertOk()->assertJsonPath('product_name', $combo->name);
        $this->get(route('checkout'))->assertOk()->assertSee($combo->name);
        $payload = [
            'checkout_token' => session('checkout_token'),
            'customer_name' => 'Khách Combo',
            'customer_phone' => '0901234567',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'shipping_address' => 'Số 1 đường mẫu',
            'payment_method' => 'cod',
        ];
        $this->post(route('checkout.store'), $payload)->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame('combo', $order->items->first()->item_type);
        $this->assertDatabaseHas('order_item_combo_components', ['order_item_id' => $order->items->first()->id, 'quantity' => 4, 'stock_reserved' => 1]);
        $this->assertSame(1, (int) $product->default_variant->fresh()->stock);

        app(OrderInventoryService::class)->release($order);
        $this->assertSame(5, (int) $product->default_variant->fresh()->stock);
    }

    private function singleVariantProduct(): Product
    {
        $product = Product::query()->where('is_active', true)->with('variants')->get()->first(fn (Product $item): bool => $item->variants->where('is_active', true)->count() === 1);
        $this->assertNotNull($product);

        return $product;
    }
}
