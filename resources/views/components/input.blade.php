@props([
    'name',
    'label' => null,
    'value' => null,
    'type' => 'text',
    'required' => false,
    'placeholder' => '',
    'translatable' => false,
    'id' => null,
    'useOld' => true,
])

@php
    $inputId = $id ?? 'input_' . str_replace(['[', ']'], ['_', ''], $name) . '_' . Str::random(4);
    $translations = [];
    if ($translatable) {
        if (is_array($value)) {
            $translations = $value;
        } elseif (is_object($value) && method_exists($value, 'getTranslations')) {
            $translations = $value->getTranslations($name);
        }
    }
    
    // Lấy danh sách ngôn ngữ hoạt động từ Model Cache, fallback về vi/en
    $dbLangs = \App\Models\Language::getActiveLanguages();
    $langs = $dbLangs->isNotEmpty() ? $dbLangs : collect([
        (object)['code' => 'vi', 'name' => 'Tiếng Việt'],
        (object)['code' => 'en', 'name' => 'English']
    ]);
    $showTabs = $translatable && $langs->count() > 1;
@endphp

<div class="mb-3">
    @if($label)
        <label for="{{ $inputId }}" class="form-label font-weight-bold">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    @if($translatable)
        @if($showTabs)
            <ul class="nav nav-tabs mb-2" id="{{ $inputId }}_tabs" role="tablist">
                @foreach($langs as $index => $lang)
                    @php
                        $langCode = $lang->code;
                        $tabId = $inputId . '_' . $langCode;
                        $hasError = $errors->has($name . '.' . $langCode);
                    @endphp
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $index === 0 ? 'active' : '' }} py-1 px-3" 
                            id="{{ $tabId }}-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#{{ $tabId }}" 
                            type="button" 
                            role="tab" 
                            aria-controls="{{ $tabId }}" 
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                        {{ $lang->name }} 
                        @if($hasError)<span class="badge bg-danger rounded-circle ms-1" style="font-size: 0.6rem; padding: 0.2em 0.4em;">!</span>@endif
                    </button>
                </li>
            @endforeach
        </ul>
        @endif
        <div class="tab-content" id="{{ $inputId }}_tabContent">
            @foreach($langs as $index => $lang)
                @php
                    $langCode = $lang->code;
                    $tabId = $inputId . '_' . $langCode;
                    $hasError = $errors->has($name . '.' . $langCode);
                @endphp
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ $tabId }}" role="tabpanel" aria-labelledby="{{ $tabId }}-tab">
                    <input 
                        type="{{ $type }}" 
                        name="{{ $name }}[{{ $langCode }}]" 
                        id="{{ $tabId }}_field"
                        class="form-control {{ $hasError ? 'is-invalid' : '' }}" 
                        value="{{ $useOld ? old($name . '.' . $langCode, $translations[$langCode] ?? '') : ($translations[$langCode] ?? '') }}"
                        placeholder="{{ $placeholder ?: 'Nhập ' . strtolower($label) . ' (' . $lang->name . ')...' }}"
                        {{ $required && $langCode === 'vi' ? 'data-required=true' : '' }}
                        {{ $attributes }}
                    >
                    @error($name . '.' . $langCode)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach
        </div>
    @else
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $inputId }}"
            class="form-control @error($name) is-invalid @enderror" 
            value="{{ $useOld ? old($name, $value) : $value }}"
            placeholder="{{ $placeholder ?: ($label ? 'Nhập ' . strtolower($label) . '...' : '') }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        >
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    @endif
</div>
