<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreComboRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'combo_category_id' => ['nullable', 'integer', 'exists:combo_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('combos', 'slug'), Rule::unique('slugs', 'slug')->where(fn ($query) => $query->where('locale', app()->getLocale()))],
            'summary' => ['nullable', 'string'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'compare_price' => ['nullable', 'numeric', 'gt:price'],
            'status' => ['required', 'in:active,draft,archived'],
            'allow_preorder' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:4096'],
            'image_remove' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug((string) $this->input('slug')) : null,
            'items' => collect((array) $this->input('items', []))
                ->map(fn ($item): array => (array) $item)
                ->filter(fn (array $item): bool => (int) ($item['product_id'] ?? 0) > 0)
                ->values()
                ->all(),
        ]);
    }

    public function after(): array
    {
        return [function ($validator): void {
            $this->validateItems($validator);
        }];
    }

    private function validateItems($validator): void
    {
        $rows = collect((array) $this->input('items'));
        $productIds = $rows->pluck('product_id')->map(fn ($id) => (int) $id)->filter()->unique();
        $products = Product::query()->whereIn('id', $productIds)->where('is_active', true)->where('status', 'active')->withCount('activeVariants')->get(['id'])->keyBy('id');
        $variantIds = $rows->pluck('product_variant_id')->map(fn ($id) => (int) $id)->filter()->unique();
        $variants = ProductVariant::query()->whereIn('id', $variantIds)->get(['id', 'product_id', 'is_active'])->keyBy('id');
        $signatures = [];

        foreach ($rows as $index => $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            $variantId = (int) ($row['product_variant_id'] ?? 0);
            $product = $products->get($productId);
            if (! $product) {
                $validator->errors()->add("items.$index.product_id", 'Sản phẩm thành phần phải đang hoạt động.');
                continue;
            }
            if ((int) $product->active_variants_count < 1) {
                $validator->errors()->add("items.$index.product_id", 'Sản phẩm thành phần chưa có biến thể hoạt động.');
            }
            if ($variantId > 0 && (! $variants->has($variantId) || ! $variants->get($variantId)->is_active || (int) $variants->get($variantId)->product_id !== $productId)) {
                $validator->errors()->add("items.$index.product_variant_id", 'Biến thể không thuộc sản phẩm thành phần.');
            }
            if ($variantId === 0 && (int) $product->active_variants_count > 1) {
                $validator->errors()->add("items.$index.product_variant_id", 'Sản phẩm có nhiều biến thể, vui lòng chọn một biến thể cụ thể.');
            }
            $signature = $productId.':'.$variantId;
            if (in_array($signature, $signatures, true)) {
                $validator->errors()->add("items.$index.product_id", 'Sản phẩm/biến thể này đang bị lặp trong Combo.');
            }
            $signatures[] = $signature;
        }
    }
}
