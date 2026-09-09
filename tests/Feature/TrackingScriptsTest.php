<?php

namespace Tests\Feature;

use App\Settings\TrackingSettings;
use App\Support\Tracking\TrackingScripts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class TrackingScriptsTest extends TestCase
{
    use RefreshDatabase;

    private function settings(array $values = []): TrackingSettings
    {
        return TrackingSettings::fake(array_replace(array_fill_keys(TrackingScripts::FIELDS, null), $values), false);
    }

    public function test_empty_settings_emit_no_markup(): void
    {
        $output = (new TrackingScripts($this->settings()))->render();
        $this->assertSame(['head_start' => '', 'head' => '', 'body' => '', 'footer' => ''], $output);
    }

    public function test_platform_code_is_placed_correctly_and_duplicate_page_scripts_are_not_repeated(): void
    {
        $ga = '<script data-test="ga">window.qa = 1;</script>';
        $meta = '<script data-test="meta">window.qm = 1;</script>';
        $fallback = '<noscript><img src="/pixel.gif" alt=""></noscript>';
        $gtmBody = '<noscript><iframe src="/gtm.html"></iframe></noscript>';
        $settings = $this->settings([
            'google_tag_manager_head_code' => '<script data-test="gtm">window.qg = 1;</script>',
            'google_tag_manager_body_code' => $gtmBody,
            'google_analytics_code' => $ga,
            'meta_pixel_code' => $meta.$fallback,
            'tiktok_pixel_code' => '<script data-test="tiktok">window.qt = 1;</script>',
            'head_code' => $meta,
            'body_open_code' => $gtmBody,
            'body_close_code' => '<script data-test="footer">window.qf = 1;</script>',
        ]);
        $output = (new TrackingScripts($settings))->render(['head' => $meta.$fallback, 'footer' => $ga]);
        $this->assertStringContainsString('data-test="gtm"', $output['head_start']);
        $this->assertStringContainsString($ga, $output['head_start']);
        $this->assertStringContainsString($meta, $output['head']);
        $this->assertStringContainsString('data-test="tiktok"', $output['head']);
        $this->assertStringNotContainsString('<noscript>', $output['head']);
        $this->assertStringContainsString($fallback, $output['body']);
        $this->assertStringContainsString($gtmBody, $output['body']);
        $this->assertStringStartsWith($gtmBody, $output['body']);
        $this->assertStringContainsString('data-test="footer"', $output['footer']);
        foreach ([$ga, $meta, $fallback, $gtmBody] as $code) {
            $this->assertSame(1, substr_count(implode('', $output), $code));
        }
    }

    public function test_legacy_ids_are_not_exposed_or_used_to_generate_tracking(): void
    {
        $settings = $this->settings(['google_analytics_id' => 'G-TEST123', 'google_tag_manager_id' => 'GTM-TEST123']);
        $scripts = new TrackingScripts($settings);
        $this->assertArrayNotHasKey('google_analytics_id', $scripts->formData());
        $this->assertArrayNotHasKey('google_tag_manager_id', $scripts->formData());
        $this->assertFalse(property_exists(TrackingSettings::class, 'google_analytics_id'));
        $this->assertFalse(property_exists(TrackingSettings::class, 'google_tag_manager_id'));
        $this->assertSame('', implode('', $scripts->render()));
    }

    public function test_legacy_custom_google_tag_does_not_also_generate_the_same_id(): void
    {
        $legacy = '<script async src="https://www.googletagmanager.com/gtag/js?id=G-LEGACY1"></script><script>gtag("config","G-LEGACY1");</script>';
        $settings = $this->settings(['google_analytics_id' => 'G-LEGACY1', 'head_code' => $legacy]);
        $output = (new TrackingScripts($settings))->render();
        $this->assertSame('', $output['head_start']);
        $this->assertSame(1, substr_count($output['head'], 'gtag/js?id=G-LEGACY1'));
    }

    public function test_public_layouts_render_each_tracking_slot_once(): void
    {
        $this->settings([
            'google_analytics_code' => '<script data-qa="ga4"></script>',
            'meta_pixel_code' => '<script data-qa="meta"></script><noscript data-qa="fallback"><img src="/qa.gif" alt=""></noscript>',
            'body_close_code' => '<script data-qa="footer"></script>',
        ]);
        foreach (['master', 'plain'] as $layout) {
            $html = view('layouts.'.$layout, [
                'hideHeader' => true, 'hideFooter' => true,
                'errors' => new ViewErrorBag,
                'landingAssets' => ['vite' => []],
                'landingTracking' => ['head' => '<script data-qa="meta"></script>'],
            ])->render();
            foreach (['ga4', 'meta', 'fallback', 'footer'] as $marker) {
                $this->assertSame(1, substr_count($html, 'data-qa="'.$marker.'"'), $layout.' '.$marker);
            }
            $head = strpos($html, '</head>');
            $body = strpos($html, '<body');
            $this->assertLessThan($head, strpos($html, 'data-qa="ga4"'));
            $this->assertLessThan($head, strpos($html, 'data-qa="meta"'));
            $this->assertGreaterThan($body, strpos($html, 'data-qa="fallback"'));
            $this->assertGreaterThan($body, strpos($html, 'data-qa="footer"'));
        }
    }
}
