<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Slug;
use Illuminate\Http\Request;

class SlugController extends Controller
{
    public function show(string $slug)
    {
        return $this->resolve(request(), 'tin-tuc', $slug);
    }

    public function showByDomain(string $domain, string $slug)
    {
        return $this->resolve(request(), $domain, $slug);
    }

    /**
     * Phân giải Slug đa hình từ CSDL để gọi đúng Controller xử lý.
     */
    public function resolve(Request $request, string $domain, string $slug)
    {
        $slugModel = Slug::where('slug', $slug)
            ->where('locale', app()->getLocale())
            ->first();

        if (! $slugModel) {
            abort(404);
        }

        $sluggable = $slugModel->sluggable;

        if (! $sluggable) {
            abort(404);
        }

        // Phân giải Bài viết / Tin tức
        if ($sluggable instanceof Post) {
            $postDomain = $sluggable->category?->slug ?: 'tin-tuc';
            abort_unless(in_array($domain, ['tin-tuc', $postDomain], true), 404);

            return app(PostController::class)->show($slug);
        }

        // Phân giải Danh mục Bài viết
        if ($sluggable instanceof PostCategory) {
            abort_unless($domain === 'tin-tuc', 404);

            return app(PostController::class)->index($request, $slug);
        }

        if ($sluggable instanceof Page) {
            abort_unless($domain === 'trang', 404);

            return app(PageController::class)->show($slug);
        }

        abort(404);
    }
}
