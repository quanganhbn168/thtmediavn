<?php

namespace App\Http\Controllers;

use App\Models\Intro;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\View\View;

class IntroController extends Controller
{
    public function __invoke(string $slug): View
    {
        $intro = Intro::query()->published()->where('slug', $slug)->firstOrFail();

        return view('intros.show', [
            'intro' => $intro,
            'content' => RichContentRenderer::make($intro->content ?? '')->toHtml(),
            'seoKeywords' => $intro->keywords,
            'seoTitle' => $intro->meta_title ?: $intro->title,
            'seoDescription' => $intro->meta_description ?: $intro->summary,
            'seoImage' => $intro->image_url,
            'seoUrl' => $intro->url,
        ]);
    }
}
