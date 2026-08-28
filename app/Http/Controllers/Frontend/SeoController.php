<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\Seo\RobotsTxt;
use App\Support\Seo\SitemapBuilder;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(SitemapBuilder $sitemap): Response
    {
        return response($sitemap->build()->render(), 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(RobotsTxt $robots): Response
    {
        return response($robots->render(), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
