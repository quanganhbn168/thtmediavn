@props([
    'name',
    'label' => null,
    'value' => null,
    'existingUrl' => null,
    'required' => false,
    'id' => null,
    'placeholder' => 'Kéo thả video vào đây hoặc click để chọn file',
])

@php
    $uploadId = $id ?? 'video_upload_' . str_replace(['[', ']'], ['_', ''], $name) . '_' . Str::random(4);
    $existingVideo = $existingUrl ?: ((!empty($value) && !Str::startsWith($value, 'uploads/tmp/')) ? $value : null);
    $existingVideoUrl = $existingVideo
        ? (Str::startsWith((string) $existingVideo, ['http://', 'https://']) ? $existingVideo : asset($existingVideo))
        : null;
    $pendingValue = old($name, Str::startsWith((string) $value, 'uploads/tmp/') ? $value : '');
    $pendingVideoUrl = $pendingValue
        ? (Str::startsWith((string) $pendingValue, ['http://', 'https://']) ? $pendingValue : asset($pendingValue))
        : null;
    $mediaSettings = app(\App\Settings\MediaSettings::class);
    $allowedExtensions = $mediaSettings->media_allowed_extensions ?? 'jpg,jpeg,png,webp,gif,pdf,doc,docx,mp4,webm,mov';
    $allowedVideoExts = array_values(array_intersect(
        array_map('trim', explode(',', strtolower($allowedExtensions))),
        ['mp4', 'webm', 'mov'],
    ));
    $acceptedFiles = implode(',', array_map(fn ($extension) => '.' . $extension, $allowedVideoExts));
    $allowedExtensionsDisplay = implode(', ', array_map('strtoupper', $allowedVideoExts));
    $maxSize = $mediaSettings->media_max_size ?? 10;
@endphp

