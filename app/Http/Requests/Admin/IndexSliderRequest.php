<?php

namespace App\Http\Requests\Admin;

use App\Enums\SliderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexSliderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'type' => ['nullable', Rule::enum(SliderType::class)],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50])],
        ];
    }
}
