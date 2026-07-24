<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'header_menu_id' => $this->menuRule('header'),
            'mega_menu_id' => $this->menuRule('header'),
            'footer_menu_1_id' => $this->menuRule('footer'),
            'footer_menu_2_id' => $this->menuRule('footer'),
        ];
    }

    public function messages(): array
    {
        return [
            'header_menu_id.exists' => 'Menu điều hướng phải là một menu Header đang hoạt động.',
            'mega_menu_id.exists' => 'Mega menu phải là một menu Header đang hoạt động.',
            'footer_menu_1_id.exists' => 'Menu chân trang 1 phải là một menu Footer đang hoạt động.',
            'footer_menu_2_id.exists' => 'Menu chân trang 2 phải là một menu Footer đang hoạt động.',
        ];
    }

    private function menuRule(string $location): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('menus', 'id')->where(fn ($query) => $query
                ->where('location', $location)
                ->where('is_active', true)),
        ];
    }
}
