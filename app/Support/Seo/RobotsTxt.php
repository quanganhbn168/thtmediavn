<?php

namespace App\Support\Seo;

final class RobotsTxt
{
    public function render(): string
    {
        $baseUrl = rtrim((string) config('app.url'), '/');

        return implode(PHP_EOL, [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            '',
            'Sitemap: '.$baseUrl.'/sitemap.xml',
            '',
        ]);
    }
}
