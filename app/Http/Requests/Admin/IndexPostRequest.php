<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50])],
        ];
    }
}
