<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonialRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'label' => ['nullable', 'string', 'max:160'],
            'content' => ['required', 'string', 'max:1600'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'string', 'max:500'],
            'avatar_remove' => ['nullable', 'boolean'],
            'before_image' => ['nullable', 'string', 'max:500'],
            'before_image_remove' => ['nullable', 'boolean'],
            'after_image' => ['nullable', 'string', 'max:500'],
            'after_image_remove' => ['nullable', 'boolean'],
        ];
    }
}
