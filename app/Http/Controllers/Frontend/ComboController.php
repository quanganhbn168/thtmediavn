<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use App\Models\ComboCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComboController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'category' => ['nullable', 'string', 'max:180'],
            'q' => ['nullable', 'string', 'max:150'],
            'sort' => ['nullable', 'in:featured,price-asc,price-desc,name-asc,newest'],
        ]);
        $categorySlug = trim((string) ($data['category'] ?? ''));
        $search = trim((string) ($data['q'] ?? ''));
        $category = $categorySlug !== '' ? ComboCategory::query()->where('slug', $categorySlug)->where('is_active', true)->firstOrFail() : null;
        $combos = Combo::query()
            ->visibleOnSite()
            ->with(['category', 'media', 'items.product.variants', 'items.variant'])
            ->when($category, fn ($query) => $query->where('combo_category_id', $category->id))
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when(($data['sort'] ?? 'featured') === 'featured', fn ($query) => $query->orderByDesc('is_featured')->orderBy('sort_order'))
            ->when(($data['sort'] ?? null) === 'price-asc', fn ($query) => $query->orderBy('price'))
            ->when(($data['sort'] ?? null) === 'price-desc', fn ($query) => $query->orderByDesc('price'))
            ->when(($data['sort'] ?? null) === 'name-asc', fn ($query) => $query->orderBy('name'))
            ->when(($data['sort'] ?? null) === 'newest', fn ($query) => $query->latest('published_at'))
            ->paginate(12)
            ->withQueryString();

        return view('frontend.combos.index', [
            'combos' => $combos,
            'categories' => ComboCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'activeCategory' => $category,
            'searchTerm' => $search,
            'sort' => $data['sort'] ?? 'featured',
        ]);
    }

    public function byCategory(Request $request, string $category): View
    {
        $request->merge(['category' => $category]);

        return $this->index($request);
    }

    public function show(string $slug): View
    {
        $combo = Combo::query()
            ->visibleOnSite()
            ->where('slug', $slug)
            ->with(['category', 'media', 'items.product.brand', 'items.product.media', 'items.product.variants', 'items.variant'])
            ->firstOrFail();
        $related = Combo::query()
            ->visibleOnSite()
            ->whereKeyNot($combo->id)
            ->when($combo->combo_category_id, fn ($query) => $query->where('combo_category_id', $combo->combo_category_id))
            ->with(['category', 'media', 'items.product.variants', 'items.variant'])
            ->take(4)
            ->get();

        return view('frontend.combos.detail', compact('combo', 'related'));
    }
}
