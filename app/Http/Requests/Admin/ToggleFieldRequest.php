<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleFieldRequest extends FormRequest
{
    public const MODELS = [
        'Slider',
        'SliderItem',
        'Menu',
        'MenuItem',
        'Page',
        'Post',
        'PostCategory',
        'Product',
        'ProductCategory',
        'Brand',
        'ProductOption',
        'ProductAttribute',
        'Testimonial',
        'Customer',
        'Subscriber',
    ];

    public const MODEL_FIELDS = [
        'Slider' => ['is_active'],
        'SliderItem' => ['is_active'],
        'Menu' => ['is_active'],
        'MenuItem' => ['is_active'],
        'Page' => ['is_active'],
        'Post' => ['is_active', 'is_featured'],
        'PostCategory' => ['is_active', 'is_home'],
        'Product' => ['is_active', 'is_featured', 'is_home'],
        'ProductCategory' => ['is_active', 'is_featured', 'is_home'],
        'Brand' => ['is_active', 'is_featured'],
        'ProductOption' => ['is_active'],
        'ProductAttribute' => ['is_active', 'show_in_product_menu'],
        'Testimonial' => ['is_active'],
        'Customer' => ['is_active'],
        'Subscriber' => ['is_active'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'model' => ['required', 'string', Rule::in(self::MODELS)],
            'id' => ['required', 'integer', 'min:1'],
            'field' => ['required', 'string', Rule::in(self::MODEL_FIELDS[(string) $this->input('model')] ?? [])],
        ];
    }

    public function messages(): array
    {
        return [
            'model.in' => 'Model không được phép cập nhật.',
            'field.in' => 'Trường dữ liệu không được phép cập nhật.',
        ];
    }
}
