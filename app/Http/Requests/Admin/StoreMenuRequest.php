<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name.vi' => 'required|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:100|in:header,footer',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.vi.required' => 'Tên menu tiếng Việt không được để trống.',
            'location.in' => 'Vị trí menu chỉ có thể là Header hoặc Footer.',
        ];
    }
}
