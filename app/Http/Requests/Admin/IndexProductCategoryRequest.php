<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 25, 50])],
            'parent_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
