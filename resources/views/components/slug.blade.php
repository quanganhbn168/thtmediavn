@props([
    'name' => 'slug',
    'label' => 'Đường dẫn thân thiện (Slug)',
    'value' => null,
    'source' => null, // selector/ID của input nguồn (ví dụ: slider_name hoặc #slider_name)
    'required' => false,
    'translatable' => false,
    'id' => null,
])

@php
    $inputId = $id ?? 'slug_' . str_replace(['[', ']'], ['_', ''], $name) . '_' . Str::random(4);
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

    // Làm sạch source selector để dùng trong JS
    $cleanSource = $source ? (Str::startsWith($source, '#') ? substr($source, 1) : $source) : null;
@endphp

<div class="mb-4">
    @if($label)
        <label for="{{ $inputId }}" class="ui-label text-green-700">
            <i class="fa-solid fa-link mr-1"></i>{{ $label }}
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
                    $sourceFieldId = $cleanSource ? $cleanSource . '_' . $langCode . '_field' : '';
                @endphp
                <div class="{{ $index === 0 ? '' : 'hidden' }}" id="{{ $tabId }}" data-language-tab-panel role="tabpanel" aria-labelledby="{{ $tabId }}-tab">
                    <div class="flex items-start">
                        <input
                            type="text"
                            name="{{ $name }}[{{ $langCode }}]"
                            id="{{ $tabId }}_field"
                            class="ui-input rounded-r-none {{ $hasError ? 'border-red-500' : '' }}"
                            value="{{ old($name . '.' . $langCode, $translations[$langCode] ?? '') }}"
                            placeholder="slug-{{ $langCode }}"
                            readonly
                            data-locked="true"
                            {{ $attributes }}
                        >
                        <button class="inline-flex min-h-12 items-center justify-center rounded-r-lg border border-l-0 border-line bg-white px-3 text-muted transition duration-200 hover:bg-slate-50 hover:text-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary toggle-slug-lock" type="button" data-target="#{{ $tabId }}_field" data-source-id="{{ $sourceFieldId }}">
                            <i class="fa-solid fa-lock"></i>
                        </button>
                        @error($name . '.' . $langCode)
                            <div class="ui-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex items-start">
            <input
                type="text"
                name="{{ $name }}"
                id="{{ $inputId }}"
                class="ui-input rounded-r-none @error($name) border-red-500 @enderror"
                value="{{ old($name, $value) }}"
                placeholder="slug-duong-dan"
                readonly
                data-locked="true"
                {{ $required ? 'required' : '' }}
                {{ $attributes }}
            >
            <button class="inline-flex min-h-12 items-center justify-center rounded-r-lg border border-l-0 border-line bg-white px-3 text-muted transition duration-200 hover:bg-slate-50 hover:text-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary toggle-slug-lock" type="button" data-target="#{{ $inputId }}" data-source-id="{{ $cleanSource }}">
                <i class="fa-solid fa-lock"></i>
            </button>
            @error($name)
                <div class="ui-error">{{ $message }}</div>
            @enderror
        </div>
    @endif
</div>

@pushOnce('js')
<script>
    // Hàm chuyển chữ tiếng Việt có dấu thành slug
    function generateSlugFromText(text) {
        let slug = text.toString().toLowerCase().trim();

        slug = slug.replace(/á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
        slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
        slug = slug.replace(/í|ì|ỉ|ĩ|ị/gi, 'i');
        slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
        slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
        slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
        slug = slug.replace(/đ/gi, 'd');

        slug = slug.replace(/[^a-z0-9 -]/g, '');
        slug = slug.replace(/\s+/g, '-');
        slug = slug.replace(/-+/g, '-');
        slug = slug.replace(/^-+/, '');
        slug = slug.replace(/-+$/, '');

        return slug;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.body.addEventListener('click', function (e) {
            const btn = e.target.closest('.toggle-slug-lock');
            if (!btn) return;

            const targetSelector = btn.getAttribute('data-target');
            const targetInput = document.querySelector(targetSelector);
            if (!targetInput) return;

            const isLocked = targetInput.getAttribute('readonly') !== null;
            if (isLocked) {
                targetInput.removeAttribute('readonly');
                targetInput.setAttribute('data-locked', 'false');
                btn.innerHTML = '<i class="fa-solid fa-pen text-warning"></i>';
                btn.classList.add('border-amber-500', 'bg-amber-50', 'text-amber-700');
                btn.classList.remove('bg-white', 'text-muted');
            } else {
                targetInput.setAttribute('readonly', 'readonly');
                targetInput.setAttribute('data-locked', 'true');
                btn.innerHTML = '<i class="fa-solid fa-lock"></i>';
                btn.classList.remove('border-amber-500', 'bg-amber-50', 'text-amber-700');
                btn.classList.add('bg-white', 'text-muted');

                // Đồng bộ lại ngay khi khóa
                const sourceId = btn.getAttribute('data-source-id');
                const sourceElement = document.getElementById(sourceId);
                if (sourceElement) {
                    targetInput.value = generateSlugFromText(sourceElement.value);
                }
            }
        });
    });
</script>
@endpushOnce

@if($cleanSource)
@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($translatable)
            @foreach($langs as $lang)
                @php
                    $langCode = $lang->code;
                    $sourceFieldId = $cleanSource . '_' . $langCode . '_field';
                    $targetFieldId = $inputId . '_' . $langCode . '_field';
                @endphp
                (function() {
                    const sourceEl = document.getElementById('{{ $sourceFieldId }}');
                    const targetEl = document.getElementById('{{ $targetFieldId }}');
                    if (sourceEl && targetEl) {
                        sourceEl.addEventListener('keyup', function () {
                            if (targetEl.getAttribute('data-locked') === 'true') {
                                targetEl.value = generateSlugFromText(this.value);
                            }
                        });
                    }
                })();
            @endforeach
        @else
            (function() {
                const sourceEl = document.getElementById('{{ $cleanSource }}');
                const targetEl = document.getElementById('{{ $inputId }}');
                if (sourceEl && targetEl) {
                    sourceEl.addEventListener('keyup', function () {
                        if (targetEl.getAttribute('data-locked') === 'true') {
                            targetEl.value = generateSlugFromText(this.value);
                        }
                    });
                }
            })();
        @endif
    });
</script>
@endpush
@endif
