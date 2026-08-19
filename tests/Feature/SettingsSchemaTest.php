<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\SiteAsset;
use App\Models\User;
use App\Settings\AboutSettings;
use App\Settings\CompanySettings;
use App\Settings\ContactSettings;
use App\Settings\HomepageSettings;
use App\Settings\SeoSettings;
use App\Settings\TrackingSettings;
use App\Settings\UploadSettings;
use App\Settings\WebsiteSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use ReflectionProperty;
use Tests\TestCase;

class SettingsSchemaTest extends TestCase
{
    use RefreshDatabase;

    private const SETTINGS_CLASSES = [
        CompanySettings::class,
        WebsiteSettings::class,
        ContactSettings::class,
        SeoSettings::class,
        TrackingSettings::class,
        HomepageSettings::class,
        AboutSettings::class,
        UploadSettings::class,
    ];

    public function test_settings_classes_are_registered_explicitly(): void
    {
        $this->assertSame(self::SETTINGS_CLASSES, config('settings.settings'));
        $this->assertSame([], config('settings.auto_discover_settings'));
    }

    public function test_every_declared_setting_has_a_migrated_value(): void
    {
        $expectedProperties = collect(self::SETTINGS_CLASSES)
            ->flatMap(function (string $settingsClass): array {
                $group = $settingsClass::group();

                return collect((new ReflectionClass($settingsClass))->getProperties(ReflectionProperty::IS_PUBLIC))
                    ->reject(fn (ReflectionProperty $property): bool => $property->isStatic())
                    ->map(fn (ReflectionProperty $property): string => $group.'.'.$property->getName())
                    ->all();
            })
            ->sort()
            ->values()
            ->all();

        $actualProperties = DB::table('settings')
            ->get(['group', 'name'])
            ->map(fn (object $setting): string => $setting->group.'.'.$setting->name)
            ->sort()
            ->values()
            ->all();

        $this->assertSame($expectedProperties, $actualProperties);

        foreach (self::SETTINGS_CLASSES as $settingsClass) {
            $expectedNames = collect((new ReflectionClass($settingsClass))->getProperties(ReflectionProperty::IS_PUBLIC))
                ->reject(fn (ReflectionProperty $property): bool => $property->isStatic())
                ->map(fn (ReflectionProperty $property): string => $property->getName())
                ->sort()
                ->values()
                ->all();
            $loadedNames = collect(app($settingsClass)->toArray())->keys()->sort()->values()->all();

            $this->assertSame($expectedNames, $loadedNames, $settingsClass.' chưa nạp đủ giá trị migration.');
        }
    }

    public function test_settings_migrations_are_create_only(): void
    {
        $migrations = collect(File::files(database_path('settings')))
            ->map(fn (\SplFileInfo $file): string => $file->getFilename())
            ->sort()
            ->values();

        $this->assertCount(14, $migrations);
        $this->assertSame([
            '2026_08_10_200000_create_company_settings.php',
            '2026_08_10_200100_create_website_settings.php',
            '2026_08_10_200200_create_contact_settings.php',
            '2026_08_10_200300_create_seo_settings.php',
            '2026_08_10_200400_create_homepage_settings.php',
            '2026_08_10_200500_create_about_settings.php',
            '2026_08_10_200600_create_upload_settings.php',
            '2026_08_18_150000_create_seo_tracking_settings.php',
            '2026_08_18_160000_create_tracking_settings.php',
            '2026_08_18_170000_create_tracking_placement_settings.php',
            '2026_08_18_180000_create_contact_directory_settings.php',
            '2026_08_19_120000_add_homepage_content_settings.php',
            '2026_08_19_130000_create_company_intro_settings.php',
            '2026_08_19_140000_add_company_content_settings.php',
        ], $migrations->all());
    }

    public function test_all_database_migrations_are_non_destructive(): void
    {
        $migrations = collect([
            ...File::files(database_path('migrations')),
            ...File::files(database_path('settings')),
        ]);

        $nonCreateMigrations = $migrations
            ->map(fn (\SplFileInfo $file): string => $file->getFilename())
            ->reject(fn (string $filename): bool => str_contains($filename, '_create_') || str_contains($filename, '_add_'))
            ->values()
            ->all();

        $this->assertSame([], $nonCreateMigrations);
    }

    public function test_multilingual_switch_controls_active_admin_languages(): void
    {
        $this->seed();
        Language::query()->create([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 20,
        ]);

        $website = app(WebsiteSettings::class);
        $website->multilingual_enabled = false;
        $website->save();
        cache()->forget('active_languages');

        $this->assertSame(['vi'], Language::getActiveLanguages()->pluck('code')->all());

        $website->multilingual_enabled = true;
        $website->save();
        cache()->forget('active_languages');

        $this->assertSame(['vi', 'en'], Language::getActiveLanguages()->pluck('code')->all());
    }

    public function test_filament_settings_page_is_available_to_the_admin(): void
    {
        $this->seed();
        Storage::fake('public_media');
        SiteAsset::current()
            ->addMedia(UploadedFile::fake()->image('logo-footer.png'))
            ->toMediaCollection('logo_footer', 'public_media');

        $admin = User::query()->where('email', 'admin@thtmedia.test')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Cài đặt website')
            ->assertSee('Website')
            ->assertSee('Trang chủ')
            ->assertSee('Doanh nghiệp')
            ->assertSee('Giới thiệu')
            ->assertSee('Liên hệ')
            ->assertSee('Các số điện thoại')
            ->assertSee('Chi nhánh')
            ->assertSee('SEO')
            ->assertSee('Tracking')
            ->assertSee('Logo footer')
            ->assertSee('Ảnh chia sẻ mặc định')
            ->assertSee('Mã trong head')
            ->assertSee('Mã ngay sau body mở')
            ->assertSee('Mã trước body đóng')
            ->assertSee('Logo website')
            ->assertSee('Favicon')
            ->assertSee('Ảnh watermark')
            ->assertSee('Mã Google Analytics / tracking')
            ->assertSee('Mã Meta Pixel')
            ->assertSee('Lưu cài đặt')
            ->assertSee('fi-sc-actions')
            ->assertSee('build/assets/theme-');

        $this->assertContains('watermark', SiteAsset::COLLECTIONS);
    }

    public function test_filament_company_content_resource_is_available_to_the_admin(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@thtmedia.test')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get('/admin/company-contents')
            ->assertOk()
            ->assertSee('Nội dung công ty');

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertSee('Nội dung công ty');
    }

    public function test_company_content_form_uses_curator_media_without_content_type(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@thtmedia.test')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->get('/admin/company-contents/create')
            ->assertOk()
            ->assertSee('Ảnh')
            ->assertSee('Banner')
            ->assertSee('Ảnh chia sẻ')
            ->assertSee('Mô tả')
            ->assertSee('Nội dung')
            ->assertSee('Trạng thái')
            ->assertDontSee('SEO keywords')
            ->assertDontSee('Loại nội dung');
    }

    public function test_admin_panel_uses_a_dedicated_guard_and_seeds_super_admin(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@thtmedia.test')->firstOrFail();

        $this->assertSame('admin', Filament::getPanel('admin')->getAuthGuard());
        $this->assertTrue($admin->hasRole('super_admin', 'admin'));
        $this->assertFalse($admin->hasRole('super_admin', 'web'));
    }
}
