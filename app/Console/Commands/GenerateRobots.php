<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateRobots extends Command
{
    protected $signature = 'seo:robots:generate {--path= : Full output path; defaults to public/robots.txt}';

    protected $description = 'Generate the public robots.txt from the application URL and environment';

    public function handle(): int
    {
        $siteUrl = rtrim((string) config('app.url'), '/');
        $lines = app()->environment('production')
            ? [
                'User-agent: *',
                'Allow: /',
                'Disallow: /admin',
            ]
            : [
                'User-agent: *',
                'Disallow: /',
            ];

        $lines[] = '';
        $lines[] = 'Sitemap: '.$siteUrl.'/sitemap.xml';

        $path = $this->option('path') ?: public_path('robots.txt');
        file_put_contents($path, implode(PHP_EOL, $lines).PHP_EOL);

        $this->components->info('Robots generated: '.$path);

        return self::SUCCESS;
    }
}
