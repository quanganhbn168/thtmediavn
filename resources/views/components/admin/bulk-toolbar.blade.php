@props([
    'formId',
    'resource',
    'deleteWarning' => 'Dữ liệu đã xóa không thể khôi phục.',
    'actions' => [],
])

@php
    $actionLabels = [
        'activate' => 'Kích hoạt',
        'deactivate' => 'Ngừng kích hoạt',
        'duplicate' => 'Nhân bản',
        'delete' => 'Xóa',
    ];

    $toolbarActions = is_array($actions) && $actions !== [] ? $actions : [];
    if ($toolbarActions === [] && $resource) {
        $toolbarActions = [];
        foreach (\App\Services\BulkActionService::actionsFor($resource) as $action) {
            if (array_key_exists($action, $actionLabels)) {
                $toolbarActions[$action] = $actionLabels[$action];
            }
        }
    }

    if ($toolbarActions === []) {
        $toolbarActions = $actionLabels;
    }
@endphp

<form
    id="{{ $formId }}"
    action="{{ route('admin.common.bulk-action') }}"
    method="POST"
    class="admin-bulk-toolbar"
    data-admin-bulk-form
    data-bulk-toolbar
    data-delete-warning="{{ $deleteWarning }}"
    hidden
>
    @csrf
    <input type="hidden" name="resource" value="{{ $resource }}">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <strong><span data-selected-count>0</span> bản ghi đã chọn</strong>
        <span class="text-body-secondary small">trên trang hiện tại</span>
        <select name="action" class="form-select form-select-sm w-auto ms-md-auto" data-bulk-action>
            <option value="">Chọn thao tác</option>
            @foreach($toolbarActions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-primary btn-sm" data-bulk-apply>Áp dụng</button>
    </div>
</form>
