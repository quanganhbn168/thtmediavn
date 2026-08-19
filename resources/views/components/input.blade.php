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

<div class="mb-4">
    @if($label)
        <label for="{{ $inputId }}" class="ui-label">
            {{ $label }}
            @if($required) <span class="text-red-600">*</span> @endif
        </label>
    @endif

    @if($translatable)
        @if($showTabs)
            <div class="mb-3 flex flex-wrap gap-2" id="{{ $inputId }}_tabs" data-language-tabs role="tablist">
                @foreach($langs as $index => $lang)
                    @php
                        $langCode = $lang->code;
                        $tabId = $inputId . '_' . $langCode;
                        $hasError = $errors->has($name . '.' . $langCode);
                    @endphp
                <button class="rounded-lg border border-line px-3 py-2 text-sm font-semibold text-muted transition hover:border-primary hover:text-primary {{ $index === 0 ? 'border-primary bg-primary-soft text-primary' : 'bg-white' }}"
                            id="{{ $tabId }}-tab"
                            data-language-tab-toggle="{{ $tabId }}"
                            type="button"
                            role="tab"
                            aria-controls="{{ $tabId }}"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                        {{ $lang->name }}
                        @if($hasError)<span class="ml-1 inline-flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs text-white">!</span>@endif
                    </button>
            @endforeach
            </div>
        @endif
        <div class="tab-content" id="{{ $inputId }}_tabContent">
            @foreach($langs as $index => $lang)
                @php
                    $langCode = $lang->code;
                    $tabId = $inputId . '_' . $langCode;
                    $hasError = $errors->has($name . '.' . $langCode);
                @endphp
                <div class="{{ $index === 0 ? '' : 'hidden' }}" id="{{ $tabId }}" data-language-tab-panel role="tabpanel" aria-labelledby="{{ $tabId }}-tab">
                    <input
                        type="{{ $type }}"
                        name="{{ $name }}[{{ $langCode }}]"
                        id="{{ $tabId }}_field"
                        class="ui-input {{ $hasError ? 'border-red-500' : '' }}"
                        value="{{ $useOld ? old($name . '.' . $langCode, $translations[$langCode] ?? '') : ($translations[$langCode] ?? '') }}"
                        placeholder="{{ $placeholder ?: 'Nhập ' . strtolower($label) . ' (' . $lang->name . ')...' }}"
                        {{ $required && $langCode === 'vi' ? 'data-required=true' : '' }}
                        {{ $attributes }}
                    >
                    @error($name . '.' . $langCode)
                        <div class="ui-error">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach
        </div>
    @else
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $inputId }}"
            class="ui-input @error($name) border-red-500 @enderror"
            value="{{ $useOld ? old($name, $value) : $value }}"
            placeholder="{{ $placeholder ?: ($label ? 'Nhập ' . strtolower($label) . '...' : '') }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        >
        @error($name)
            <div class="ui-error">{{ $message }}</div>
        @enderror
    @endif
</div>