<div class="mb-3" id="{{ $uploadId }}_container">
    @if($label)
        <label class="form-label font-weight-bold">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    <div class="video-preview-wrapper mb-3 {{ $existingVideoUrl || $pendingVideoUrl ? '' : 'd-none' }}" id="{{ $uploadId }}_preview_container">
        <div class="video-upload-preview position-relative border rounded p-1 bg-light" id="{{ $uploadId }}_preview">
            <video class="w-100 rounded" controls preload="metadata" playsinline>
                <source src="{{ $pendingVideoUrl ?: $existingVideoUrl }}">
                Trình duyệt không hỗ trợ phát video.
            </video>
            @if($pendingVideoUrl)
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle" id="{{ $uploadId }}_remove_temporary_btn" title="Xóa video này"><i class="bi bi-x"></i></button>
            @endif
        </div>

        @if($existingVideoUrl)
            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="{{ $uploadId }}_remove_existing_btn">
                <i class="bi bi-trash me-1"></i>Xóa video hiện có
            </button>
        @endif
    </div>

    <div class="dropzone-wrapper {{ $existingVideoUrl || $pendingVideoUrl ? 'd-none' : '' }}" id="{{ $uploadId }}_dropzone_zone">
        <div id="{{ $uploadId }}" class="dropzone border-dashed border-2 rounded p-4 text-center cursor-pointer bg-light d-flex flex-column align-items-center justify-content-center" style="border-color: #0d6efd !important; min-height: 120px; transition: background-color 0.2s;">
            <div class="dz-message py-2">
                <i class="bi bi-camera-video text-primary" style="font-size: 2.5rem;"></i>
                <p class="text-muted mt-2 mb-0">{{ $placeholder }}</p>
                <div class="text-xs text-secondary mt-1">Hỗ trợ: {{ $allowedExtensionsDisplay ?: 'MP4, WEBM, MOV' }} (tối đa {{ $maxSize }}MB)</div>
            </div>
        </div>
    </div>

    <input type="hidden" name="{{ $name }}" id="{{ $uploadId }}_input" value="{{ $pendingValue }}">
    <input type="hidden" name="{{ $name }}_remove" id="{{ $uploadId }}_remove_input" value="0">

    @error($name)
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@pushOnce('css')
<style>
    .video-upload-preview { max-width: 100%; }
    .video-upload-preview video { display: block; max-height: 260px; background: #111; }
    .video-upload-preview .btn-danger { transform: translate(40%, -40%); padding: 0.1rem 0.4rem; font-size: 0.8rem; }
</style>
@endpushOnce

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('{{ $uploadId }}_input');
        const removeInput = document.getElementById('{{ $uploadId }}_remove_input');
        const previewContainer = document.getElementById('{{ $uploadId }}_preview_container');
        const preview = document.getElementById('{{ $uploadId }}_preview');
        const dropzoneZone = document.getElementById('{{ $uploadId }}_dropzone_zone');
        const removeExistingBtn = document.getElementById('{{ $uploadId }}_remove_existing_btn');
        const removeTemporaryBtn = document.getElementById('{{ $uploadId }}_remove_temporary_btn');
        let existingVideo = {{ $existingVideoUrl ? 'true' : 'false' }};

        function refreshVisibility() {
            const hasVideo = existingVideo || Boolean((input.value || '').trim());
            previewContainer.classList.toggle('d-none', !hasVideo);
            dropzoneZone.classList.toggle('d-none', hasVideo);
        }

        function showTemporaryPreview(path, url) {
            preview.querySelector('video')?.remove();
            const video = document.createElement('video');
            video.className = 'w-100 rounded';
            video.controls = true;
            video.preload = 'metadata';
            video.playsInline = true;

            const source = document.createElement('source');
            source.src = url;
            video.appendChild(source);
            preview.insertBefore(video, preview.querySelector('button'));

            let removeButton = preview.querySelector('.remove-temporary-video');
            if (!removeButton) {
                removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle remove-temporary-video';
                removeButton.title = 'Xóa video này';
                removeButton.innerHTML = '<i class="bi bi-x"></i>';
                preview.appendChild(removeButton);
            }
        }

        const myDropzone = new Dropzone('#{{ $uploadId }}', {
            url: "{{ route('admin.media.upload.temp') }}",
            maxFiles: 1,
            maxFilesize: {{ $maxSize }},
            acceptedFiles: '{{ $acceptedFiles }}',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            dictMaxFilesExceeded: 'Chỉ được chọn một video.',
            dictFileTooBig: 'File quá lớn (@{{filesize}}MB). Dung lượng tối đa là @{{maxFilesize}}MB.',
            init: function () {
                this.on('sending', function (file, xhr, formData) {
                    formData.append('convert_to_webp', '0');
                });

                this.on('success', function (file, response) {
                    if (response.success) {
                        input.value = response.path;
                        removeInput.value = '0';
                        existingVideo = false;
                        showTemporaryPreview(response.path, response.url);
                        this.removeFile(file);
                        refreshVisibility();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Lỗi upload video', text: response.message || 'Có lỗi xảy ra khi tải video.' });
                        this.removeFile(file);
                    }
                });

                this.on('error', function (file, message) {
                    const errorText = typeof message === 'string' ? message : (message.message || 'Lỗi kết nối máy chủ.');
                    Swal.fire({ icon: 'error', title: 'Lỗi upload video', text: errorText });
                    this.removeFile(file);
                });
            }
        });

        if (removeTemporaryBtn) {
            removeTemporaryBtn.addEventListener('click', function () {
                input.value = '';
                preview.querySelector('video')?.remove();
                removeTemporaryBtn.remove();
                refreshVisibility();
            });
        }

        preview.addEventListener('click', function (event) {
            if (!event.target.closest('.remove-temporary-video')) {
                return;
            }

            input.value = '';
            preview.querySelector('video')?.remove();
            event.target.closest('.remove-temporary-video').remove();
            refreshVisibility();
        });

        if (removeExistingBtn) {
            removeExistingBtn.addEventListener('click', function () {
                existingVideo = false;
                removeInput.value = '1';
                preview.querySelector('video')?.remove();
                removeExistingBtn.remove();
                refreshVisibility();
            });
        }

        refreshVisibility();
    });
</script>
@endpush
