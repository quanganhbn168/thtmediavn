<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('product_options', 'slug')],
            'display_type' => ['required', 'in:button,color,select'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'values' => ['nullable', 'array'],
            'values.*.value' => ['nullable', 'string', 'max:120'],
            'values.*.color_code' => ['nullable', 'string', 'max:20'],
        ];
    }
}

