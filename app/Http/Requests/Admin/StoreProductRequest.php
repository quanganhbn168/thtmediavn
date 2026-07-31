<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductCategory;
use App\Models\ProductOptionValue;
use App\Rules\LeafCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_category_id' => ['required', 'integer', 'exists:product_categories,id', new LeafCategory(ProductCategory::class, 'Danh mục sản phẩm')],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug'),
                Rule::unique('slugs', 'slug')->where(fn ($query) => $query->where('locale', app()->getLocale())),
            ],
            'summary' => ['nullable', 'string'],
            'description' => ['required', 'string'],
            'ingredients' => ['nullable', 'string'],
            'usage' => ['nullable', 'string'],
            'product_notes' => ['nullable', 'string'],
            'status' => ['required', 'in:active,draft,archived'],
            'variant_selection_mode' => ['required', 'in:combination,options'],
            'track_inventory' => ['nullable', 'boolean'],
            'allow_preorder' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_home' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'image' => ['required', 'string', 'max:4096'],
            'image_remove' => ['nullable', 'boolean'],
            'image_order' => ['nullable', 'json', 'max:8192'],
            'image_removed_ids' => ['nullable', 'json', 'max:4096'],
            'has_variants' => ['required', 'boolean'],
            'option_ids' => ['required_if:has_variants,1', 'nullable', 'array', 'max:3'],
            'option_ids.*' => ['integer', 'distinct', 'exists:product_options,id'],
            'option_value_ids' => ['nullable', 'array'],
            'option_value_ids.*' => ['nullable', 'array'],
            'option_value_ids.*.*' => ['integer', 'distinct', 'exists:product_option_values,id'],
            'attribute_value_ids' => ['nullable', 'array'],
            'attribute_value_ids.*' => ['integer', 'distinct', 'exists:product_attribute_values,id'],
            'variants' => ['required', 'array', 'min:1', 'max:100'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.barcode' => ['nullable', 'string', 'max:100'],
            'variants.*.list_price' => ['required', 'numeric', 'min:1'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0', 'lt:variants.*.list_price'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.compare_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.weight' => ['nullable', 'integer', 'min:0'],
            'variants.*.value_ids' => ['required_if:has_variants,1', 'nullable', 'array', 'max:3'],
            'variants.*.value_ids.*' => ['integer', 'exists:product_option_values,id'],
            'variants.*.is_default' => ['nullable', 'boolean'],
            'variants.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        if ($this->filled('slug')) {
            $data['slug'] = Str::slug((string) $this->input('slug'));
        }

        $data['variants'] = collect((array) $this->input('variants', []))
            ->map(function ($variant): array {
                $variant = (array) $variant;
                $listPrice = $variant['list_price'] ?? null;
                $salePrice = $variant['sale_price'] ?? null;
                $hasSalePrice = $salePrice !== null && $salePrice !== '';
                $variant['price'] = $hasSalePrice ? $salePrice : $listPrice;
                $variant['compare_price'] = $hasSalePrice ? $listPrice : null;

                return $variant;
            })
            ->values()
            ->all();

        $this->merge($data);
    }

    public function after(): array
    {
        return [function ($validator): void {
            if (! $this->boolean('has_variants')) {
                return;
            }

            $selectedOptions = collect((array) $this->input('option_ids'))->map(fn ($id) => (int) $id)->filter()->unique();
            $valueOptions = ProductOptionValue::query()
                ->whereIn('id', collect((array) $this->input('variants'))->flatMap(fn ($row) => $row['value_ids'] ?? [])->unique())
                ->pluck('product_option_id', 'id');
            $signatures = [];

            foreach ((array) $this->input('variants') as $index => $variant) {
                $submittedValueIds = collect($variant['value_ids'] ?? [])->map(fn ($id) => (int) $id)->filter();
                $valueIds = $submittedValueIds->unique()->sort()->values();
                $optionIds = $valueIds->map(fn ($id) => (int) ($valueOptions[$id] ?? 0))->filter()->unique();
                $signature = $valueIds->implode('-');

                if ($submittedValueIds->count() !== $valueIds->count()) {
                    $validator->errors()->add("variants.$index.value_ids", 'Một biến thể không được lặp lại cùng một giá trị.');
                }
                if ($optionIds->count() !== $selectedOptions->count() || $optionIds->diff($selectedOptions)->isNotEmpty()) {
                    $validator->errors()->add("variants.$index.value_ids", 'Biến thể phải có đúng một giá trị thuộc mỗi thuộc tính đã chọn.');
                }
                if ($signature !== '' && in_array($signature, $signatures, true)) {
                    $validator->errors()->add("variants.$index.value_ids", 'Tổ hợp biến thể đang bị trùng.');
                }
                $signatures[] = $signature;
            }
        }];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'Đường dẫn này đã được sử dụng, vui lòng chọn đường dẫn khác.',
            'description.required' => 'Vui lòng nhập mô tả sản phẩm.',
            'variants.*.list_price.required' => 'Vui lòng nhập giá niêm yết.',
            'variants.*.sale_price.lt' => 'Giá giảm phải nhỏ hơn giá niêm yết.',
            'option_ids.max' => 'Mỗi sản phẩm chỉ được chọn tối đa 3 thuộc tính.',
            'option_ids.*.distinct' => 'Mỗi thuộc tính chỉ được chọn một lần.',
            'option_value_ids.*.*.distinct' => 'Mỗi giá trị thuộc tính chỉ được chọn một lần.',
        ];
    }
}
