<?php

namespace App\Traits;

use App\Models\Language;

trait HasTranslatableValidation
{
    /**
     * Tự động áp dụng quy tắc xác thực cho các trường đa ngôn ngữ dựa trên danh sách ngôn ngữ hoạt động.
     *
     * @param array $rules Mảng luật validate gốc của Form Request
     * @param array $translatableRules Cấu hình trường đa ngôn ngữ và quy tắc tương ứng: ['field_name' => 'rules']
     * @param bool $nullableForOthers Nếu true, các ngôn ngữ phụ sẽ được chuyển từ 'required' thành 'nullable'
     * @return array
     */
    protected function applyTranslatableRules(array $rules, array $translatableRules, bool $nullableForOthers = true): array
    {
        $activeLangs = Language::getActiveLanguages();
        
        // Dự phòng (fallback) nếu database chưa được tạo hoặc chưa có ngôn ngữ
        if ($activeLangs->isEmpty()) {
            $activeLangs = collect([
                (object)['code' => 'vi', 'is_default' => true],
                (object)['code' => 'en', 'is_default' => false]
            ]);
        }

        $defaultLang = $activeLangs->firstWhere('is_default', true)?->code ?? 'vi';

        foreach ($activeLangs as $lang) {
            $code = $lang->code;
            foreach ($translatableRules as $field => $fieldRules) {
                // Đưa các quy tắc về dạng mảng để dễ biến đổi
                $rulesArray = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

                // Nếu không phải ngôn ngữ mặc định và được cấu hình nullable cho ngôn ngữ phụ
                if ($code !== $defaultLang && $nullableForOthers) {
                    $rulesArray = array_map(function ($rule) {
                        return $rule === 'required' ? 'nullable' : $rule;
                    }, $rulesArray);

                    if (!in_array('nullable', $rulesArray)) {
                        $rulesArray[] = 'nullable';
                    }
                }

                $rules["{$field}.{$code}"] = $rulesArray;
            }
        }

        return $rules;
    }

    /**
     * Tự động tạo nhãn thuộc tính thân thiện (attributes) cho các trường đa ngôn ngữ.
     *
     * @param array $attributes Mảng nhãn gốc
     * @param array $fieldLabels Nhãn của các trường: ['name' => 'Tiêu đề bài viết']
     * @return array
     */
    protected function applyTranslatableAttributes(array $attributes, array $fieldLabels): array
    {
        $activeLangs = Language::getActiveLanguages();
        
        if ($activeLangs->isEmpty()) {
            $activeLangs = collect([
                (object)['code' => 'vi', 'name' => 'Tiếng Việt'],
                (object)['code' => 'en', 'name' => 'English']
            ]);
        }

        foreach ($activeLangs as $lang) {
            foreach ($fieldLabels as $field => $label) {
                $attributes["{$field}.{$lang->code}"] = "{$label} ({$lang->name})";
            }
        }

        return $attributes;
    }
}
