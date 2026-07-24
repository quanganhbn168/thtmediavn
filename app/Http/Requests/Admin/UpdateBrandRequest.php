<?php

namespace App\Http\Requests\Admin;

use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brandId = null;
        $brand = $this->route('brand');
        if ($brand instanceof Brand) {
            $brandId = $brand->id;
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('brands')->ignore($brandId)],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:255'],
            'logo_remove' => ['nullable', 'boolean'],
            'website' => ['nullable', 'url', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
