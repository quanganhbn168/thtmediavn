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

<div class="mb-3">
    @if($label)
        <label for="{{ $inputId }}" class="form-label font-weight-bold text-success">
            <i class="bi bi-link-45deg me-1"></i>{{ $label }}
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
                    $sourceFieldId = $cleanSource ? $cleanSource . '_' . $langCode . '_field' : '';
                @endphp
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ $tabId }}" role="tabpanel" aria-labelledby="{{ $tabId }}-tab">
                    <div class="input-group">
                        <input 
                            type="text" 
                            name="{{ $name }}[{{ $langCode }}]" 
                            id="{{ $tabId }}_field"
                            class="form-control {{ $hasError ? 'is-invalid' : '' }}" 
                            value="{{ old($name . '.' . $langCode, $translations[$langCode] ?? '') }}"
                            placeholder="slug-{{ $langCode }}"
                            readonly
                            data-locked="true"
                            {{ $attributes }}
                        >
                        <button class="btn btn-outline-secondary toggle-slug-lock" type="button" data-target="#{{ $tabId }}_field" data-source-id="{{ $sourceFieldId }}">
                            <i class="bi bi-lock-fill"></i>
                        </button>
                        @error($name . '.' . $langCode)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="input-group">
            <input 
                type="text" 
                name="{{ $name }}" 
                id="{{ $inputId }}"
                class="form-control @error($name) is-invalid @enderror" 
                value="{{ old($name, $value) }}"
                placeholder="slug-duong-dan"
                readonly
                data-locked="true"
                {{ $required ? 'required' : '' }}
                {{ $attributes }}
            >
            <button class="btn btn-outline-secondary toggle-slug-lock" type="button" data-target="#{{ $inputId }}" data-source-id="{{ $cleanSource }}">
                <i class="bi bi-lock-fill"></i>
            </button>
            @error($name)
                <div class="invalid-feedback">{{ $message }}</div>
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
                btn.innerHTML = '<i class="bi bi-pencil-fill text-warning"></i>';
                btn.classList.add('btn-warning');
                btn.classList.remove('btn-outline-secondary');
            } else {
                targetInput.setAttribute('readonly', 'readonly');
                targetInput.setAttribute('data-locked', 'true');
                btn.innerHTML = '<i class="bi bi-lock-fill"></i>';
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-outline-secondary');
                
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
