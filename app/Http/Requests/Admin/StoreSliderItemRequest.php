<?php

namespace App\Http\Requests\Admin;

use App\Traits\HasTranslatableValidation;
use Illuminate\Foundation\Http\FormRequest;

class StoreSliderItemRequest extends FormRequest
{
    use HasTranslatableValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'slider_id' => ['required', 'integer', 'exists:sliders,id'],
            'button_link_1' => ['nullable', 'string', 'max:2048'],
            'button_link_2' => ['nullable', 'string', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['required', 'boolean'],
            'image' => ['required', 'string', 'max:500'],
            'mobile_image' => ['nullable', 'string', 'max:500'],
        ];

        return $this->applyTranslatableRules($rules, [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'button_text_1' => ['nullable', 'string', 'max:100'],
            'button_text_2' => ['nullable', 'string', 'max:100'],
        ], false);
    }

    public function messages(): array
    {
        return [
            'slider_id.required' => 'Mã bộ slider không hợp lệ.',
            'slider_id.exists' => 'Bộ slider không tồn tại trên hệ thống.',
            'image.required' => 'Vui lòng chọn hình ảnh cho slide.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sort_order' => $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
