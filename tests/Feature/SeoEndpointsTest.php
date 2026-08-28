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
                'Disallow: /admin',
                '',
                'Sitemap: https://thtmedia.example/sitemap.xml',
                '',
            ]));
    }
}
