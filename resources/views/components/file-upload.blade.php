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
    
    // Đọc cấu hình MediaSettings động từ cơ sở dữ liệu
    $mediaSettings = app(\App\Settings\MediaSettings::class);
    $allowedExtensions = $mediaSettings->media_allowed_extensions ?? 'jpg,jpeg,png,webp,gif,pdf,doc,docx';
    $maxSize = $mediaSettings->media_max_size ?? 10;
    
    // Xác định xem tệp tin có phải là định dạng hình ảnh không
    $isImage = strpos($attributes->get('accept', ''), 'image') !== false || in_array($name, ['logo', 'logo_footer', 'favicon', 'seo_image', 'about_image', 'default_product_banner', 'default_promotion_banner', 'default_post_banner']);
    
    $allowedExtsArray = array_map('trim', explode(',', strtolower($allowedExtensions)));
    if ($isImage) {
        $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'];
        $allowedExtsArray = array_intersect($allowedExtsArray, $imageExts);
    }
    
    $accept = $attributes->get('accept') ?? ($isImage ? implode(',', array_map(fn($ext) => '.' . $ext, $allowedExtsArray)) : null);
    $allowedDisplay = implode(', ', array_map('strtoupper', $allowedExtsArray));
@endphp

<div class="mb-3">
    @if($label)
        <label class="form-label font-weight-bold">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    <input 
        type="file" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        class="form-control mb-1 @error($name) is-invalid @enderror" 
        onchange="previewImage(this, '{{ $previewElementId }}')"
        @if($accept) accept="{{ $accept }}" @endif
        {{ $attributes->except('accept') }}
    >
    <div class="text-xs text-secondary mb-2" style="font-size: 0.8rem;">
        Hỗ trợ định dạng: {{ $allowedDisplay }} (tối đa {{ $maxSize }}MB)
    </div>

    <div class="border p-2 bg-{{ $bg }} rounded text-center shadow-sm d-flex align-items-center justify-content-center" style="min-height: 100px; overflow: hidden;">
        <img 
            id="{{ $previewElementId }}" 
            src="{{ $value ? asset($value) : asset('assets/img/no-image.jpg') }}" 
            alt="{{ $label ?? $name }}" 
            class="img-fluid rounded" 
            style="max-height: {{ $previewHeight }}; max-width: {{ $previewWidth }};"
        >
    </div>

    @error($name)
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
