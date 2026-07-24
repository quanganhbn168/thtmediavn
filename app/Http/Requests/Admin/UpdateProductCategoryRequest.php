<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = null;
        $category = $this->route('product_category');
        if ($category instanceof ProductCategory) {
            $categoryId = $category->id;
        }

        return [
            'parent_id' => ['nullable', 'exists:product_categories,id', Rule::notIn([$categoryId])],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('product_categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
            'image_remove' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_home' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
