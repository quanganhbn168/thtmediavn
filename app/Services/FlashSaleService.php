<?php

namespace App\Services;

use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FlashSaleService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = FlashSale::query()->withCount('items');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        if (($filters['is_active'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['is_active'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->latest()
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    /**
     * Dữ liệu khởi tạo cho trình soạn Flash Sale. Chỉ đưa sản phẩm hợp lệ vào
     * form để một liên kết cũ hoặc đã xoá không thể vô tình được lưu lại.
     */
    public function formContext(FlashSale $sale, ?array $oldItems = null): array
    {
        if ($oldItems !== null) {
            return [
                'sale' => $sale,
                'editorItems' => $this->itemsFromInput($oldItems),
            ];
        }

        $sale->loadMissing([
            'items.product.media',
            'items.product.variants' => fn ($query) => $query
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('id'),
        ]);

        return [
            'sale' => $sale,
            'editorItems' => $sale->items
                ->filter(fn (FlashSaleProduct $item) => $item->product?->is_active)
                ->map(fn (FlashSaleProduct $item) => $this->editorItem($item->product, $item))
                ->filter()
                ->values()
                ->all(),
        ];
    }

    /**
     * Nguồn tìm kiếm riêng cho hộp chọn sản phẩm. Dữ liệu luôn phân trang qua
     * AJAX nên không ảnh hưởng danh sách sản phẩm đã chọn trên form.
     */
    public function productPicker(array $filters): array
    {
        $search = trim((string) ($filters['q'] ?? ''));
        $perPage = min(max((int) ($filters['per_page'] ?? 12), 6), 24);

        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('variants', fn ($query) => $query->where('is_active', true)->where('price', '>', 0))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->with([
                'media',
                'variants' => fn ($query) => $query
                    ->where('is_active', true)
                    ->where('price', '>', 0)
                    ->orderByDesc('is_default')
                    ->orderBy('id'),
            ])
            ->orderBy('name')
            ->paginate($perPage);

        return [
            'data' => $products->getCollection()
                ->map(fn (Product $product) => $this->editorProduct($product))
                ->filter()
                ->values(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ];
    }

    public function create(array $data): FlashSale
    {
        return DB::transaction(function () use ($data): FlashSale {
            $sale = FlashSale::create($this->toPayload($data));
            $this->syncItems($sale, $data['items']);

            return $sale;
        });
    }

    public function update(FlashSale $sale, array $data): void
    {
        DB::transaction(function () use ($sale, $data): void {
            $sale->update($this->toPayload($data));
            $this->syncItems($sale, $data['items']);
        });
    }

    public function delete(FlashSale $sale): void
    {
        $sale->delete();
    }

    private function itemsFromInput(array $items): array
    {
        $items = collect($items)
            ->filter(fn ($item) => is_array($item) && (int) ($item['product_id'] ?? 0) > 0)
            ->values();

        if ($items->isEmpty()) {
            return [];
        }

        $products = $this->productsForEditor($items->pluck('product_id')->map(fn ($id) => (int) $id)->all());

        return $items
            ->map(function (array $item) use ($products) {
                $product = $products->get((int) $item['product_id']);

                return $product ? $this->editorItem($product, null, $item) : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    private function productsForEditor(array $productIds): EloquentCollection
    {
        return Product::query()
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->with([
                'media',
                'variants' => fn ($query) => $query
                    ->where('is_active', true)
                    ->where('price', '>', 0)
                    ->orderByDesc('is_default')
                    ->orderBy('id'),
            ])
            ->get()
            ->keyBy('id');
    }

    private function editorItem(Product $product, ?FlashSaleProduct $existing = null, array $input = []): ?array
    {
        $productData = $this->editorProduct($product);
        if ($productData === null || $productData['variants'] === []) {
            return null;
        }

        $requestedVariantId = (int) ($input['product_variant_id'] ?? $existing?->product_variant_id ?? 0);
        $variant = collect($productData['variants'])->firstWhere('id', $requestedVariantId)
            ?? $productData['variants'][0];

        $discountType = (string) ($input['discount_type'] ?? $existing?->discount_type ?? 'percent');
        $discountType = in_array($discountType, ['fixed', 'percent'], true) ? $discountType : 'percent';
        $discountValue = $input['discount_value'] ?? $existing?->discount_value ?? 10;
        $quantity = $input['quantity'] ?? $existing?->quantity ?? 1;

        return $productData + [
            'key' => (string) ($existing?->id ?: $product->id.'-'.$variant['id'].'-'.uniqid('', true)),
            'product_variant_id' => $variant['id'],
            'base_price' => $variant['price'],
            'discount_type' => $discountType,
            'discount_value' => (float) $discountValue,
            'quantity' => max(1, (int) $quantity),
            'sold' => (int) ($existing?->sold ?? 0),
        ];
    }

    private function editorProduct(Product $product): ?array
    {
        $variants = $product->variants
            ->filter(fn (ProductVariant $variant) => $variant->is_active && (float) $variant->price > 0)
            ->map(fn (ProductVariant $variant) => [
                'id' => (int) $variant->id,
                'name' => $variant->name ?: 'Mặc định',
                'price' => (float) $variant->price,
                'stock' => (int) $variant->stock,
                'is_default' => (bool) $variant->is_default,
            ])
            ->values()
            ->all();

        if ($variants === []) {
            return null;
        }

        return [
            'product_id' => (int) $product->id,
            'product_name' => $product->name,
            'product_image' => $product->image_url,
            'variants' => $variants,
        ];
    }

    private function toPayload(array $data): array
    {
        return [
            'name' => $data['name'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    /**
     * Đồng bộ theo cặp sản phẩm + biến thể để không reset cột sold của item
     * đang tồn tại. Những item bị bỏ khỏi form mới bị gỡ khỏi chương trình.
     */
    private function syncItems(FlashSale $sale, array $items): void
    {
        $existingItems = $sale->items()->get()->keyBy(
            fn (FlashSaleProduct $item) => $this->itemKey((int) $item->product_id, (int) $item->product_variant_id)
        );
        $keptKeys = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $variantId = (int) $item['product_variant_id'];
            $key = $this->itemKey($productId, $variantId);
            $keptKeys[] = $key;

            $variant = ProductVariant::query()
                ->whereKey($variantId)
                ->where('product_id', $productId)
                ->where('is_active', true)
                ->firstOrFail();
            $discountType = (string) $item['discount_type'];
            $discountValue = (float) $item['discount_value'];
            $payload = [
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'sale_price' => $this->calculateSalePrice($discountValue, $discountType, (float) $variant->price),
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'quantity' => (int) $item['quantity'],
            ];

            $existingItem = $existingItems->get($key);
            if ($existingItem) {
                $existingItem->update($payload);
            } else {
                $sale->items()->create($payload);
            }
        }

        $staleIds = $existingItems
            ->reject(fn (FlashSaleProduct $item, string $key) => in_array($key, $keptKeys, true))
            ->pluck('id');

        if ($staleIds->isNotEmpty()) {
            $sale->items()->whereKey($staleIds)->delete();
        }
    }

    private function calculateSalePrice(float $discountValue, string $discountType, float $basePrice): float
    {
        if ($discountType === 'percent') {
            return max(0.0, round($basePrice * (1 - (min(100.0, $discountValue) / 100)), 2));
        }

        return max(0.0, round($basePrice - $discountValue, 2));
    }

    private function itemKey(int $productId, int $variantId): string
    {
        return $productId.'-'.$variantId;
    }
}
