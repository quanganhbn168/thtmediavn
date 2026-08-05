<?php

namespace App\Http\Requests\Admin;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class ServiceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['nullable', 'boolean'],
            'is_landing' => ['nullable', 'boolean'],
            'landing_slug' => ['nullable', 'string', 'max:190'],
            'landing_template' => ['nullable', 'string', 'max:120'],
            'notification_emails' => ['nullable', 'string', 'max:1000'],
            'notification_subject' => ['nullable', 'string', 'max:255'],
            'send_customer_confirmation' => ['nullable', 'boolean'],
            'customer_confirmation_subject' => ['nullable', 'string', 'max:255'],
            'customer_confirmation_body' => ['nullable', 'string', 'max:12000'],
            'customer_confirmation_message' => ['nullable', 'string', 'max:3000'],
            'notification_body' => ['nullable', 'string', 'max:12000'],
            'form_schema' => ['nullable', 'string', function (string $attribute, mixed $value, Closure $fail) {
                if ($value === null || trim($value) === '') {
                    return;
                }

                $decoded = json_decode($value, true);
                if (! is_array($decoded)) {
                    $fail('Nội dung cấu hình form không đúng định dạng JSON.');
                    return;
                }

                foreach ($decoded as $index => $field) {
                    if (! is_array($field)) {
                        $fail("Trường thứ ".($index + 1).' phải là object.');
                        return;
                    }
                    if (empty($field['name']) || ! is_string($field['name'])) {
                        $fail("Trường thứ ".($index + 1).' thiếu tên (name).');
                        return;
                    }
                    if (empty($field['label']) || ! is_string($field['label'])) {
                        $fail("Trường '{$field['name']}' thiếu nhãn (label).");
                        return;
                    }
                    if (empty($field['type']) || ! is_string($field['type'])) {
                        $fail("Trường '{$field['name']}' thiếu kiểu hiển thị (type).");
                        return;
                    }
                }
            }],
        ];
    }
}
