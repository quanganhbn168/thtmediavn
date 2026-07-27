<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\Order;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductOption;
use App\Models\Slider;
use App\Models\Subscriber;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\MediaLibrary\HasMedia;

class BulkActionService
{
    public const RESOURCES = [
        'slider' => [
            'model' => Slider::class,
            'table' => 'sliders',
            'label' => 'bộ trình chiếu',
            'actions' => ['activate', 'deactivate', 'duplicate', 'delete'],
        ],
        'post' => [
            'model' => Post::class,
            'table' => 'posts',
            'label' => 'bài viết',
            'actions' => ['activate', 'deactivate', 'duplicate', 'delete'],
        ],
        'post_category' => [
            'model' => PostCategory::class,
            'table' => 'post_categories',
            'label' => 'danh mục bài viết',
            'actions' => ['activate', 'deactivate', 'duplicate', 'delete'],
        ],
        'order' => ['model' => Order::class, 'table' => 'orders', 'label' => 'đơn hàng', 'actions' => ['delete']],
        'product' => ['model' => Product::class, 'table' => 'products', 'label' => 'sản phẩm', 'actions' => ['activate', 'deactivate', 'duplicate', 'delete']],
        'product_category' => ['model' => ProductCategory::class, 'table' => 'product_categories', 'label' => 'danh mục sản phẩm', 'actions' => ['activate', 'deactivate', 'duplicate', 'delete']],
        'brand' => ['model' => Brand::class, 'table' => 'brands', 'label' => 'thương hiệu', 'actions' => ['activate', 'deactivate', 'duplicate', 'delete']],
        'product_option' => ['model' => ProductOption::class, 'table' => 'product_options', 'label' => 'thuộc tính', 'actions' => ['activate', 'deactivate', 'duplicate', 'delete']],
        'product_attribute' => ['model' => ProductAttribute::class, 'table' => 'product_attributes', 'label' => 'thuộc tính lọc', 'actions' => ['activate', 'deactivate', 'delete']],
        'testimonial' => ['model' => Testimonial::class, 'table' => 'testimonials', 'label' => 'cảm nhận khách hàng', 'actions' => ['activate', 'deactivate', 'delete']],
        'coupon' => ['model' => Coupon::class, 'table' => 'coupons', 'label' => 'mã giảm giá', 'actions' => ['activate', 'deactivate', 'delete']],
        'flash_sale' => ['model' => FlashSale::class, 'table' => 'flash_sales', 'label' => 'flash sale', 'actions' => ['delete']],
        'page' => ['model' => Page::class, 'table' => 'pages', 'label' => 'trang', 'actions' => ['activate', 'deactivate', 'duplicate', 'delete']],
        'contact' => ['model' => Contact::class, 'table' => 'contacts', 'label' => 'tin nhắn', 'actions' => ['duplicate', 'delete']],
        'subscriber' => ['model' => Subscriber::class, 'table' => 'subscribers', 'label' => 'người đăng ký', 'actions' => ['activate', 'deactivate', 'duplicate', 'delete']],
    ];

    public function __construct(
        private readonly SliderService $sliderService,
        private readonly PostService $postService,
        private readonly PostCategoryService $postCategoryService,
        private readonly ProductCategoryService $productCategoryService,
        private readonly ProductAttributeService $productAttributeService,
        private readonly CouponService $couponService,
        private readonly PageService $pageService,
        private readonly TestimonialService $testimonialService,
    ) {}

    public function execute(string $resource, string $action, array $ids): string
    {
        $definition = self::RESOURCES[$resource];
        $modelClass = $definition['model'];
        $label = $definition['label'];

        if ($action === 'delete') {
            DB::transaction(function () use ($modelClass, $ids, $resource): void {
                $modelClass::query()
                    ->whereKey($ids)
                    ->get()
                    ->each(fn (Model $model) => $this->delete($resource, $model));
            });

            return "Đã xóa các {$label} được chọn.";
        }

        if ($action === 'duplicate') {
            $modelClass::query()
                ->whereKey($ids)
                ->get()
                ->each(fn (Model $model) => $this->duplicate($resource, $model));

            return "Đã nhân bản các {$label} được chọn.";
        }

        $modelClass::query()->whereKey($ids)->update([
            'is_active' => $action === 'activate',
        ]);

        return $action === 'activate'
            ? "Đã kích hoạt các {$label} được chọn."
            : "Đã ngừng kích hoạt các {$label} được chọn.";
    }

