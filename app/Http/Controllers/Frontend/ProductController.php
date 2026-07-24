<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Brand;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductOption;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends FrontendController
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'category' => ['nullable', 'string', 'max:180'],
            'brand' => ['nullable', 'string', 'max:180'],
            'q' => ['nullable', 'string', 'max:150'],
            'sort' => ['nullable', 'in:featured,price-asc,price-desc,name-asc,newest'],
            'price' => ['nullable', 'in:under-100,100-300,300-500,over-500'],
            'stock' => ['nullable', 'in:in-stock,preorder'],
            'option_values' => ['nullable', 'array'],
            'option_values.*' => ['nullable', 'array'],
            'option_values.*.*' => ['nullable', 'integer', 'exists:product_option_values,id'],
            'attribute_values' => ['nullable', 'array'],
            'attribute_values.*' => ['nullable', 'array'],
            'attribute_values.*.*' => ['nullable', 'integer', 'exists:product_attribute_values,id'],
        ]);

        $category = trim((string) ($data['category'] ?? ''));
        $brand = trim((string) ($data['brand'] ?? ''));
        $search = trim((string) ($data['q'] ?? ''));
        $categoryIds = $category ? $this->resolveCategoryScopeIds($category, false) : [];
        $templateCategoryIds = $category ? $this->resolveCategoryScopeIds($category, true) : [];

        $query = Product::query()
            ->where('is_active', true)
            ->visibleOnSite()
            ->with($this->productRelations())
            ->addSelect([
                'min_variant_price' => ProductVariant::query()
                    ->selectRaw('MIN(price)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_active', true),
            ]);

        $facetQuery = Product::query()->where('is_active', true)->visibleOnSite();

        $applyFacetContextFilters = function (Builder $targetQuery) use ($categoryIds, $brand, $search, $data): void {
            if (! empty($categoryIds)) {
                $targetQuery->whereIn('product_category_id', $categoryIds);
            }

            if ($brand) {
                $targetQuery->whereHas('brand', fn (Builder $brandQuery) => $brandQuery->where('slug', $brand));
            }

            if ($search) {
                $targetQuery->where(function (Builder $searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('brand', fn (Builder $brandQuery) => $brandQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('variants', fn (Builder $variantQuery) => $variantQuery->where('sku', 'like', "%{$search}%"));
                });
            }

            match ($data['price'] ?? null) {
                'under-100' => $targetQuery->whereHas('activeVariants', fn (Builder $variantQuery) => $variantQuery->where('price', '<', 100000)),
                '100-300' => $targetQuery->whereHas('activeVariants', fn (Builder $variantQuery) => $variantQuery->whereBetween('price', [100000, 300000])),
                '300-500' => $targetQuery->whereHas('activeVariants', fn (Builder $variantQuery) => $variantQuery->whereBetween('price', [300000, 500000])),
                'over-500' => $targetQuery->whereHas('activeVariants', fn (Builder $variantQuery) => $variantQuery->where('price', '>', 500000)),
                default => null,
            };

            if (($data['stock'] ?? null) === 'in-stock') {
                $targetQuery->whereHas('activeVariants', fn (Builder $variantQuery) => $variantQuery->where('stock', '>', 0));
            }

            if (($data['stock'] ?? null) === 'preorder') {
                $targetQuery->where('allow_preorder', true);
            }
        };

        $applyFacetContextFilters($query);
        $applyFacetContextFilters($facetQuery);

        $selectedOptionValues = $this->extractFilterIds((array) ($data['option_values'] ?? []));
        $selectedAttributeValues = $this->extractFilterIds((array) ($data['attribute_values'] ?? []));

        foreach ($selectedOptionValues as $valueIds) {
            $query->whereHas('variants', function (Builder $variantQuery) use ($valueIds) {
                $variantQuery->whereHas('values', fn (Builder $valueQuery) => $valueQuery->whereIn('product_option_values.id', $valueIds));
            });
        }

        foreach ($selectedAttributeValues as $valueIds) {
            $query->whereHas('attributeValues', fn (Builder $valueQuery) => $valueQuery->whereIn('product_attribute_values.id', $valueIds));
        }

        match ($data['sort'] ?? 'featured') {
            'price-asc' => $query->orderBy('min_variant_price'),
            'price-desc' => $query->orderByDesc('min_variant_price'),
            'name-asc' => $query->orderBy('name'),
            'newest' => $query->latest('published_at'),
            default => $query->orderByDesc('is_featured')->latest('published_at'),
        };

        $products = $query->paginate(16)->withQueryString();
        $products->through(fn (Product $product) => $this->presentProduct($product));

        $facetProductIds = $facetQuery->pluck('id');

        return view('frontend.products.index', [
            'products' => $products,
            'categories' => $this->categoriesForView(),
            'brands' => Brand::query()
                ->where('is_active', true)
                ->whereHas('products', fn (Builder $query) => $query->where('is_active', true)->visibleOnSite())
                ->orderBy('name')
                ->get(['name', 'slug']),
            'optionGroups' => $this->scopeOptionGroups($facetProductIds, $templateCategoryIds),
            'attributeGroups' => $this->scopeAttributeGroups($facetProductIds, $templateCategoryIds),
            'activeCategory' => $category,
            'searchTerm' => $search,
            'activeBrand' => $brand,
            'activePrice' => $data['price'] ?? '',
            'activeStock' => $data['stock'] ?? '',
            'activeOptionValues' => $selectedOptionValues,
            'activeAttributeValues' => $selectedAttributeValues,
            'sort' => $data['sort'] ?? 'featured',
        ]);
    }

    public function detail(string $slug): View|RedirectResponse
    {
        if (ProductCategory::query()->where('slug', $slug)->where('is_active', true)->exists()) {
            return redirect()->route('content.show', ['domain' => 'danh-muc', 'slug' => $slug], 301);
        }

        $model = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->visibleOnSite()
            ->with([...$this->productRelations(), 'options.values', 'reviews' => fn ($q) => $q->where('status', 'approved')->latest()])
            ->firstOrFail();

        $activeFlashItem = $model->flashSaleItems->first(function (object $item) {
            return $item->flashSale?->isRunning();
        });
        $flashPrice = $activeFlashItem ? (float) $activeFlashItem->sale_price : null;

        $related = Product::query()
            ->where('product_category_id', $model->product_category_id)
            ->whereKeyNot($model->id)
            ->where('is_active', true)
            ->visibleOnSite()
            ->with($this->productRelations())
            ->take(4)
            ->get()
            ->map(fn (Product $product) => $this->presentProduct($product));

        $gallery = $model->getMedia('product_images')->map->getUrl();
        if ($gallery->isEmpty()) {
            $gallery = collect([$model->image_url]);
        }

        $variantSelectionData = $model->variants
            ->where('is_active', true)
            ->map(function ($variant) use ($model): array {
                $flashItem = $model->flashSaleItems->first(fn ($item) => $item->flashSale?->isRunning() && (
                    is_null($item->product_variant_id) || (int) $item->product_variant_id === (int) $variant->id
                ));
                $price = (float) ($flashItem?->sale_price ?? $variant->price ?? 0);
                $comparePrice = (float) ($variant->compare_price ?? 0);

                return [
                    'id' => (int) $variant->id,
                    'value_ids' => $variant->values->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all(),
                    'price' => $price,
                    'compare_price' => $comparePrice > $price ? $comparePrice : null,
                    'stock' => (int) $variant->stock,
                ];
            })
            ->values();

        return view('frontend.products.detail', [
            'product' => $this->presentProduct($model, $flashPrice, $activeFlashItem?->variant),
            'productModel' => $model,
            'flashDealUntil' => $activeFlashItem?->flashSale?->ends_at?->toIso8601String(),
            'hasFlashDeal' => (bool) $activeFlashItem,
            'relatedProducts' => $related,
            'gallery' => $gallery,
            'variantSelectionData' => $variantSelectionData,
            'availableCoupons' => Coupon::query()
                ->where('is_active', true)
                ->visibleOnSite()
                ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->orderBy('id')
                ->take(4)
                ->get(),
        ]);
    }

    public function productByCate(Request $request, string $category): View
    {
        if (! trim($category)) {
            return $this->index($request);
        }

        $categoryRequest = clone $request;
        $categoryRequest->merge(['category' => $category]);

        return $this->index($categoryRequest);
    }

    private function extractFilterIds(array $raw): array
    {
        $result = [];

        foreach ($raw as $ids) {
            if (! is_array($ids)) {
                continue;
            }

            $normalized = array_values(
                array_unique(
                    array_filter(
                        array_map('intval', $ids),
                        static fn (int $id): bool => $id > 0
                    )
                )
            );

            if ($normalized) {
                $result[] = $normalized;
            }
        }

        return $result;
    }

    private function resolveCategoryScopeIds(string $slug, bool $includeAncestors = false): array
    {
        $root = ProductCategory::query()
            ->select(['id', 'parent_id'])
            ->where('slug', $slug)
            ->first();

        if (! $root) {
            return [];
        }

        $categoryIds = [(int) $root->id];
        $frontier = $categoryIds;
        $seen = $categoryIds;

        // Include all descendants in tree.
        while ($frontier) {
            $children = ProductCategory::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->all();

            if (! $children) {
                break;
            }

            $children = array_values(array_unique(array_map('intval', $children)));
            $frontier = [];

            foreach ($children as $childId) {
                if (! in_array($childId, $seen, true)) {
                    $categoryIds[] = $childId;
                    $frontier[] = $childId;
                    $seen[] = $childId;
                }
            }
        }

        if (! $includeAncestors) {
            return $categoryIds;
        }

        // Include ancestors so child categories inherit parent filter templates.
        $ancestor = $root;
        while ($ancestor->parent_id) {
            $ancestor = ProductCategory::query()
                ->select(['id', 'parent_id'])
                ->where('id', $ancestor->parent_id)
                ->first();

            if (! $ancestor) {
                break;
            }

            $parentId = (int) $ancestor->id;
            if (! in_array($parentId, $categoryIds, true)) {
                $categoryIds[] = $parentId;
            }
        }

        return $categoryIds;
    }

    private function scopeOptionGroups($facetProductIds, array $categoryIds)
    {
        $hasCategoryScope = ! empty($categoryIds);
        $hasFacetProducts = $facetProductIds->isNotEmpty();

        $base = ProductOption::query()
            ->where('is_active', true)
            ->whereHas('values');

        if ($hasCategoryScope || $hasFacetProducts) {
            $base->where(function (Builder $q) use ($categoryIds, $facetProductIds, $hasCategoryScope, $hasFacetProducts) {
                if ($hasCategoryScope) {
                    $q->whereHas('categories', fn (Builder $categoryQuery) => $categoryQuery->whereIn('product_categories.id', $categoryIds));
                }

                if ($hasFacetProducts) {
                    $q->orWhereHas('products', fn (Builder $productQuery) => $productQuery->whereIn('products.id', $facetProductIds));
                }
            });
        }

        return $base
            ->with(['values' => function ($q) use ($hasFacetProducts, $facetProductIds) {
                if ($hasFacetProducts) {
                    $q->whereHas('variants', fn (Builder $variantQuery) => $variantQuery->whereHas('product', fn (Builder $productQuery) => $productQuery->whereIn('products.id', $facetProductIds)))
                        ->orderBy('sort_order');
                } else {
                    $q->orderBy('sort_order');
                }
            }])
            ->orderBy('sort_order')
            ->get();
    }

    private function scopeAttributeGroups($facetProductIds, array $categoryIds)
    {
        $hasCategoryScope = ! empty($categoryIds);
        $hasFacetProducts = $facetProductIds->isNotEmpty();

        $base = ProductAttribute::query()
            ->where('is_active', true)
            ->whereHas('values');

        if ($hasCategoryScope || $hasFacetProducts) {
            $base->where(function (Builder $q) use ($categoryIds, $facetProductIds, $hasCategoryScope, $hasFacetProducts) {
                if ($hasCategoryScope) {
                    $q->whereHas('categories', fn (Builder $categoryQuery) => $categoryQuery->whereIn('product_categories.id', $categoryIds));
                }

                if ($hasFacetProducts) {
                    $q->orWhereHas('values', fn (Builder $valueQuery) => $valueQuery->whereHas('products', fn (Builder $productQuery) => $productQuery->whereIn('products.id', $facetProductIds)));
                }
            });
        }

        return $base
            ->with(['values' => function ($q) use ($facetProductIds, $hasFacetProducts) {
                if ($hasFacetProducts) {
                    $q->whereHas('products', fn (Builder $productQuery) => $productQuery->whereIn('products.id', $facetProductIds))
                        ->orderBy('sort_order');
                } else {
                    $q->orderBy('sort_order');
                }
            }])
            ->orderBy('name')
            ->get();
    }
}
