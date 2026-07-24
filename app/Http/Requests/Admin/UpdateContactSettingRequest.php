<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'tax_code' => 'nullable|string|max:50',
            'map_embed' => 'nullable|string',
            'working_hours' => 'nullable|string|max:255',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'zalo' => 'nullable|string|max:255',
        ];
    }
}
