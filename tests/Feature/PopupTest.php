<?php

namespace Tests\Feature;

use App\Models\Popup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopupTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_popup_is_rendered_on_the_homepage(): void
    {
        $this->seed();

        $popup = Popup::query()->create([
            'title' => 'Nhận tư vấn cho dự án mới',
            'subtitle' => 'THT Media',
            'content' => '<p>Gửi brief để được tư vấn.</p>',
            'button_text' => 'Trao đổi ngay',
            'button_url' => '/lien-he',
            'display_scope' => 'home',
            'show_once' => true,
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($popup->title)
            ->assertSee('data-popup-id="'.$popup->id.'"', false)
            ->assertSee('data-show-once="1"', false);
    }

    public function test_home_only_popup_is_not_rendered_on_other_pages(): void
    {
        $this->seed();

        Popup::query()->create([
            'title' => 'Chỉ hiện ở trang chủ',
            'display_scope' => 'home',
            'is_active' => true,
        ]);

        $this->get(route('contact'))
            ->assertOk()
            ->assertDontSee('Chỉ hiện ở trang chủ');
    }

    public function test_inactive_and_expired_popups_are_not_rendered(): void
    {
        $this->seed();

        Popup::query()->create([
            'title' => 'Popup đã tắt',
            'is_active' => false,
        ]);
        Popup::query()->create([
            'title' => 'Popup đã hết hạn',
            'is_active' => true,
            'ends_at' => now()->subMinute(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Popup đã tắt')
            ->assertDontSee('Popup đã hết hạn');
    }

    public function test_admin_can_open_the_filament_popup_resource(): void
    {
        $this->seed();
        $admin = User::role('super_admin')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get(route('filament.admin.resources.popups.index'))
            ->assertOk()
            ->assertSee('Popup');

        $this->get(route('filament.admin.resources.popups.create'))
            ->assertOk()
            ->assertSee('Tiêu đề');

        $popup = Popup::query()->create(['title' => 'Popup kiểm tra form']);

        $this->get(route('filament.admin.resources.popups.edit', $popup))
            ->assertOk()
            ->assertSee('Chỉnh sửa Popup')
            ->assertSee('Tiêu đề');
    }
}
