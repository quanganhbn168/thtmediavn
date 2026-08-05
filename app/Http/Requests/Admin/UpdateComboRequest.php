<?php

namespace App\Http\Requests\Admin;

use App\Models\Combo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateComboRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $combo = $this->route('combo');
        $comboId = $combo instanceof Combo ? $combo->id : null;
        $slugId = $comboId ? $combo->slugs()->where('locale', app()->getLocale())->value('id') : null;

        return [
            'combo_category_id' => ['nullable', 'integer', 'exists:combo_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('combos', 'slug')->ignore($comboId), Rule::unique('slugs', 'slug')->ignore($slugId)->where(fn ($query) => $query->where('locale', app()->getLocale()))],
            'summary' => ['nullable', 'string'],
            'description' => ['required', 'string'],
            'ingredients' => ['nullable', 'string'],
            'usage' => ['nullable', 'string'],
            'product_notes' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'compare_price' => ['nullable', 'numeric', 'gt:price'],
            'status' => ['required', 'in:active,draft,archived'],
            'allow_preorder' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:4096'],
            'image_remove' => ['nullable', 'boolean'],
            'image_order' => ['nullable', 'json', 'max:8192'],
            'image_removed_ids' => ['nullable', 'json', 'max:4096'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? Str::slug((string) $this->input('slug')) : null,
        ]);
    }
}
