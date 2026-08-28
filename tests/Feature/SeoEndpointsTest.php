<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoEndpointsTest extends TestCase
{
    public function test_robots_uses_app_url_and_allows_public_crawling(): void
    {
        config(['app.url' => 'https://thtmedia.example']);

        $this->withServerVariables([
            'HTTP_HOST' => 'request-host.example',
            'HTTPS' => 'off',
        ])->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertContent(implode(PHP_EOL, [
                'User-agent: *',
                'Allow: /',
                '',
                'Sitemap: https://thtmedia.example/sitemap.xml',
                '',
            ]));
    }

    public function test_frontend_master_always_allows_indexing(): void
    {
        $master = file_get_contents(resource_path('views/layouts/master.blade.php'));

        $this->assertIsString($master);
        $this->assertStringContainsString(
            '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">',
            $master,
        );
    }

    public function test_plain_error_layout_also_allows_indexing(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/plain.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringContainsString(
            '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">',
            $layout,
        );
    }
}
