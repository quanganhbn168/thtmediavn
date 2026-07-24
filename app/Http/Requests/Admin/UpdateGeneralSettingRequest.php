<?php

namespace App\Http\Requests\Admin;

use App\Traits\HasTranslatableValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneralSettingRequest extends FormRequest
{
    use HasTranslatableValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'site_status' => 'nullable|boolean',
            'timezone' => ['required', 'string', Rule::in(\DateTimeZone::listIdentifiers())],
            'logo' => 'nullable|string|max:255',
            'logo_footer' => 'nullable|string|max:255',
            'favicon' => 'nullable|string|max:255',
            'logo_remove' => 'nullable|boolean',
            'logo_footer_remove' => 'nullable|boolean',
            'favicon_remove' => 'nullable|boolean',
        ];

        return $this->applyTranslatableRules($rules, [
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'copyright' => 'nullable|string|max:255',
        ], false);
    }

    public function attributes(): array
    {
        return $this->applyTranslatableAttributes([], [
            'site_name' => 'Tiêu đề chính của Website',
            'site_description' => 'Mô tả ngắn Website',
            'copyright' => 'Bản quyền chân trang',
        ]);
    }

    public function messages(): array
    {
        return [
            'site_name.vi.required' => 'Tiêu đề website tiếng Việt không được để trống.',
            'site_name.en.required' => 'Tiêu đề website tiếng Anh không được để trống.',
        ];
    }
}
