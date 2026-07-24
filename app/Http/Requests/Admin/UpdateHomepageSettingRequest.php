<?php

namespace App\Http\Requests\Admin;

use App\Traits\HasTranslatableValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomepageSettingRequest extends FormRequest
{
    use HasTranslatableValidation;

    private const SECTIONS = ['categories', 'flash_sale', 'featured_products', 'brands', 'testimonials', 'posts'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // Frontend hiện chỉ triển khai Slider; không lưu lựa chọn chưa có chức năng.
            'homepage_banner_type' => 'required|string|in:slider',
            'homepage_sections' => 'nullable|array',
            'homepage_sections.*' => ['required', 'string', 'distinct', Rule::in(self::SECTIONS)],
        ];

        $translatable = [];
        foreach (self::SECTIONS as $section) {
            $translatable["homepage_section_titles.{$section}"] = 'nullable|string|max:255';
        }

        return $this->applyTranslatableRules($rules, $translatable);
    }
}
