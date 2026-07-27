<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_can_add_product_apply_coupon_and_checkout_with_cod(): void
    {
        $product = $this->singleVariantProduct();
        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('product_name', $product->name);
        $this->post(route('cart.coupon'), ['coupon' => 'MTD10'])->assertRedirect();
        $this->get(route('cart'))->assertOk()->assertSee($product->name)->assertSee('MTD10');

        $checkout = $this->get(route('checkout'))
            ->assertOk()
            ->assertSee('Tôi cần xuất hóa đơn')
            ->assertSee('chính sách mua hàng');
        $token = session('checkout_token');
        $this->assertNotEmpty($token);

        $response = $this->post(route('checkout.store'), $this->checkoutPayload($token));

        $order = Order::firstOrFail();
        $response->assertRedirect(route('checkout.success', $order->order_code));
        $this->assertMatchesRegularExpression('/^RHEA-\d{6}-[A-Z0-9]{6}$/', $order->order_code);
        $this->assertCount(1, $order->items);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertDatabaseCount('cart_items', 0);

        $this->get(route('checkout.success', $order->order_code))
            ->assertOk()
            ->assertSee('Đơn hàng đã được tiếp nhận')
            ->assertSee('Thanh toán khi nhận hàng (COD)')
            ->assertSee('0901234567');
    }

    public function test_buy_now_adds_the_selected_product_and_returns_checkout_redirect(): void
    {
        $product = $this->singleVariantProduct();

        $this->postJson(route('cart.store'), [
            'product_id' => $product->id,
            'variant_id' => $product->default_variant->id,
            'quantity' => 1,
            'action' => 'buy_now',
        ])->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('redirect', route('checkout'));

        $this->get(route('checkout'))->assertOk()->assertSee($product->name);
    }

    public function test_product_with_multiple_variants_requires_an_explicit_variant(): void
    {
        $product = $this->singleVariantProduct();
        $variant = $product->variants()->create([
            'name' => 'Dung tích kiểm thử',
            'sku' => 'CHECKOUT-VARIANT-'.$product->id,
            'price' => $product->default_variant->price,
            'stock' => 5,
            'is_default' => false,
            'is_active' => true,
        ]);

        $this->postJson(route('cart.store'), ['product_id' => $product->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('variant_id');
        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'variant_id' => $variant->id])
            ->assertOk()
            ->assertJsonPath('count', 1);
    }

    public function test_sepay_order_opens_a_dedicated_qr_payment_page(): void
    {
        config([
            'commerce.sepay.enabled' => true,
            'commerce.sepay.bank_name' => 'Ngân hàng kiểm thử',
            'commerce.sepay.bank_code' => 'VCB',
            'commerce.sepay.account_name' => 'CONG TY RHEA',
            'commerce.sepay.account_number' => '123456789',
            'commerce.sepay.webhook_secret' => 'test-webhook-secret',
        ]);
        $product = $this->singleVariantProduct();
        $this->postJson(route('cart.store'), ['product_id' => $product->id])->assertOk();
        $this->get(route('checkout'))->assertOk()->assertSee('QR ngân hàng qua SePay');
        $token = session('checkout_token');

        $this->post(route('checkout.store'), $this->checkoutPayload($token, 'sepay_qr'));
        $order = Order::firstOrFail();

        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame('pending_payment', $order->status);
        $this->assertMatchesRegularExpression('/^RHEA[A-Z0-9]{12}$/', $order->payment_code);
        $this->assertSame(64, strlen($order->payment_public_token));
        $this->get(route('checkout.payment', $order->payment_public_token))
            ->assertOk()
            ->assertSee('Quét mã để thanh toán')
            ->assertSee('Đang chờ thanh toán')
            ->assertSee('Ngân hàng kiểm thử')
            ->assertSee('123456789')
            ->assertSee($order->payment_code)
            ->assertSee('data-copy-value', false);
    }

    public function test_invoice_fields_are_persisted_when_requested(): void
    {
        $product = $this->singleVariantProduct();
        $this->postJson(route('cart.store'), ['product_id' => $product->id])->assertOk();
        $this->get(route('checkout'));
        $payload = $this->checkoutPayload(session('checkout_token')) + [
            'requires_invoice' => 1,
            'invoice_company' => 'Công ty kiểm thử',
            'invoice_tax_code' => '0123456789',
        ];

        $this->post(route('checkout.store'), $payload)->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'requires_invoice' => 1,
            'invoice_company' => 'Công ty kiểm thử',
            'invoice_tax_code' => '0123456789',
        ]);
    }

    public function test_checkout_token_prevents_a_repeated_order_submission(): void
    {
        $product = $this->singleVariantProduct();
        $this->postJson(route('cart.store'), ['product_id' => $product->id])->assertOk();
        $this->get(route('checkout'));
        $payload = $this->checkoutPayload(session('checkout_token'));

        $this->post(route('checkout.store'), $payload)->assertRedirect();
        $this->postJson(route('checkout.store'), $payload)->assertUnprocessable();
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_checkout_rechecks_stock_after_product_was_added(): void
    {
        $product = $this->singleVariantProduct();
        $product->update(['track_inventory' => true, 'allow_preorder' => false]);
        $product->default_variant->update(['stock' => 2]);
        $this->postJson(route('cart.store'), ['product_id' => $product->id])->assertOk();
        $this->get(route('checkout'));
        $product->default_variant->update(['stock' => 0]);

        $this->postJson(route('checkout.store'), $this->checkoutPayload(session('checkout_token')))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cart_rejects_quantity_above_stock_and_invalid_coupon(): void
    {
        $product = $this->singleVariantProduct();
        $product->update(['track_inventory' => true]);
        $product->default_variant->update(['stock' => 1]);
        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'quantity' => 2])->assertUnprocessable();

        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'quantity' => 1])->assertOk();
        $this->postJson(route('cart.coupon'), ['coupon' => 'KHONG-HOP-LE'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('coupon');
    }

    public function test_unavailable_cart_item_does_not_show_an_old_line_total(): void
    {
        $product = $this->singleVariantProduct();
        $this->postJson(route('cart.store'), ['product_id' => $product->id])->assertOk();
        $product->delete();

        $response = $this->get(route('cart'))
            ->assertOk()
            ->assertSee('Không còn khả dụng')
            ->assertSee('Xóa khỏi giỏ');

        $this->assertMatchesRegularExpression('/data-cart-line-total>\s*—\s*</', $response->getContent());
    }

    public function test_shipping_summary_uses_the_central_commerce_configuration(): void
    {
        config([
            'commerce.shipping.flat_fee' => 45000,
            'commerce.shipping.free_threshold' => 2000000,
        ]);
        $product = $this->singleVariantProduct();

        $response = $this->postJson(route('cart.store'), ['product_id' => $product->id])->assertOk();
        $subtotal = (float) $response->json('summary.subtotal');

        $response->assertJsonPath('summary.shipping', 45000)
            ->assertJsonPath('summary.freeShippingRemain', (int) max(0, 2000000 - $subtotal))
            ->assertJsonPath('summary.freeShippingPercent', min(100, (int) round($subtotal / 2000000 * 100)));
    }

    private function singleVariantProduct(): Product
    {
        $product = Product::query()
            ->where('is_active', true)
            ->with('variants')
            ->get()
            ->first(fn (Product $item): bool => $item->variants->where('is_active', true)->count() === 1);

        $this->assertNotNull($product, 'Seeder cần ít nhất một sản phẩm có đúng một biến thể hoạt động.');

        return $product;
    }

    private function checkoutPayload(string $token, string $paymentMethod = 'cod'): array
    {
        return [
            'checkout_token' => $token,
            'customer_name' => 'Nguyễn Văn A',
            'customer_phone' => '0901234567',
            'customer_email' => 'a@example.com',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'shipping_address' => 'Số 1 đường mẫu',
            'payment_method' => $paymentMethod,
        ];
    }
}
