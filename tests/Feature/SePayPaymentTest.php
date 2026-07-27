<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SePayPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config([
            'commerce.sepay.enabled' => true,
            'commerce.sepay.mode' => 'test',
            'commerce.sepay.bank_name' => 'Vietcombank',
            'commerce.sepay.bank_code' => 'VCB',
            'commerce.sepay.account_name' => 'CONG TY RHEA',
            'commerce.sepay.account_number' => '123456789',
            'commerce.sepay.webhook_secret' => 'sepay-test-secret',
            'commerce.sepay.api_token' => 'test-token',
            'commerce.sepay.api_base_url' => 'https://userapi-sandbox.sepay.vn/v2',
            'commerce.sepay.payment_timeout_minutes' => 20,
        ]);
    }

    public function test_valid_hmac_webhook_marks_order_paid_once_and_records_sold_count(): void
    {
        [$order, $product, $stockBefore, $soldBefore] = $this->createSePayOrder();
        $payload = $this->webhookPayload($order);

        $this->postSignedWebhook($payload)->assertOk()->assertExactJson(['success' => true]);
        $this->postSignedWebhook($payload)->assertOk()->assertExactJson(['success' => true]);

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('pending', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame((float) $order->total_amount, (float) $order->paid_amount);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('payment_transactions', 1);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'method' => 'sepay_qr', 'status' => 'completed', 'is_automatic' => 1]);
        $this->assertDatabaseHas('payment_transactions', ['order_id' => $order->id, 'match_status' => 'matched', 'signature_verified' => 1]);
        $this->assertSame($stockBefore - 1, $product->default_variant->fresh()->stock);
        $this->assertSame($soldBefore + 1, $product->fresh()->sold_count);
    }

    public function test_invalid_hmac_is_rejected_without_persisting_payload(): void
    {
        [$order] = $this->createSePayOrder();
        $payload = $this->webhookPayload($order);
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $this->call('POST', route('api.webhooks.sepay'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_SEPAY_TIMESTAMP' => (string) now()->timestamp,
            'HTTP_X_SEPAY_SIGNATURE' => 'sha256=invalid',
        ], $body)->assertUnauthorized();

        $this->assertDatabaseCount('payment_transactions', 0);
        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }

    public function test_expired_payment_releases_stock_and_a_late_webhook_needs_review(): void
    {
        [$order, $product, $stockBefore, $soldBefore] = $this->createSePayOrder();
        $this->assertSame($stockBefore - 1, $product->default_variant->fresh()->stock);

        $this->travel(21)->minutes();
        $this->artisan('sepay:expire')->assertSuccessful();

        $order->refresh();
        $this->assertSame('payment_expired', $order->status);
        $this->assertNotNull($order->stock_released_at);
        $this->assertSame($stockBefore, $product->default_variant->fresh()->stock);

        $payload = $this->webhookPayload($order, now()->format('Y-m-d H:i:s'));
        $this->postSignedWebhook($payload)->assertOk();

        $this->assertDatabaseHas('payment_transactions', ['order_id' => $order->id, 'match_status' => 'late']);
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame('unpaid', $order->fresh()->payment_status);
        $this->assertSame($soldBefore, $product->fresh()->sold_count);
    }

    public function test_api_v2_reconciliation_recovers_a_missing_webhook(): void
    {
        [$order] = $this->createSePayOrder();
        Http::fake([
            'https://userapi-sandbox.sepay.vn/v2/transactions*' => Http::response([
                'status' => 'success',
                'data' => [[
                    'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                    'transaction_date' => now()->format('Y-m-d H:i:s'),
                    'account_number' => '123456789',
                    'transfer_type' => 'in',
                    'amount_in' => (int) $order->total_amount,
                    'amount_out' => 0,
                    'transaction_content' => $order->payment_code,
                    'reference_number' => 'FT-API-TEST-1',
                    'code' => $order->payment_code,
                    'bank_brand_name' => 'VCB',
                ]],
                'meta' => ['pagination' => ['has_more' => false]],
            ]),
        ]);

        $this->artisan('sepay:reconcile')->assertSuccessful();

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertDatabaseHas('payment_transactions', ['source' => 'api', 'match_status' => 'matched', 'signature_verified' => 0]);
        $this->assertDatabaseHas('payment_sync_states', ['provider' => 'sepay', 'last_status' => 'success']);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    private function createSePayOrder(): array
    {
        $product = Product::query()->where('is_active', true)->with('variants')->get()
            ->first(fn (Product $item): bool => $item->variants->where('is_active', true)->count() === 1);
        $this->assertNotNull($product);
        $product->update(['track_inventory' => true, 'allow_preorder' => false]);
        $product->default_variant->update(['stock' => 10]);
        $stockBefore = $product->default_variant->fresh()->stock;
        $soldBefore = $product->sold_count;

        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'quantity' => 1])->assertOk();
        $this->get(route('checkout'))->assertOk();
        $this->post(route('checkout.store'), [
            'checkout_token' => session('checkout_token'),
            'customer_name' => 'Khách SePay',
            'customer_phone' => '0901234567',
            'customer_email' => 'sepay@example.com',
            'shipping_province' => 'Thành phố Hà Nội',
            'shipping_ward' => 'Phường Ba Đình',
            'shipping_address' => 'Số 1 đường mẫu',
            'payment_method' => 'sepay_qr',
        ])->assertRedirect();

        return [Order::firstOrFail(), $product, $stockBefore, $soldBefore];
    }

    private function webhookPayload(Order $order, ?string $transactionDate = null): array
    {
        return [
            'id' => 92704,
            'gateway' => 'Vietcombank',
            'transactionDate' => $transactionDate ?: now()->format('Y-m-d H:i:s'),
            'accountNumber' => '123456789',
            'subAccount' => '',
            'code' => $order->payment_code,
            'content' => $order->payment_code.' chuyen tien',
            'transferType' => 'in',
            'description' => 'KHACH HANG chuyen tien',
            'transferAmount' => (int) $order->total_amount,
            'accumulated' => 1000000,
            'referenceCode' => 'FT24012345678',
        ];
    }

    private function postSignedWebhook(array $payload)
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $timestamp = (string) now()->timestamp;
        $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, 'sepay-test-secret');

        return $this->call('POST', route('api.webhooks.sepay'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_SEPAY_TIMESTAMP' => $timestamp,
            'HTTP_X_SEPAY_SIGNATURE' => $signature,
        ], $body);
    }
}
