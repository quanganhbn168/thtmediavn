<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'integer', 'exists:product_categories,id'],
            'status' => ['nullable', 'in:active,draft,archived'],
            'per_page' => ['nullable', 'integer', 'in:10,20,25,50'],
        ];
    }
}
