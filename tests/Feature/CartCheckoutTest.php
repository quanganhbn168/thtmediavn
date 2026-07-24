<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void { parent::setUp(); $this->seed(); }

    public function test_guest_can_add_product_apply_coupon_and_checkout(): void
    {
        $product = Product::firstOrFail();
        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2])->assertOk()->assertJsonPath('count', 2);
        $this->post(route('cart.coupon'), ['coupon' => 'MTD10'])->assertRedirect();
        $this->get(route('cart'))->assertOk()->assertSee($product->name)->assertSee('MTD10');

        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Nguyễn Văn A', 'customer_phone' => '0901234567', 'customer_email' => 'a@example.com',
            'shipping_province' => 'Hà Nội', 'shipping_district' => 'Cầu Giấy', 'shipping_ward' => 'Dịch Vọng',
            'shipping_address' => 'Số 1 đường mẫu', 'payment_method' => 'cod',
        ]);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('checkout.success', $order->order_code));
        $this->assertCount(1, $order->items);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cart_rejects_quantity_above_stock(): void
    {
        $product = Product::firstOrFail();
        $product->update(['track_inventory' => true]);
        $product->default_variant->update(['stock' => 1]);
        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2])->assertUnprocessable();
    }
}
