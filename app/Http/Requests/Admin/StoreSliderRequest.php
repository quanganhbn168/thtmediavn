<?php

namespace App\Http\Requests\Admin;

use App\Enums\SliderType;
use App\Models\Language;
use App\Traits\HasTranslatableValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSliderRequest extends FormRequest
{
    use HasTranslatableValidation;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'key' => ['required', Rule::enum(SliderType::class), 'unique:sliders,key'],
            'is_active' => ['required', 'boolean'],
        ];

        return $this->applyTranslatableRules($rules, [
            'name' => ['required', 'string', 'max:255'],
        ]);
    }

    public function messages(): array
    {
        $defaultLang = Language::where('is_default', true)->first();
        $langName = $defaultLang ? $defaultLang->name : 'mặc định';
        $defaultCode = $defaultLang ? $defaultLang->code : 'vi';

        return [
            "name.{$defaultCode}.required" => "Tên bộ slider tiếng {$langName} không được để trống.",
            'key.required' => 'Vui lòng chọn vị trí hiển thị.',
            'key.enum' => 'Vị trí hiển thị không hợp lệ.',
            'key.unique' => 'Vị trí này đã có bộ trình chiếu, vui lòng chỉnh sửa bản ghi hiện có.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
