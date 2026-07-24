@props([
    'name',
    'label' => null,
    'value' => null, // Url hoặc path của ảnh cũ nếu có
    'existingUrl' => null,
    'existingImages' => [],
    'required' => false,
    'id' => null,
    'placeholder' => 'Kéo thả ảnh vào đây hoặc click để chọn file',
    'maxFiles' => 1,
    'convertToWebp' => null, // null (dùng cấu hình hệ thống), true hoặc false
    'width' => null,
    'height' => null,
])

@php
    $uploadId = $id ?? 'image_upload_' . str_replace(['[', ']'], ['_', ''], $name) . '_' . Str::random(4);
    $maxFiles = max(1, (int) $maxFiles);
    $isMultiple = $maxFiles > 1;
    $existingImage = $existingUrl ?: ((!empty($value) && !Str::startsWith($value, 'uploads/tmp/')) ? $value : null);
    $pendingValue = old($name, Str::startsWith((string) $value, 'uploads/tmp/') ? $value : '');
    $pendingValues = $pendingValue ? array_values(array_filter(explode('|', (string) $pendingValue), fn ($path): bool => trim((string) $path) !== '')) : [];
    $normalizedExistingImages = collect($existingImages)
        ->map(function ($image) {
            $url = is_array($image) ? ($image['url'] ?? null) : $image;

            if (blank($url)) {
                return null;
            }

            return Str::startsWith((string) $url, ['http://', 'https://']) ? $url : asset($url);
        })
        ->filter()
        ->values();

    if ($normalizedExistingImages->isEmpty() && $existingImage) {
        $normalizedExistingImages->push(
            Str::startsWith((string) $existingImage, ['http://', 'https://'])
                ? $existingImage
                : asset($existingImage)
        );
    }

    $existingImageCount = $normalizedExistingImages->count();
    $currentImageCount = $existingImageCount + count($pendingValues);
    $hasImages = $currentImageCount > 0;

    // Đọc cấu hình MediaSettings động từ cơ sở dữ liệu
    $mediaSettings = app(\App\Settings\MediaSettings::class);
    $allowedExtensions = $mediaSettings->media_allowed_extensions ?? 'jpg,jpeg,png,webp,gif,pdf,doc,docx';
    $maxSize = $mediaSettings->media_max_size ?? 10;

    // Lọc riêng các định dạng ảnh phổ biến cho trình chọn ảnh Dropzone
    $allowedExtsArray = array_map('trim', explode(',', strtolower($allowedExtensions)));
    $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'];
    $allowedImageExts = array_intersect($allowedExtsArray, $imageExts);

    $acceptedFiles = implode(',', array_map(fn($ext) => '.' . $ext, $allowedImageExts));
    $allowedExtensionsDisplay = implode(', ', array_map('strtoupper', $allowedImageExts));
@endphp

<div class="mb-3" id="{{ $uploadId }}_container">
    @if($label)
        <label class="form-label font-weight-bold">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    <div class="image-preview-wrapper mb-3 {{ $hasImages ? '' : 'd-none' }}" id="{{ $uploadId }}_preview_container">
        <div class="d-flex flex-wrap gap-3" id="{{ $uploadId }}_preview_list">
            @foreach($normalizedExistingImages as $imageUrl)
                <div class="image-upload-preview position-relative border rounded p-1 bg-light" data-existing-image>
                    <img src="{{ $imageUrl }}" class="img-fluid rounded" alt="{{ $label ?: 'Ảnh đã tải lên' }}">
                </div>
            @endforeach

            @foreach($pendingValues as $pendingPath)
                <div class="image-upload-preview position-relative border rounded p-1 bg-light" data-temporary-path="{{ $pendingPath }}">
                    <img src="{{ Str::startsWith($pendingPath, ['http://', 'https://']) ? $pendingPath : asset($pendingPath) }}" class="img-fluid rounded" alt="{{ $label ?: 'Ảnh mới' }}">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle remove-temporary-image" title="Xóa ảnh này">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            @endforeach
        </div>

        @if($existingImageCount > 0)
            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="{{ $uploadId }}_remove_existing_btn">
                <i class="bi bi-trash me-1"></i>{{ $existingImageCount > 1 ? 'Xóa toàn bộ ảnh hiện có' : 'Xóa ảnh hiện có' }}
            </button>
        @endif
    </div>

    <!-- Dropzone Zone -->
    <div class="dropzone-wrapper {{ $currentImageCount >= $maxFiles ? 'd-none' : '' }}" id="{{ $uploadId }}_dropzone_zone">
        <div id="{{ $uploadId }}" class="dropzone border-dashed border-2 rounded p-4 text-center cursor-pointer bg-light d-flex flex-column align-items-center justify-content-center" 
             style="border-color: #0d6efd !important; min-height: 120px; transition: background-color 0.2s;">
            <div class="dz-message py-2">
                <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 2.5rem;"></i>
                <p class="text-muted mt-2 mb-0">{{ $placeholder }}</p>
                <div class="text-xs text-secondary mt-1">Hỗ trợ các định dạng: {{ $allowedExtensionsDisplay }} (tối đa {{ $maxSize }}MB)</div>
                @if($isMultiple)
                    <div class="text-xs text-primary mt-1">Có thể chọn tối đa {{ $maxFiles }} ảnh</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Hidden input để lưu trữ path của file ảnh (ảnh cũ hoặc ảnh tạm mới) -->
    <input type="hidden" name="{{ $name }}" id="{{ $uploadId }}_input" value="{{ $pendingValue }}">
    <input type="hidden" name="{{ $name }}_remove" id="{{ $uploadId }}_remove_input" value="0">

    @error($name)
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@pushOnce('css')
<style>
    .dropzone {
        border: 2px dashed #0d6efd !important;
        background: #f8f9fa;
    }
    .dropzone:hover {
        background: #e9ecef !important;
    }
    .border-dashed {
        border-style: dashed !important;
    }
    .image-upload-preview {
        width: 132px;
        height: 132px;
    }
    .image-upload-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .image-upload-preview .btn-danger {
        transform: translate(40%, -40%);
        padding: 0.1rem 0.4rem;
        font-size: 0.8rem;
    }
