<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class PageController extends FrontendController
{
    public function show(string $slug): View
    {
        $page = Page::query()->visibleOnSite()
            ->whereHas('slugs', fn (Builder $query) => $query
                ->where('slug', $slug)
                ->where('locale', app()->getLocale()))
            ->firstOrFail();

        return view('frontend.pages.show', compact('page'));
    }
}