    private function delete(string $resource, Model $model): void
    {
        match ($resource) {
            'slider' => $this->sliderService->delete($model),
            'post' => $this->postService->delete($model),
            'post_category' => $this->postCategoryService->delete($model),
            'product_category' => $this->productCategoryService->delete($model),
            'product_attribute' => $this->productAttributeService->delete($model),
            'coupon' => $this->couponService->delete($model),
            'page' => $this->pageService->delete($model),
            'testimonial' => $this->testimonialService->delete($model),
            default => $model->delete(),
        };
    }

    private function duplicate(string $resource, Model $model): void
    {
        DB::transaction(function () use ($model) {
            $copy = $model->replicate();
            $this->ensureUniqueFieldValues($copy, $model);
            if (Schema::hasColumn($model->getTable(), 'created_by')) {
                $copy->setAttribute('created_by', auth()->id());
            }

            $copy->save();
            $this->duplicateMedia($model, $copy);
        });
    }

    private function ensureUniqueFieldValues(Model $copy, Model $source): void
    {
        foreach ($this->uniqueColumns($source) as $column) {
            if (! array_key_exists($column, $copy->getAttributes())) {
                continue;
            }

            $original = $copy->getAttribute($column);
            if (! is_scalar($original)) {
                continue;
            }

            $copy->setAttribute($column, $this->uniqueValue($source, $column, (string) $original));
        }
    }

    private function uniqueColumns(Model $model): array
    {
        $connection = $model->getConnectionName() ?? DB::connection()->getName();
        $cacheKey = $connection.':'.$model->getTable();
        static $cache = [];

        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        try {
            $rows = DB::connection($connection)->select(
                "SHOW INDEX FROM `{$model->getTable()}` WHERE Non_unique = 0 AND Key_name != 'PRIMARY'"
            );
        } catch (\Throwable) {
            $cache[$cacheKey] = [];

            return [];
        }

        $columns = [];
        foreach ($rows as $row) {
            if (! empty($row->Column_name)) {
                $columns[] = (string) $row->Column_name;
            }
        }

        $cache[$cacheKey] = array_values(array_unique($columns));

        return $cache[$cacheKey];
    }

    private function uniqueValue(Model $source, string $column, string $value): string
    {
        $candidate = trim($value);
        if ($candidate === '') {
            return $candidate;
        }

        $suffixIndex = 0;
        $query = $source->newQueryWithoutScopes();

        while ($query->where($column, $candidate)->exists()) {
            $suffixIndex++;
            $candidate = $this->copyCandidate((string) $value, $suffixIndex, $column);

            $maxLength = $this->columnMaxLength($source, $column);
            if ($maxLength) {
                $candidate = $this->truncateCandidate($candidate, $maxLength, $column);
            }
        }

        return $candidate;
    }

    private function copyCandidate(string $value, int $index, string $column): string
    {
        $base = trim($value);
        if ($column === 'email' && str_contains($base, '@')) {
            [$left, $right] = explode('@', $base, 2);

            return $index <= 1 ? "{$left}-copy@{$right}" : "{$left}-copy-{$index}@{$right}";
        }

        return $index <= 1 ? "{$base}-copy" : "{$base}-copy-{$index}";
    }

    private function truncateCandidate(string $candidate, int $maxLength, string $column): string
    {
        if (mb_strlen($candidate) <= $maxLength) {
            return $candidate;
        }

        if ($column === 'email' && str_contains($candidate, '@')) {
            [$left, $right] = explode('@', $candidate, 2);
            $maxLeft = max(1, $maxLength - mb_strlen($right) - 1);

            return mb_substr($left, 0, $maxLeft).'@'.$right;
        }

        return mb_substr($candidate, 0, $maxLength);
    }

    private function columnMaxLength(Model $model, string $column): ?int
    {
        try {
            $schema = $model->getConnection()->getDatabaseName();
            $row = $model->getConnection()->selectOne(
                'SELECT CHARACTER_MAXIMUM_LENGTH AS max_length
                 FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$schema, $model->getTable(), $column]
            );

            return $row?->max_length ? (int) $row->max_length : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function duplicateMedia(Model $source, Model $target): void
    {
        if (! ($source instanceof HasMedia) || ! ($target instanceof HasMedia)) {
            return;
        }

        foreach ($source->getMedia() as $media) {
            $media->copy($target, $media->collection_name, $media->disk);
        }
    }

    public static function resources(): array
    {
        return array_keys(self::RESOURCES);
    }

    public static function tableFor(string $resource): string
    {
        return self::RESOURCES[$resource]['table'] ?? '';
    }

    public static function actionsFor(string $resource): array
    {
        return self::RESOURCES[$resource]['actions'] ?? [];
    }
}