</style>
@endpushOnce

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('{{ $uploadId }}_input');
        const removeInput = document.getElementById('{{ $uploadId }}_remove_input');
        const previewContainer = document.getElementById('{{ $uploadId }}_preview_container');
        const previewList = document.getElementById('{{ $uploadId }}_preview_list');
        const removeExistingBtn = document.getElementById('{{ $uploadId }}_remove_existing_btn');
        const dropzoneZone = document.getElementById('{{ $uploadId }}_dropzone_zone');
        const maxFiles = {{ (int) $maxFiles }};
        let existingImageCount = {{ $existingImageCount }};

        function temporaryPaths() {
            return (input.value || '')
                .split('|')
                .map((value) => value.trim())
                .filter(Boolean);
        }

        function setTemporaryPaths(paths) {
            input.value = [...new Set(paths)].slice(0, maxFiles).join('|');
        }

        function refreshVisibility() {
            const total = existingImageCount + temporaryPaths().length;
            previewContainer.classList.toggle('d-none', total === 0);
            dropzoneZone.classList.toggle('d-none', total >= maxFiles);
        }

        function addTemporaryPreview(path, url) {
            if (previewList.querySelector(`[data-temporary-path="${CSS.escape(path)}"]`)) {
                return;
            }

            const preview = document.createElement('div');
            preview.className = 'image-upload-preview position-relative border rounded p-1 bg-light';
            preview.dataset.temporaryPath = path;

            const image = document.createElement('img');
            image.src = url;
            image.alt = @json($label ?: 'Ảnh mới');
            image.className = 'img-fluid rounded';

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle remove-temporary-image';
            removeButton.title = 'Xóa ảnh này';
            removeButton.innerHTML = '<i class="bi bi-x"></i>';

            preview.append(image, removeButton);
            previewList.appendChild(preview);
        }

        const myDropzone = new Dropzone('#{{ $uploadId }}', {
            url: "{{ route('admin.media.upload.temp') }}",
            maxFiles: maxFiles,
            maxFilesize: {{ $maxSize }},
            acceptedFiles: '{{ $acceptedFiles }}',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            dictMaxFilesExceeded: "Bạn chỉ được chọn tối đa {{ (int) $maxFiles }} ảnh.",
            dictFileTooBig: "File quá lớn (@{{filesize}}MB). Dung lượng tối đa cho phép là @{{maxFilesize}}MB.",
            init: function () {
                this.on("sending", function (file, xhr, formData) {
                    @if(isset($convertToWebp))
                        formData.append('convert_to_webp', '{{ $convertToWebp ? "1" : "0" }}');
                    @endif
                    @if(isset($width))
                        formData.append('width', '{{ $width }}');
                    @endif
                    @if(isset($height))
                        formData.append('height', '{{ $height }}');
                    @endif
                });

                this.on("success", function (file, response) {
                    if (response.success) {
                        const paths = temporaryPaths();
                        if (existingImageCount + paths.length >= maxFiles) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Đã đủ số lượng ảnh',
                                text: `Chỉ được chọn tối đa ${maxFiles} ảnh.`
                            });
                            this.removeFile(file);
                            refreshVisibility();
                            return;
                        }

                        paths.push(response.path);
                        setTemporaryPaths(paths);
                        removeInput.value = '0';
                        addTemporaryPreview(response.path, response.url);
                        this.removeFile(file);
                        refreshVisibility();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi tải ảnh',
                            text: response.message || 'Có lỗi xảy ra khi tải ảnh.'
                        });
                        this.removeFile(file);
                    }
                });

                this.on("error", function (file, message) {
                    let errorText = typeof message === 'string' ? message : (message.message || 'Lỗi kết nối máy chủ.');
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi upload ảnh',
                        text: errorText
                    });
                    this.removeFile(file);
                });
            }
        });

        previewList.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.remove-temporary-image');
            if (!removeButton) {
                return;
            }

            const preview = removeButton.closest('[data-temporary-path]');
            const path = preview.dataset.temporaryPath;
            setTemporaryPaths(temporaryPaths().filter((value) => value !== path));
            preview.remove();
            refreshVisibility();
        });

        if (removeExistingBtn) {
            removeExistingBtn.addEventListener('click', function () {
                previewList.querySelectorAll('[data-existing-image]').forEach((preview) => preview.remove());
                existingImageCount = 0;
                removeInput.value = '1';
                removeExistingBtn.remove();
                refreshVisibility();
            });
        }

        refreshVisibility();
    });
</script>
@endpush
