<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSeoFiles extends Command
{
    protected $signature = 'seo:files:generate';

    protected $description = 'Generate physical robots.txt and sitemap.xml files for the current APP_URL';

    public function handle(): int
    {
        foreach (['seo:robots:generate', 'seo:sitemap:generate'] as $command) {
            if ($this->call($command) !== self::SUCCESS) {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
