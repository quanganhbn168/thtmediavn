@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'multiple' => false,
    'placeholder' => '--- Chọn ---',
    'required' => false,
    'tomSelect' => true,
    'tomSelectCreate' => false,
    'id' => null,
])

@php
    $selectId = $id ?? 'select_' . str_replace(['[', ']'], ['_', ''], $name) . '_' . Str::random(4);
    $selectedValues = is_array($selected) ? $selected : ($selected instanceof \Illuminate\Support\Collection ? $selected->toArray() : [$selected]);
    
    // Nếu là multiple name cần kết thúc bằng []
    $selectName = $name . ($multiple && !Str::endsWith($name, '[]') ? '[]' : '');
@endphp

<div class="mb-3">
    @if($label)
        <label for="{{ $selectId }}" class="form-label font-weight-bold">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    <select 
        name="{{ $selectName }}" 
        id="{{ $selectId }}" 
        class="form-select @error($name) is-invalid @enderror" 
        {{ $multiple ? 'multiple' : '' }} 
        {{ $required ? 'required' : '' }}
        data-tom-select="{{ $tomSelect && $multiple ? '1' : '0' }}"
        data-tom-select-create="{{ $tomSelect && $multiple && $tomSelectCreate ? '1' : '0' }}"
        data-placeholder="{{ $placeholder }}"
        {{ $attributes }}
    >
        @if(!$multiple && $placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $val => $lbl)
            <option value="{{ $val }}" {{ in_array((string)$val, array_map('strval', $selectedValues)) ? 'selected' : '' }}>
                {{ $lbl }}
            </option>
        @endforeach
    </select>

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
