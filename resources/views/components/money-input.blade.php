@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'placeholder' => '',
    'currency' => 'VND',
    'decimals' => 0,
    'id' => null,
    'useOld' => true,
    'min' => null,
])

@php
    $inputId = $id ?? 'money_input_' . str_replace(['[', ']'], ['_', ''], $name) . '_' . Str::random(4);
    $rawValue = $useOld ? old($name, $value) : $value;
    $rawValue = $rawValue === null ? '' : (string) $rawValue;
    $alpineInitialValue = preg_match('/^-?\d+(?:\.\d+)?$/', $rawValue) ? $rawValue : 'null';
    $hasError = $errors->has($name);
@endphp

<div class="mb-3" x-data="moneyInput({ initialValue: {{ $alpineInitialValue }}, decimals: {{ (int) $decimals }} })">
    @if($label)
        <label for="{{ $inputId }}" class="form-label font-weight-bold">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    <div class="input-group">
        <input
            type="text"
            id="{{ $inputId }}"
            class="form-control {{ $hasError ? 'is-invalid' : '' }}"
            x-mask:dynamic="$money($input, ',', '.', {{ (int) $decimals }})"
            x-model="display"
            x-on:input="onInput($event.target.value)"
            x-on:blur="onBlur()"
            placeholder="{{ $placeholder ?: ($label ? 'Nhập ' . strtolower($label) . '...' : '') }}"
            @required($required)
            @if($min !== null) min="{{ $min }}" @endif
            @disabled($attributes->get('disabled'))
        >
        <span class="input-group-text">{{ $currency }}</span>
    </div>

    <input type="hidden" name="{{ $name }}" :value="raw">

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

@once
    @push('js')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('moneyInput', ({ initialValue = '', decimals = 0 } = {}) => ({
                    display: '',
                    raw: '',
                    decimals: Number(decimals) || 0,

                    init() {
                        this.raw = this.normalize(initialValue);
                        this.display = this.format(this.raw);
                    },

                    onInput(value) {
                        this.raw = this.normalize(value);
                        this.display = this.format(this.raw);
                    },

                    onBlur() {
                        this.raw = this.normalize(this.raw);
                        this.display = this.format(this.raw);
                    },

                    normalize(value) {
                        let v = (value ?? '').toString().trim().replace(/\s/g, '');
                        if (!v) {
                            return '';
                        }

                        v = v.replace(/[^0-9.,-]/g, '');
                        if (!v || v === '-') {
                            return '';
                        }

                        const hasDot = v.includes('.');
                        const hasComma = v.includes(',');
                        const lastDot = v.lastIndexOf('.');
                        const lastComma = v.lastIndexOf(',');

                        if (hasDot && hasComma) {
                            if (lastComma > lastDot) {
                                v = v.replace(/\./g, '').replace(/,/g, '.');
                            } else {
                                v = v.replace(/,/g, '');
                            }
                        } else if (hasComma) {
                            if (/^\d{1,3}(,\d{3})+$/.test(v)) {
                                v = v.replace(/,/g, '');
                            } else {
                                v = v.replace(/,/g, '.');
                            }
                        } else if (hasDot) {
                            if (/^\d{1,3}(\.\d{3})+$/.test(v)) {
                                v = v.replace(/\./g, '');
                            }
                        }

                        if (this.decimals === 0 && v.includes('.')) {
                            v = v.split('.')[0];
                        }

                        if (this.decimals > 0 && v.includes('.')) {
                            const parts = v.split('.');
                            if (parts[1] && parts[1].length > this.decimals) {
                                parts[1] = parts[1].slice(0, this.decimals);
                            }
                            v = parts.join('.');
                        }

                        return v;
                    },

                    format(value) {
                        if (!value) {
                            return '';
                        }

                        const numberValue = Number(value);
                        if (Number.isNaN(numberValue)) {
                            return '';
                        }

                        const formatter = new Intl.NumberFormat('vi-VN', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: this.decimals,
                        });

                        return formatter.format(numberValue);
                    },
                }));
            });
        </script>
    @endpush
@endonce
