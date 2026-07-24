<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void { parent::setUp(); $this->seed(); }

    public function test_customer_can_register_and_manage_account(): void
    {
        $this->post(route('register.store'), ['name'=>'Khách mới','email'=>'moi@example.com','phone'=>'0911222333','password'=>'password123','password_confirmation'=>'password123'])->assertRedirect(route('account.index'));
        $user=User::where('email','moi@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('customer'));
        $this->get(route('account.profile'))->assertOk()->assertSee('Thông tin cá nhân');
        $this->post(route('account.addresses.store'), ['name'=>'Khách mới','phone'=>'0911222333','province'=>'Hà Nội','address'=>'Số 1','is_default'=>1])->assertRedirect();
        $this->assertDatabaseHas('user_addresses',['user_id'=>$user->id,'is_default'=>true]);
    }

    public function test_customer_can_submit_a_verified_purchase_review(): void
    {
        $user=User::role('customer')->firstOrFail();$product=Product::firstOrFail();
        $order=Order::create(['order_code'=>'DH-TEST','user_id'=>$user->id,'customer_name'=>$user->name,'customer_phone'=>'0901','shipping_province'=>'Hà Nội','shipping_address'=>'Số 1','status'=>'completed','payment_status'=>'paid','payment_method'=>'cod','subtotal_amount'=>100000,'total_amount'=>100000]);
        $item=$order->items()->create(['item_type'=>'product','item_id'=>$product->id,'item_name'=>$product->name,'product_id'=>$product->id,'product_name'=>$product->name,'sku'=>$product->sku,'quantity'=>1,'unit_price'=>100000,'total_price'=>100000]);
        $this->actingAs($user)->post(route('product.reviews.store',$product),['rating'=>5,'content'=>'Sản phẩm dùng rất tốt và đóng gói cẩn thận.'])->assertRedirect();
        $this->assertDatabaseHas('reviews',['product_id'=>$product->id,'order_item_id'=>$item->id,'is_verified'=>true,'status'=>'pending']);
    }

    public function test_customer_cannot_open_another_customers_order(): void
    {
        $customer=User::role('customer')->firstOrFail();$other=User::factory()->create();$other->assignRole('customer');
        $order=Order::create(['order_code'=>'DH-PRIVATE','user_id'=>$other->id,'customer_name'=>$other->name,'customer_phone'=>'0901','shipping_province'=>'Hà Nội','shipping_address'=>'Số 1','status'=>'pending','payment_status'=>'unpaid','payment_method'=>'cod','subtotal_amount'=>0,'total_amount'=>0]);
        $this->actingAs($customer)->get(route('account.orders.show',$order))->assertForbidden();
    }
}
