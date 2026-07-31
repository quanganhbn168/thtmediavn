<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use App\Models\PostCategory;
use App\Rules\LeafCategory;
use App\Traits\HasTranslatableValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    use HasTranslatableValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'post_category_id' => ['required', 'integer', 'exists:post_categories,id', new LeafCategory(PostCategory::class, 'Danh mục bài viết')],
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'published_at' => 'nullable|date_format:Y-m-d\TH:i',
            'image' => 'nullable|string|max:255',
            'image_remove' => 'nullable|boolean',
        ];

        return $this->applyTranslatableRules($rules, [
            'name' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string|max:255',
        ]);
    }

    public function attributes(): array
    {
        return $this->applyTranslatableAttributes([], [
            'name' => 'Tiêu đề bài viết',
            'summary' => 'Tóm tắt ngắn',
            'content' => 'Nội dung chi tiết',
            'seo_title' => 'Tiêu đề SEO',
            'seo_description' => 'Mô tả SEO',
            'seo_keywords' => 'Từ khóa SEO',
        ]);
    }

    public function messages(): array
    {
        $defaultLang = Language::where('is_default', true)->first();
        $langName = $defaultLang ? $defaultLang->name : 'mặc định';
        $defaultCode = $defaultLang ? $defaultLang->code : 'vi';

        return [
            'post_category_id.required' => 'Vui lòng chọn danh mục bài viết.',
            'post_category_id.exists' => 'Danh mục bài viết không tồn tại.',
            "name.{$defaultCode}.required" => "Tiêu đề bài viết tiếng {$langName} không được để trống.",
        ];
    }
}
