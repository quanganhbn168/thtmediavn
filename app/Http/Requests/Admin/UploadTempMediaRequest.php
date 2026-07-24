<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadTempMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
            'convert_to_webp' => ['nullable', 'boolean'],
            'width' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'height' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn tệp cần tải lên.',
            'file.file' => 'Tệp tải lên không hợp lệ.',
        ];
    }
}
