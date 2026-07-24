<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductAttributeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('product_attributes', 'slug')],
            'values_text' => ['nullable', 'string', 'max:8000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'show_in_product_menu' => ['nullable', 'boolean'],
        ];
    }
}
