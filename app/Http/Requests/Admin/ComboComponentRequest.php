<?php

namespace App\Http\Requests\Admin;

use App\Models\Combo;
use App\Models\ComboItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;

abstract class ComboComponentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'product_variant_id' => $this->filled('product_variant_id') ? (int) $this->input('product_variant_id') : null,
            'quantity' => (int) $this->input('quantity', 1),
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);
    }

    public function after(): array
    {
        return [function ($validator): void {
            $combo = $this->route('combo');
            if (! $combo instanceof Combo) {
                return;
            }

            $productId = (int) $this->input('product_id');
            $variantId = (int) ($this->input('product_variant_id') ?: 0);
            $product = Product::query()
                ->whereKey($productId)
                ->where('is_active', true)
                ->where('status', 'active')
                ->withCount('activeVariants')
                ->first();

            if (! $product) {
                $validator->errors()->add('product_id', 'Sản phẩm thành phần phải đang hoạt động.');

                return;
            }

            if ((int) $product->active_variants_count < 1) {
                $validator->errors()->add('product_id', 'Sản phẩm thành phần chưa có biến thể hoạt động.');
            }

            if ($variantId > 0) {
                $variant = ProductVariant::query()->find($variantId);
                if (! $variant || ! $variant->is_active || (int) $variant->product_id !== $productId) {
                    $validator->errors()->add('product_variant_id', 'Biến thể không thuộc sản phẩm thành phần.');
                }
            } elseif ((int) $product->active_variants_count > 1) {
                $validator->errors()->add('product_variant_id', 'Sản phẩm có nhiều biến thể, vui lòng chọn một biến thể cụ thể.');
            }

            $duplicate = ComboItem::query()
                ->where('combo_id', $combo->id)
                ->where('product_id', $productId)
                ->when($variantId > 0, fn ($query) => $query->where('product_variant_id', $variantId), fn ($query) => $query->whereNull('product_variant_id'))
                ->when($this->route('comboItem') instanceof ComboItem, fn ($query) => $query->whereKeyNot($this->route('comboItem')->id))
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('product_id', 'Sản phẩm/biến thể này đã có trong Combo.');
            }
        }];
    }
}
