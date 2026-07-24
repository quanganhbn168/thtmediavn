<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Brand;
use App\Models\ProductOption;
use App\Models\ProductAttribute;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;

class ReorderService
{
    private const RESOURCES = [
        'product_category' => ['model' => ProductCategory::class, 'table' => 'product_categories', 'order_column' => 'sort_order'],
        'brand' => ['model' => Brand::class, 'table' => 'brands', 'order_column' => 'sort_order'],
        'product_option' => ['model' => ProductOption::class, 'table' => 'product_options', 'order_column' => 'sort_order'],
        'product_attribute' => ['model' => ProductAttribute::class, 'table' => 'product_attributes', 'order_column' => 'sort_order'],
        'testimonial' => ['model' => Testimonial::class, 'table' => 'testimonials', 'order_column' => 'sort_order'],
        'page' => ['model' => Page::class, 'table' => 'pages', 'order_column' => 'sort_order'],
        'post_category' => ['model' => PostCategory::class, 'table' => 'post_categories', 'order_column' => 'sort_order'],
    ];

    public function execute(string $resource, array $items): void
    {
        $definition = self::RESOURCES[$resource];
        $modelClass = $definition['model'];
        $column = $definition['order_column'];

        DB::transaction(function () use ($modelClass, $column, $items) {
            foreach ($items as $item) {
                $modelClass::query()->whereKey($item['id'])->update([$column => $item['order']]);
            }
        });
    }

    public static function resources(): array
    {
        return array_keys(self::RESOURCES);
    }

    public static function tableFor(string $resource): string
    {
        return self::RESOURCES[$resource]['table'] ?? '';
    }
}
