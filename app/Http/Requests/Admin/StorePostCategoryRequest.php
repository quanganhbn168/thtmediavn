<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use App\Models\PostCategory;
use App\Rules\ValidCategoryParent;
use Illuminate\Foundation\Http\FormRequest;

class StorePostCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $defaultLang = Language::where('is_default', true)->first()?->code ?? 'vi';
        $allLangs = Language::pluck('code')->toArray();

        $rules = [
            'parent_id' => ['nullable', 'integer', 'exists:post_categories,id', new ValidCategoryParent(PostCategory::class, null, 'posts', 'bài viết')],
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
        ];
    }
}
