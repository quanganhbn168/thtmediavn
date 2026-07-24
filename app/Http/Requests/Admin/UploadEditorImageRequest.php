<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadEditorImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['file' => ['required', 'file']];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn ảnh cần tải lên.',
            'file.file' => 'Ảnh tải lên không hợp lệ.',
        ];
    }
}
