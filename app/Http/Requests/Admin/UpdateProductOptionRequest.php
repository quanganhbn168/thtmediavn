<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $optionId = null;
        $option = $this->route('productOption');
        if ($option instanceof ProductOption) {
            $optionId = $option->id;
        }

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('product_options', 'slug')->ignore($optionId)],
            'display_type' => ['required', 'in:button,color,select'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'values' => ['nullable', 'array'],
            'values.*.id' => ['nullable', 'integer', Rule::exists('product_option_values', 'id')],
            'values.*.value' => ['nullable', 'string', 'max:120'],
            'values.*.color_code' => ['nullable', 'string', 'max:20'],
        ];
    }
}
