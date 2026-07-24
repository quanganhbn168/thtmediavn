<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }

    /**
     * Get the custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Họ tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.unique' => 'Email này đã được sử dụng bởi người dùng khác.',
            'password.min' => 'Mật khẩu mới phải có tối thiểu 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ];
    }
}
