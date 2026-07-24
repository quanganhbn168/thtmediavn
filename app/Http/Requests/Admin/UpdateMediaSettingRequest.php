<?php

namespace App\Http\Requests\Admin;

use App\Services\ImageService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaSettingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $extensions = collect(explode(',', (string) $this->input('media_allowed_extensions')))
            ->map(fn (string $extension) => strtolower(ltrim(trim($extension), '.')))
            ->filter()
            ->unique()
            ->implode(',');

        $this->merge(['media_allowed_extensions' => $extensions]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'media_allowed_extensions' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $extensions = explode(',', (string) $value);
                    $invalid = array_diff($extensions, ImageService::SAFE_EXTENSIONS);
                    if ($invalid !== []) {
                        $fail('Định dạng không an toàn hoặc không được hỗ trợ: '.implode(', ', $invalid).'.');
                    }
                },
            ],
            'media_max_size' => 'required|integer|min:1|max:100',
            'media_webp_conversion' => 'nullable|boolean',
            'media_quality' => 'required|integer|min:1|max:100',
            'default_product_banner' => 'nullable|string|max:255',
            'default_promotion_banner' => 'nullable|string|max:255',
            'default_post_banner' => 'nullable|string|max:255',
            'default_product_banner_remove' => 'nullable|boolean',
            'default_promotion_banner_remove' => 'nullable|boolean',
            'default_post_banner_remove' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'media_allowed_extensions.required' => 'Định dạng tệp tin cho phép không được để trống.',
            'media_max_size.required' => 'Dung lượng tải lên tối đa không được để trống.',
            'media_quality.required' => 'Chất lượng nén ảnh WebP không được để trống.',
        ];
    }
}
