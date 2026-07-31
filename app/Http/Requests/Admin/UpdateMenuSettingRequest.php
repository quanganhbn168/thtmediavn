<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

    public function after(): array
    {
        return [function (Validator $validator): void {
            $headerMenuId = $this->input('header_menu_id');
            $megaMenuId = $this->input('mega_menu_id');
            $footerMenuOneId = $this->input('footer_menu_1_id');
            $footerMenuTwoId = $this->input('footer_menu_2_id');

            if (filled($headerMenuId) && (string) $headerMenuId === (string) $megaMenuId) {
                $validator->errors()->add('mega_menu_id', 'Mega menu cần là một menu riêng, không dùng chung với menu điều hướng chính.');
            }

            if (filled($footerMenuOneId) && (string) $footerMenuOneId === (string) $footerMenuTwoId) {
                $validator->errors()->add('footer_menu_2_id', 'Hai cột Footer cần chọn hai menu khác nhau.');
            }
        }];
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
