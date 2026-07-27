<?php

namespace Tests\Feature;

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_subscribe_with_a_zalo_phone_number(): void
    {
        $response = $this->post(route('newsletter.store'), [
            'phone' => '0901 234 567',
        ]);

        $response->assertSessionHas('newsletter_success', 'Đã đăng ký nhận ưu đãi qua Zalo.');
        $this->assertDatabaseHas('subscribers', [
            'phone' => '0901234567',
            'email' => null,
            'is_active' => true,
        ]);
    }

    public function test_an_existing_email_subscription_continues_to_work(): void
    {
        Subscriber::query()->create([
            'email' => 'customer@example.com',
            'is_active' => false,
        ]);

        $this->post(route('newsletter.store'), [
            'email' => 'CUSTOMER@example.com',
        ])->assertSessionHas('newsletter_success', 'Đăng ký nhận tin thành công.');

        $this->assertDatabaseCount('subscribers', 1);
        $this->assertDatabaseHas('subscribers', [
            'email' => 'customer@example.com',
            'is_active' => true,
        ]);
    }
}
