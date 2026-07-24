<?php

namespace App\Http\Requests\Admin;

use App\Traits\HasTranslatableValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAboutSettingRequest extends FormRequest
{
    use HasTranslatableValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'about_image' => 'nullable|string|max:255',
            'about_image_remove' => 'nullable|boolean',
        ];

        return $this->applyTranslatableRules($rules, [
            'about_story' => 'nullable|string',
            'about_history' => 'nullable|string',
            'about_mission' => 'nullable|string',
            'about_vision' => 'nullable|string',
            'about_core_values' => 'nullable|string',
        ]);
    }

    public function attributes(): array
    {
        return $this->applyTranslatableAttributes([], [
            'about_story' => 'Câu chuyện của chúng tôi',
            'about_history' => 'Lịch sử hình thành',
            'about_mission' => 'Sứ mệnh',
            'about_vision' => 'Tầm nhìn',
            'about_core_values' => 'Giá trị cốt lõi',
        ]);
    }
}
