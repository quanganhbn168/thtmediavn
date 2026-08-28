<?php

namespace App\Console\Commands;

use App\Support\Seo\RobotsTxt;
use Illuminate\Console\Command;

class GenerateRobots extends Command
{
    protected $signature = 'seo:robots:generate {--path= : Full output path; defaults to public/robots.txt}';

    protected $description = 'Generate the public robots.txt from the application URL';

    public function handle(RobotsTxt $robots): int
    {
        $path = $this->option('path') ?: public_path('robots.txt');
        file_put_contents($path, $robots->render());

        $this->components->info('Robots generated: '.$path);

        return self::SUCCESS;
    }
}
