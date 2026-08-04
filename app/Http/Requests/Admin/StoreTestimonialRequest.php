<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'rating' => $this->filled('rating') ? $this->input('rating') : 5,
        ]);
    }

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
            'video' => ['nullable', 'string', 'max:500'],
            'video_remove' => ['nullable', 'boolean'],
        ];
    }
}
