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

<div class="inline-flex items-center">
    <input
        class="toggle-field-switch h-5 w-9 cursor-pointer appearance-none rounded-full bg-slate-300 align-middle transition checked:bg-primary"
        type="checkbox"
        role="switch"
        id="{{ $toggleId }}"
        data-model="{{ $model }}"
        data-id="{{ $id }}"
        data-field="{{ $field }}"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
    >
    @if($label)
        <label class="ml-2 cursor-pointer font-bold" for="{{ $toggleId }}">
            {{ $label }}
        </label>
    @endif
</div>
