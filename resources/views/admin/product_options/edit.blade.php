@extends('layouts.admin')

@php
    $displayType = old('display_type', $option->display_type);
    $values = old('values', $option->values->map(fn ($value) => [
        'id' => $value->id,
        'value' => $value->value,
        'color_code' => $value->color_code,
    ])->all());
    $values = is_array($values) && $values !== [] ? array_values($values) : [['value' => '', 'color_code' => '']];
@endphp

@section('title', 'Sửa thuộc tính sản phẩm')
@section('page-title', 'Sửa thuộc tính sản phẩm')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.product-options.index') }}">Thuộc tính sản phẩm</a></li>
        <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
    </ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.product-options.update', $option) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <x-card type="primary" :outline="true" title="Thông tin thuộc tính" :collapsible="true" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <x-input
                                id="product_option_name"
                                name="name"
                                label="Tên thuộc tính"
                                :value="$option->name"
                                placeholder="Ví dụ: Màu sắc, Dung tích"
                                required
                            />
                        </div>
                        <div class="col-md-5">
                            <x-slug name="slug" label="Mã thuộc tính" :value="$option->slug" source="product_option_name" />
                        </div>
                    </div>
                </x-card>

                <x-card type="secondary" :outline="true" title="Giá trị thuộc tính" :collapsible="true">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <p class="text-muted mb-0">Sắp xếp lại hoặc thêm các giá trị áp dụng cho thuộc tính này.</p>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-add-option-value>
                            <i class="bi bi-plus-lg me-1"></i>Thêm giá trị
                        </button>
                    </div>

                    <div data-option-values data-next-index="{{ count($values) }}" class="vstack gap-2">
                        @foreach($values as $index => $value)
                            @php
                                $rawColor = (string) data_get($value, 'color_code', '');
                                $pickerColor = preg_match('/^#[0-9a-fA-F]{6}$/', $rawColor) ? $rawColor : '#000000';
                            @endphp
                            <div class="border rounded p-3 bg-body-tertiary" data-option-value-row>
                                <input type="hidden" name="values[{{ $index }}][id]" value="{{ data_get($value, 'id') }}">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-7">
                                        <label class="form-label" for="option_value_{{ $index }}">Giá trị</label>
                                        <input
                                            id="option_value_{{ $index }}"
                                            type="text"
                                            name="values[{{ $index }}][value]"
                                            value="{{ data_get($value, 'value') }}"
                                            class="form-control @error('values.'.$index.'.value') is-invalid @enderror"
                                            placeholder="Ví dụ: Hồng đào"
                                        >
                                        @error('values.'.$index.'.value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 option-value-color {{ $displayType === 'color' ? '' : 'd-none' }}" data-option-value-color>
                                        <label class="form-label">Mã màu</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" value="{{ $pickerColor }}" data-color-picker aria-label="Chọn màu">
                                            <input
                                                type="text"
                                                name="values[{{ $index }}][color_code]"
                                                value="{{ $rawColor }}"
                                                class="form-control @error('values.'.$index.'.color_code') is-invalid @enderror"
                                                placeholder="#RRGGBB"
                                                data-color-code
                                                @disabled($displayType !== 'color')
                                            >
                                        </div>
                                        @error('values.'.$index.'.color_code')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-1 d-grid">
                                        <button type="button" class="btn btn-outline-danger" data-remove-option-value title="Bỏ giá trị này" aria-label="Bỏ giá trị này">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <template data-option-value-template>
                        <div class="border rounded p-3 bg-body-tertiary" data-option-value-row>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-7">
                                    <label class="form-label">Giá trị</label>
                                    <input type="text" name="values[__INDEX__][value]" class="form-control" placeholder="Ví dụ: Hồng đào">
                                </div>
                                <div class="col-md-4 option-value-color {{ $displayType === 'color' ? '' : 'd-none' }}" data-option-value-color>
                                    <label class="form-label">Mã màu</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" value="#000000" data-color-picker aria-label="Chọn màu">
                                        <input type="text" name="values[__INDEX__][color_code]" class="form-control" placeholder="#RRGGBB" data-color-code @disabled($displayType !== 'color')>
                                    </div>
                                </div>
                                <div class="col-md-1 d-grid">
                                    <button type="button" class="btn btn-outline-danger" data-remove-option-value title="Bỏ giá trị này" aria-label="Bỏ giá trị này"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <p class="text-muted small mb-0 mt-3">Giá trị đã dùng trong biến thể sẽ được hệ thống giữ lại để tránh làm hỏng dữ liệu sản phẩm.</p>
                </x-card>
            </div>

            <div class="col-lg-4">
                <x-card type="info" :outline="true" title="Cách hiển thị" :collapsible="true" class="mb-4">
                    <label class="form-label fw-semibold" for="product_option_display_type">Kiểu lựa chọn</label>
                    <select id="product_option_display_type" name="display_type" class="form-select @error('display_type') is-invalid @enderror" data-display-type>
                        <option value="button" @selected($displayType === 'button')>Nút chọn</option>
                        <option value="color" @selected($displayType === 'color')>Màu sắc</option>
                        <option value="select" @selected($displayType === 'select')>Danh sách chọn</option>
                    </select>
                    @error('display_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text mb-3">Chọn “Màu sắc” để nhập thêm mã màu cho từng giá trị.</div>

                    <x-input name="sort_order" type="number" label="Thứ tự hiển thị" :value="old('sort_order', $option->sort_order)" min="0" step="1" />

                    <div class="border-top pt-3">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_active" id="product_option_is_active" value="1" @checked((bool) old('is_active', $option->is_active))>
                            <label class="form-check-label cursor-pointer fw-semibold" for="product_option_is_active">Kích hoạt thuộc tính</label>
                            <div class="form-text">Thuộc tính được kích hoạt mới dùng được khi tạo sản phẩm.</div>
                        </div>
                    </div>
                </x-card>

                <x-card type="secondary" :outline="true" title="Tóm tắt" :collapsible="true">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Giá trị hiện có</span>
                        <span class="badge text-bg-light">{{ number_format($option->values->count()) }}</span>
                    </div>
                </x-card>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 mb-5">
            <a class="btn btn-default" href="{{ route('admin.product-options.index') }}"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
            <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Lưu thay đổi</button>
        </div>
    </form>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.querySelector('[data-option-values]');
        const template = document.querySelector('[data-option-value-template]');
        const addButton = document.querySelector('[data-add-option-value]');
        const displayType = document.querySelector('[data-display-type]');
        if (!container || !template || !addButton || !displayType) return;

        function syncColorInput(row) {
            const picker = row.querySelector('[data-color-picker]');
            const code = row.querySelector('[data-color-code]');
            if (!picker || !code) return;
            picker.addEventListener('input', () => { code.value = picker.value.toUpperCase(); });
            code.addEventListener('input', () => {
                if (/^#[0-9a-f]{6}$/i.test(code.value)) picker.value = code.value;
            });
        }

        function syncColorFields() {
            const isColor = displayType.value === 'color';
            container.querySelectorAll('[data-option-value-color]').forEach((field) => {
                field.classList.toggle('d-none', !isColor);
                field.querySelectorAll('input[name$="[color_code]"]').forEach((input) => { input.disabled = !isColor; });
            });
        }

        function addValue() {
            const index = Number(container.dataset.nextIndex || 0);
            const fragment = template.content.cloneNode(true);
            fragment.querySelectorAll('[name]').forEach((input) => { input.name = input.name.replace('__INDEX__', index); });
            const row = fragment.querySelector('[data-option-value-row]');
            container.appendChild(fragment);
            container.dataset.nextIndex = String(index + 1);
            syncColorInput(row);
            syncColorFields();
            row.querySelector('input[type="text"]')?.focus();
        }

        container.querySelectorAll('[data-option-value-row]').forEach(syncColorInput);
        addButton.addEventListener('click', addValue);
        displayType.addEventListener('change', syncColorFields);
        container.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-option-value]');
            if (button) button.closest('[data-option-value-row]')?.remove();
        });
        syncColorFields();
    });
</script>
@endpush
