<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductAttribute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductAttributeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $attribute = $this->route('productAttribute') ?? $this->route('product_attribute');
        $attributeId = $attribute instanceof ProductAttribute ? $attribute->id : null;

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('product_attributes', 'slug')->ignore($attributeId)],
            'values_text' => ['nullable', 'string', 'max:8000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'show_in_product_menu' => ['nullable', 'boolean'],
        ];
    }
}
