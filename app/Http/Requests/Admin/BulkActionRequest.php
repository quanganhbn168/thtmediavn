<?php

namespace App\Http\Requests\Admin;

use App\Services\BulkActionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $resource = (string) $this->input('resource');
        $table = BulkActionService::tableFor($resource);

        return [
            'resource' => ['required', Rule::in(BulkActionService::resources())],
            'action' => ['required', Rule::in(BulkActionService::actionsFor($resource))],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct', Rule::exists($table, 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'resource.in' => 'Loại dữ liệu không hỗ trợ thao tác hàng loạt.',
            'action.required' => 'Vui lòng chọn thao tác cần thực hiện.',
            'action.in' => 'Thao tác đã chọn không hợp lệ.',
            'ids.required' => 'Vui lòng chọn ít nhất một bản ghi.',
            'ids.min' => 'Vui lòng chọn ít nhất một bản ghi.',
            'ids.*.exists' => 'Có bản ghi không còn tồn tại trên hệ thống.',
        ];
    }
}
