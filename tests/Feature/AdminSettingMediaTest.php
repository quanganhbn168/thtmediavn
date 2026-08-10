<?php

namespace Tests\Feature;

use App\Models\SiteAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_image_is_stored_preserved_and_removed_by_spatie_media_library(): void
    {
        Storage::fake('public_media');
        $this->seed();
        $admin = User::role('admin')->firstOrFail();

        File::ensureDirectoryExists(public_path('uploads/tmp'));
        File::put(public_path('uploads/tmp/site-logo.png'), base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Z8RkAAAAASUVORK5CYII=',
        ));

        $payload = [
            'site_status' => '1',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'site_name' => ['vi' => 'THT MEDIA VN'],
            'site_description' => ['vi' => 'Nền tảng truyền thông'],
            'copyright' => ['vi' => '© THT MEDIA VN'],
            'logo' => 'uploads/tmp/site-logo.png',
        ];

        $this->actingAs($admin, 'admin')
            ->post(route('admin.settings.general.update'), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $assets = SiteAsset::current()->fresh();
        $this->assertTrue($assets->hasMedia('logo'));
        $mediaId = $assets->getFirstMedia('logo')->getKey();

        unset($payload['logo']);
        $this->actingAs($admin, 'admin')
            ->post(route('admin.settings.general.update'), $payload)
            ->assertSessionHasNoErrors();

        $this->assertSame($mediaId, $assets->fresh()->getFirstMedia('logo')->getKey());

        $payload['logo_remove'] = '1';
        $this->actingAs($admin, 'admin')
            ->post(route('admin.settings.general.update'), $payload)
            ->assertSessionHasNoErrors();

        $this->assertFalse($assets->fresh()->hasMedia('logo'));
    }

    public function test_setting_rejects_a_path_outside_the_temporary_upload_directory(): void
    {
        Storage::fake('public_media');
        $this->seed();
        $admin = User::role('admin')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->from(route('admin.settings.general'))
            ->post(route('admin.settings.general.update'), [
                'timezone' => 'Asia/Ho_Chi_Minh',
                'site_name' => ['vi' => 'THT MEDIA VN'],
                'logo' => '../outside.png',
            ])
            ->assertRedirect(route('admin.settings.general'))
            ->assertSessionHasErrors('logo');

        $this->assertFalse(SiteAsset::current()->hasMedia('logo'));
    }
}
