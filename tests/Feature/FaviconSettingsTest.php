<?php

namespace Tests\Feature;

use App\Settings\WebsiteSettings;
use App\Support\Branding\FaviconService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;
use App\Services\SettingService;
use Illuminate\Validation\ValidationException;
class FaviconSettingsTest extends TestCase
{
    use DatabaseTransactions;

    private string $originalPublicPath;
    private string $testPublicPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalPublicPath = public_path();
        $this->testPublicPath = storage_path('framework/testing/favicon-'.Str::uuid());
        File::ensureDirectoryExists($this->testPublicPath.'/favicon-assets/.source');
        app()->usePublicPath($this->testPublicPath);
    }

    protected function tearDown(): void
    {
        app()->usePublicPath($this->originalPublicPath);
        $root = realpath(storage_path('framework/testing')).DIRECTORY_SEPARATOR;
        $target = realpath($this->testPublicPath);
        if ($target && str_starts_with($target, $root)) {
            File::deleteDirectory($target);
        }
        parent::tearDown();
    }

    public function test_all_destinations_are_checked_before_writing(): void
    {
        File::put(public_path('favicon-assets/favicon-16x16.png'), 'original');
        try {
            app(FaviconService::class)->assertWritable();
            $this->fail('Expected the invalid .source destination to stop synchronization.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('.source', $exception->getMessage());
            $this->assertSame('original', File::get(public_path('favicon-assets/favicon-16x16.png')));
        }
    }
    public function test_unchanged_favicon_does_not_require_write_permission(): void
    {
        $settings = app(WebsiteSettings::class);
        $data = $settings->toArray();
        $data['site_name'] = ['vi' => 'QA unchanged favicon'];
        app(SettingService::class)->updateWebsite($data, $settings);
        $this->assertSame($data['site_name'], json_decode(DB::table('settings')->where('group', 'website')->where('name', 'site_name')->value('payload'), true));
    }

    public function test_permission_error_does_not_replace_media_or_save_settings(): void
    {
        $settings = app(WebsiteSettings::class);
        $before = DB::table('settings')->orderBy('id')->get()->toJson();
        $mediaBefore = DB::table('media')->orderBy('id')->get()->toJson();
        try {
            app(SettingService::class)->updateWebsite(['favicon' => 'uploads/tmp/new.png'], $settings);
            $this->fail('Expected a favicon validation error.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('data.favicon', $exception->errors());
            $this->assertSame($before, DB::table('settings')->orderBy('id')->get()->toJson());
            $this->assertSame($mediaBefore, DB::table('media')->orderBy('id')->get()->toJson());
        }
    }

}
