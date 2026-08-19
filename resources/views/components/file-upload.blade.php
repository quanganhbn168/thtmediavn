@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'previewId' => null,
    'previewHeight' => '80px',
    'previewWidth' => 'auto',
    'bg' => 'light', // light hoặc dark
])

@php
    $previewElementId = $previewId ?? 'preview_' . str_replace(['[', ']'], ['_', ''], $name) . '_' . Str::random(4);

    $uploadSettings = app(\App\Settings\UploadSettings::class);
    $allowedExtensions = $uploadSettings->media_allowed_extensions ?? 'jpg,jpeg,png,webp,gif,pdf,doc,docx';
    $maxSize = $uploadSettings->media_max_size ?? 10;

    // Xác định xem tệp tin có phải là định dạng hình ảnh không
    $isImage = strpos($attributes->get('accept', ''), 'image') !== false || in_array($name, ['logo', 'logo_footer', 'favicon', 'seo_image', 'about_image', 'default_promotion_banner', 'default_post_banner']);

    $allowedExtsArray = array_map('trim', explode(',', strtolower($allowedExtensions)));
    if ($isImage) {
        $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'];
        $allowedExtsArray = array_intersect($allowedExtsArray, $imageExts);
    }

    $accept = $attributes->get('accept') ?? ($isImage ? implode(',', array_map(fn($ext) => '.' . $ext, $allowedExtsArray)) : null);
    $allowedDisplay = implode(', ', array_map('strtoupper', $allowedExtsArray));
@endphp

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="ui-label">
            {{ $label }}
            @if($required) <span class="text-red-600">*</span> @endif
        </label>
    @endif

    <input
        type="file"
        name="{{ $name }}"
        id="{{ $name }}"
        class="ui-input mb-1 @error($name) border-red-500 @enderror"
        onchange="previewImage(this, '{{ $previewElementId }}')"
        @if($accept) accept="{{ $accept }}" @endif
        {{ $attributes->except('accept') }}
    >
    <div class="mb-2 text-xs text-muted">
        Hỗ trợ định dạng: {{ $allowedDisplay }} (tối đa {{ $maxSize }}MB)
    </div>

    <div class="flex min-h-[100px] items-center justify-center overflow-hidden rounded-xl border border-line bg-{{ $bg }} p-2 text-center shadow-sm">
        <img
            id="{{ $previewElementId }}"
            src="{{ $value ? asset($value) : asset('assets/images/no-image.svg') }}"
            alt="{{ $label ?? $name }}"
            class="max-w-full rounded object-contain"
            style="max-height: {{ $previewHeight }}; max-width: {{ $previewWidth }};"
        >
    </div>

    @error($name)
        <div class="ui-error">{{ $message }}</div>
    @enderror
</div>
