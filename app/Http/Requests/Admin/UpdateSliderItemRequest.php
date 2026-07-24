<?php

namespace App\Http\Requests\Admin;

use App\Traits\HasTranslatableValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSliderItemRequest extends FormRequest
{
    use HasTranslatableValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'button_link_1' => ['nullable', 'string', 'max:2048'],
            'button_link_2' => ['nullable', 'string', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'string', 'max:500'],
            'image_remove' => ['nullable', 'boolean'],
        ];

        return $this->applyTranslatableRules($rules, [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'button_text_1' => ['nullable', 'string', 'max:100'],
            'button_text_2' => ['nullable', 'string', 'max:100'],
        ], false);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sort_order' => $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
