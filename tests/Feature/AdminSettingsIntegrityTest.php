<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminSettingsIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_reject_values_that_are_not_supported_or_safe(): void
    {
        $this->seed();
        $admin = User::role('admin')->firstOrFail();

        $this->actingAs($admin, 'admin')->post(route('admin.settings.general.update'), [
            'timezone' => 'Not/A_Timezone',
            'site_name' => ['vi' => 'Phương Trần Cosmetics'],
        ])->assertSessionHasErrors('timezone');

        $this->actingAs($admin, 'admin')->post(route('admin.settings.homepage.update'), [
            'homepage_banner_type' => 'video',
            'homepage_sections' => ['categories', 'unknown_section'],
        ])->assertSessionHasErrors(['homepage_banner_type', 'homepage_sections.1']);

        $this->actingAs($admin, 'admin')->post(route('admin.settings.media.update'), [
            'media_allowed_extensions' => 'jpg, php, SVG',
            'media_max_size' => 10,
            'media_quality' => 85,
        ])->assertSessionHasErrors('media_allowed_extensions');
    }

    public function test_general_timezone_is_applied_after_saving(): void
    {
        $this->seed();
        $admin = User::role('admin')->firstOrFail();

        $this->actingAs($admin, 'admin')->post(route('admin.settings.general.update'), [
            'site_status' => '1',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'site_name' => ['vi' => 'Phương Trần Cosmetics'],
            'site_description' => ['vi' => 'Mỹ phẩm chính hãng'],
            'copyright' => ['vi' => '© Phương Trần Cosmetics'],
        ])->assertSessionHasNoErrors();

        $this->assertSame('Asia/Ho_Chi_Minh', config('app.timezone'));
        $this->assertSame('Asia/Ho_Chi_Minh', date_default_timezone_get());
    }

    public function test_image_upload_rejects_disguised_non_image_content(): void
    {
        $this->seed();
        $admin = User::role('admin')->firstOrFail();
        $file = UploadedFile::fake()->createWithContent('not-an-image.jpg', '<?php echo "unsafe";');

        $this->actingAs($admin, 'admin')->postJson(route('admin.media.upload.temp'), ['file' => $file])
            ->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_video_upload_keeps_the_original_video_extension(): void
    {
        $this->seed();
        $admin = User::role('admin')->firstOrFail();
        $path = null;

        try {
            $response = $this->actingAs($admin, 'admin')->postJson(route('admin.media.upload.temp'), [
                'file' => UploadedFile::fake()->create('testimonial.mp4', 100, 'video/mp4'),
            ]);

            $response->assertOk()->assertJson(['success' => true]);
            $path = $response->json('path');
            $this->assertIsString($path);
            $this->assertStringEndsWith('.mp4', $path);
        } finally {
            if (is_string($path)) {
                File::delete(public_path($path));
            }
        }
    }
}
