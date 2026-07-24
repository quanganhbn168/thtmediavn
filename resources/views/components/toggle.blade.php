@props([
    'model',
    'id',
    'field' => 'is_active',
    'checked' => false,
    'label' => null,
    'disabled' => false,
])

@php
    $toggleId = 'toggle_' . strtolower($model) . '_' . $field . '_' . $id . '_' . Str::random(4);
@endphp

<div class="form-check form-switch d-inline-block">
    <input 
        class="form-check-input toggle-field-switch cursor-pointer" 
        type="checkbox" 
        role="switch" 
        id="{{ $toggleId }}"
        data-model="{{ $model }}"
        data-id="{{ $id }}"
        data-field="{{ $field }}"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
        style="width: 2.2em; height: 1.1em;"
    >
    @if($label)
        <label class="form-check-label ms-1 cursor-pointer font-weight-bold" for="{{ $toggleId }}">
            {{ $label }}
        </label>
    @endif
</div>
