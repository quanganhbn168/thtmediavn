<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class FrontendController extends Controller
{
    protected function productRelations(): array
    {
        return ['brand', 'category', 'media', 'variants.values.option', 'attributeValues.attribute', 'flashSaleItems.flashSale'];
    }

    protected function presentProduct(?Product $product, ?float $forcedPrice = null, ?ProductVariant $variant = null): ?array
    {
        if (! $product) {
            return null;
        }

        $gift = trim((string) ($product->getAttribute('gift') ?? $product->getAttribute('gift_text') ?? ''));
        $variants = $product->relationLoaded('variants') ? $product->variants : $product->variants()->get();
        $currentVariant = $variant
            ?: $variants->firstWhere(fn ($item) => (bool) $item->is_active && (bool) $item->is_default)
            ?: $variants->firstWhere('is_active', true)
            ?: $variants->first();
        $price = $forcedPrice ?? (float) ($currentVariant?->effective_price ?: 0);
        $oldPrice = (float) ($currentVariant?->compare_price ?: ($product->current_compare_price ?? 0));
        if ($forcedPrice !== null && $oldPrice <= $price) {
            $oldPrice = 0;
        }
        $inStock = ! $product->track_inventory || (int) ($currentVariant?->stock ?: 0) > 0;
        $availability = $inStock
            ? 'in_stock'
            : ($product->allow_preorder ? 'preorder' : 'out_of_stock');

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'brand' => $product->brand?->name ?? 'Không thương hiệu',
            'brand_slug' => $product->brand?->slug,
            'price' => $price,
            'old_price' => $oldPrice > $price ? $oldPrice : null,
            'image' => $product->image_url,
            'category' => $product->category?->slug,
            'badges' => array_values(array_filter([
                $product->is_featured ? 'Bán chạy' : null,
                $availability === 'preorder' ? 'Đặt trước' : null,
            ])),
            'variant_id' => $currentVariant?->id,
            'sold' => min(100, max(10, $product->sold_count)),
            'sold_text' => 'Đã bán '.number_format($product->sold_count).' sản phẩm',
            'gift' => $gift !== '' ? $gift : null,
            'stock' => $inStock,
            'availability' => $availability,
        ];
    }

    protected function categoriesForView(bool $homeOnly = false): Collection
    {
        return ProductCategory::query()
            ->where('is_active', true)
            ->when($homeOnly, fn ($query) => $query->where('is_home', true))
            ->whereHas('products', fn ($query) => $query->where('is_active', true)->visibleOnSite())
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ProductCategory $category) => [
                'title' => $category->name,
                'name' => $category->name,
                'slug' => $category->slug,
                'image' => $this->categoryImageUrl($category->image),
            ]);
    }

    private function categoryImageUrl(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return asset('assets/images/categories/phu-kien.svg');
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = ltrim(str_replace('\\', '/', $path), '/');
        if (Str::startsWith($path, ['assets/', 'uploads/'])) {
            return asset($path);
        }

        return asset('assets/images/categories/'.$path);
    }

    protected function news(int $limit = 20): Collection
    {
        return Post::query()
            ->where('is_active', true)
            ->visibleOnSite()
            ->with(['category.slugs', 'slugs'])
            ->latest('published_at')
            ->take($limit)
            ->get()
            ->map(fn (Post $post) => $this->presentNewsCard($post));
    }

    /** Nội dung trang chủ chỉ lấy từ những danh mục bài viết được bật is_home. */
    protected function homeNews(int $limit = 13): Collection
    {
        return Post::query()
            ->where('is_active', true)
            ->visibleOnSite()
            ->whereHas('category', fn ($query) => $query
                ->where('is_active', true)
                ->where('is_home', true))
            ->with(['category.slugs', 'slugs'])
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->take($limit)
            ->get()
            ->map(fn (Post $post) => $this->presentNewsCard($post));
    }

    private function presentNewsCard(Post $post): array
    {
        return [
            'domain' => $post->category?->slug ?: 'tin-tuc',
            'category' => $post->category?->getTranslation('name', 'vi'),
            'slug' => $post->slug ?: 'bai-viet-'.$post->id,
            'title' => $post->getTranslation('name', 'vi'),
            'date' => ($post->published_at ?: $post->created_at)->format('d.m.Y'),
            'image' => $post->getFirstMediaUrl('post_image') ?: asset('images/no-image.png'),
            'excerpt' => $post->getTranslation('summary', 'vi'),
            'content' => $post->getTranslation('content', 'vi'),
        ];
    }
}
