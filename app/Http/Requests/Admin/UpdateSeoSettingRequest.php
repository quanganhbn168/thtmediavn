<?php

namespace App\Http\Requests\Admin;

use App\Traits\HasTranslatableValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoSettingRequest extends FormRequest
{
    use HasTranslatableValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'google_analytics_code' => 'nullable|string',
            'seo_image' => 'nullable|string|max:255',
            'seo_image_remove' => 'nullable|boolean',
        ];

        return $this->applyTranslatableRules($rules, [
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
        ]);
    }

    public function attributes(): array
    {
        return $this->applyTranslatableAttributes([], [
            'seo_title' => 'Tiêu đề SEO',
            'seo_description' => 'Mô tả SEO',
            'seo_keywords' => 'Từ khóa SEO',
        ]);
    }
}
