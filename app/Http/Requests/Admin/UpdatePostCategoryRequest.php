<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $defaultLang = Language::where('is_default', true)->first()?->code ?? 'vi';
        $allLangs = Language::pluck('code')->toArray();
        $categoryId = $this->route('post_category') ? ($this->route('post_category')->id ?? $this->route('post_category')) : '';

        $rules = [
            'parent_id' => 'nullable|exists:post_categories,id|not_in:' . $categoryId,
            'sort_order' => 'nullable|integer',
            'is_home' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];

        foreach ($allLangs as $code) {
            $rules["name.$code"] = ($code === $defaultLang)
                ? 'required|string|max:255'
                : 'nullable|string|max:255';
            
            $rules["description.$code"] = 'nullable|string';
            $rules["seo_title.$code"] = 'nullable|string|max:255';
            $rules["seo_description.$code"] = 'nullable|string';
        }

        return $rules;
    }

    public function messages(): array
    {
        $defaultLang = Language::where('is_default', true)->first();
        $langName = $defaultLang ? $defaultLang->name : 'mặc định';
        $defaultCode = $defaultLang ? $defaultLang->code : 'vi';

        return [
            "name.{$defaultCode}.required" => "Tên danh mục bài viết tiếng {$langName} không được để trống.",
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
            'parent_id.not_in' => 'Danh mục cha không được trùng với chính danh mục đang chỉnh sửa.',
        ];
    }
}
