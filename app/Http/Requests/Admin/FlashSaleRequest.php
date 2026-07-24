<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;

abstract class FlashSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.discount_type' => ['required', 'in:fixed,percent'],
            'items.*.discount_value' => ['required', 'numeric', 'min:0.01'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $items = collect((array) $this->input('items', []));
            if ($items->isEmpty()) {
                return;
            }

            $productIds = $items->pluck('product_id')->map(fn ($id) => (int) $id)->filter()->unique()->values();
            $variantIds = $items->pluck('product_variant_id')->map(fn ($id) => (int) $id)->filter()->unique()->values();
            $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');
            $variants = ProductVariant::query()->whereIn('id', $variantIds)->get()->keyBy('id');
            $seen = [];

            foreach ($items as $index => $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $variantId = (int) ($item['product_variant_id'] ?? 0);
                $product = $products->get($productId);
                $variant = $variants->get($variantId);

                if (! $product?->is_active) {
                    $validator->errors()->add("items.$index.product_id", 'Sản phẩm đã ngừng hoạt động hoặc không còn tồn tại.');
                    continue;
                }

                if (! $variant || (int) $variant->product_id !== $productId || ! $variant->is_active) {
                    $validator->errors()->add("items.$index.product_variant_id", 'Biến thể không thuộc sản phẩm hoặc đã ngừng hoạt động.');
                    continue;
                }

                $key = $productId.'-'.$variantId;
                if (isset($seen[$key])) {
                    $validator->errors()->add("items.$index.product_variant_id", 'Một biến thể chỉ được thêm một lần trong cùng Flash Sale.');
                }
                $seen[$key] = true;

                $discountType = (string) ($item['discount_type'] ?? '');
                $discountValue = (float) ($item['discount_value'] ?? 0);
                $basePrice = (float) $variant->price;

                if ($discountType === 'percent' && $discountValue > 100) {
                    $validator->errors()->add("items.$index.discount_value", 'Phần trăm giảm giá không được lớn hơn 100%.');
                }

                if ($discountType === 'fixed' && $discountValue >= $basePrice) {
                    $validator->errors()->add("items.$index.discount_value", 'Mức giảm phải nhỏ hơn giá bán của biến thể.');
                }

            }
        });
    }
}
