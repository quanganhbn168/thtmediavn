@props([
    'name' => 'image',
    'label' => 'Ảnh sản phẩm',
    'existingImages' => [],
    'maxFiles' => 9,
    'required' => false,
])

@php
    $galleryId = 'product_gallery_' . Str::random(6);
    $maxFiles = min(9, max(1, (int) $maxFiles));
    $removedIds = collect(json_decode((string) old($name . '_removed_ids', '[]'), true) ?: [])
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->values();
    $items = collect($existingImages)
        ->filter(fn ($image) => is_array($image) && !empty($image['id']) && !empty($image['url']))
        ->reject(fn ($image) => $removedIds->contains((int) $image['id']))
        ->map(fn ($image) => [
            'key' => 'existing:' . (int) $image['id'],
            'existing_id' => (int) $image['id'],
            'temporary_path' => null,
            'url' => $image['url'],
        ]);
    $temporaryPaths = collect(explode('|', (string) old($name, '')))
        ->map(fn ($path) => trim($path))
        ->filter()
        ->unique()
        ->map(fn ($path) => [
            'key' => 'temporary:' . $path,
            'existing_id' => null,
            'temporary_path' => $path,
            'url' => Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path),
        ]);
    $items = $items->concat($temporaryPaths)->keyBy('key');
    $oldOrder = collect(json_decode((string) old($name . '_order', '[]'), true) ?: []);
    $orderedItems = $oldOrder
        ->map(fn ($key) => $items->pull((string) $key))
        ->filter()
        ->concat($items->values())
        ->take($maxFiles)
        ->values();
    $mediaSettings = app(\App\Settings\MediaSettings::class);
    $maxSize = $mediaSettings->media_max_size ?? 10;
    $allowedExtensions = $mediaSettings->media_allowed_extensions ?? 'jpg,jpeg,png,webp,gif';
    $allowedImageExtensions = array_values(array_intersect(
        array_map('trim', explode(',', strtolower($allowedExtensions))),
        ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif']
    ));
    $acceptedFiles = implode(',', array_map(fn ($extension) => '.' . $extension, $allowedImageExtensions));
@endphp

<div class="product-gallery {{ $orderedItems->isNotEmpty() ? 'has-images' : '' }}" id="{{ $galleryId }}" data-max-files="{{ $maxFiles }}">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
        <label class="form-label fw-bold mb-0">
            {{ $label }}
            @if($required)<span class="text-danger">*</span>@endif
        </label>
        <span class="text-muted small"><span data-gallery-count>{{ $orderedItems->count() }}</span>/{{ $maxFiles }} ảnh</span>
    </div>

    <div class="product-gallery-grid {{ $orderedItems->isEmpty() ? 'd-none' : '' }}" data-gallery-grid>
        @foreach($orderedItems as $item)
            <article
                class="product-gallery-item"
                data-gallery-item
                data-key="{{ $item['key'] }}"
                @if($item['existing_id']) data-existing-id="{{ $item['existing_id'] }}" @endif
                @if($item['temporary_path']) data-temporary-path="{{ $item['temporary_path'] }}" @endif
            >
                <img src="{{ $item['url'] }}" alt="Ảnh sản phẩm" data-gallery-image>
                <span class="product-gallery-primary-badge" data-primary-badge>
                    <i class="bi bi-star-fill me-1"></i>Ảnh đại diện
                </span>
                <div class="product-gallery-actions">
                    <button type="button" class="btn btn-light btn-sm" data-edit-image title="Chỉnh sửa ảnh">
                        <i class="bi bi-crop"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-remove-image title="Xóa ảnh">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <span class="product-gallery-drag"><i class="bi bi-grip-vertical"></i></span>
            </article>
        @endforeach
    </div>

    <div class="product-gallery-dropzone {{ $orderedItems->count() >= $maxFiles ? 'd-none' : '' }}" id="{{ $galleryId }}_dropzone" data-gallery-dropzone>
        <div class="dz-message product-gallery-empty-upload">
            <i class="bi bi-cloud-arrow-up"></i>
            <strong>Kéo thả ảnh sản phẩm vào đây</strong>
            <span>Chọn cùng lúc tối đa {{ $maxFiles }} ảnh, ảnh đầu tiên là ảnh đại diện</span>
            <small>{{ implode(', ', array_map('strtoupper', $allowedImageExtensions)) }} · tối đa {{ $maxSize }}MB/ảnh</small>
        </div>
        <div class="product-gallery-compact-upload">
            <i class="bi bi-plus-lg"></i><span>Thêm ảnh</span>
        </div>
    </div>

    <input type="hidden" name="{{ $name }}" value="{{ $orderedItems->pluck('temporary_path')->filter()->implode('|') }}" data-image-input>
    <input type="hidden" name="{{ $name }}_order" value="{{ $orderedItems->pluck('key')->values()->toJson() }}" data-order-input>
    <input type="hidden" name="{{ $name }}_removed_ids" value="{{ $removedIds->toJson() }}" data-removed-input>

    @error($name)
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
    @error($name . '_order')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror

    <div class="modal fade" id="{{ $galleryId }}_editor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-crop me-2"></i>Chỉnh sửa ảnh sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="product-gallery-editor-stage">
                        <img src="" alt="Ảnh cần chỉnh sửa" data-editor-image>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                        <button type="button" class="btn btn-outline-secondary" data-crop-action="rotate-left"><i class="bi bi-arrow-counterclockwise me-1"></i>Xoay trái</button>
                        <button type="button" class="btn btn-outline-secondary" data-crop-action="rotate-right"><i class="bi bi-arrow-clockwise me-1"></i>Xoay phải</button>
                        <button type="button" class="btn btn-outline-secondary" data-crop-action="zoom-in"><i class="bi bi-zoom-in me-1"></i>Phóng to</button>
                        <button type="button" class="btn btn-outline-secondary" data-crop-action="zoom-out"><i class="bi bi-zoom-out me-1"></i>Thu nhỏ</button>
                        <button type="button" class="btn btn-outline-secondary" data-crop-action="reset"><i class="bi bi-arrow-repeat me-1"></i>Đặt lại</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" data-apply-crop>
                        <i class="bi bi-check-lg me-1"></i>Áp dụng
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@pushOnce('css', 'product-gallery-css')
<link rel="stylesheet" href="{{ asset('vendor/cropperjs/cropper.min.css') }}">
<style>
    .product-gallery-grid {
        display: grid;
        grid-template-columns: minmax(260px, 2fr) repeat(4, minmax(105px, 1fr));
        grid-template-rows: repeat(2, 145px);
        grid-auto-rows: 145px;
        gap: 12px;
        margin-bottom: 12px;
    }
    .product-gallery-item {
        position: relative;
        min-width: 0;
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        background: var(--bs-tertiary-bg);
        cursor: grab;
    }
    .product-gallery-item:first-child {
        grid-row: 1 / span 2;
    }
    .product-gallery-item.sortable-ghost {
        opacity: .35;
    }
    .product-gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .product-gallery-primary-badge {
        display: none;
        position: absolute;
        left: 10px;
        bottom: 10px;
        padding: 5px 9px;
        border-radius: 999px;
        color: #fff;
        background: rgba(13, 110, 253, .92);
        font-size: .75rem;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .18);
    }
    .product-gallery-item:first-child .product-gallery-primary-badge {
        display: inline-flex;
        align-items: center;
    }
    .product-gallery-actions {
        position: absolute;
        top: 8px;
        right: 8px;
        display: flex;
        gap: 6px;
        opacity: 0;
        transform: translateY(-4px);
        transition: opacity .15s ease, transform .15s ease;
    }
    .product-gallery-item:hover .product-gallery-actions,
    .product-gallery-item:focus-within .product-gallery-actions {
        opacity: 1;
        transform: translateY(0);
    }
    .product-gallery-drag {
        position: absolute;
        top: 9px;
        left: 9px;
        display: grid;
        place-items: center;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        color: #fff;
        background: rgba(0, 0, 0, .5);
        pointer-events: none;
    }
    .product-gallery-dropzone {
        display: grid;
        place-items: center;
        min-height: 148px;
        padding: 24px;
        border: 2px dashed #86a7d9 !important;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(13, 110, 253, .05), rgba(13, 202, 240, .04));
        cursor: pointer;
    }
    .product-gallery-dropzone:hover,
    .product-gallery-dropzone.dz-drag-hover {
        border-color: #0d6efd !important;
        background: rgba(13, 110, 253, .09);
    }
    .product-gallery-dropzone .dz-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        margin: 0;
        text-align: center;
    }
    .product-gallery-dropzone .dz-message > i {
        color: #0d6efd;
        font-size: 2.4rem;
    }
    .product-gallery-dropzone .dz-message span,
    .product-gallery-dropzone .dz-message small {
        color: var(--bs-secondary-color);
    }
    .product-gallery-compact-upload { display: none; align-items: center; justify-content: center; gap: 6px; }
    .product-gallery.has-images .product-gallery-dropzone { display: inline-grid; min-height: 44px; width: auto; padding: 8px 14px; }
    .product-gallery.has-images .product-gallery-empty-upload { display: none; }
    .product-gallery.has-images .product-gallery-compact-upload { display: flex; }
    .product-gallery-editor-stage {
        height: min(65vh, 620px);
        overflow: hidden;
        border-radius: 10px;
        background: #17191c;
    }
    .product-gallery-editor-stage img {
        display: block;
        max-width: 100%;
    }
    @media (max-width: 991.98px) {
        .product-gallery-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-template-rows: repeat(2, 135px);
            grid-auto-rows: 135px;
        }
        .product-gallery-item:first-child {
            grid-column: 1 / span 2;
            grid-row: 1 / span 2;
        }
    }
    @media (max-width: 575.98px) {
        .product-gallery-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-template-rows: 220px;
            grid-auto-rows: 120px;
        }
        .product-gallery-item:first-child {
            grid-column: 1 / -1;
            grid-row: auto;
        }
        .product-gallery-actions {
            opacity: 1;
            transform: none;
        }
    }
</style>
@endPushOnce

@pushOnce('js', 'product-gallery-js')
<script src="{{ asset('vendor/cropperjs/cropper.min.js') }}"></script>
@endPushOnce

@push('js')
<script>
    (function () {
        function initializeProductGallery() {
        const root = document.getElementById(@json($galleryId));
        if (!root || root.dataset.galleryInitialized === '1') {
            return;
        }
        root.dataset.galleryInitialized = '1';

        const grid = root.querySelector('[data-gallery-grid]');
        const dropzoneElement = root.querySelector('[data-gallery-dropzone]');
        const imageInput = root.querySelector('[data-image-input]');
        const orderInput = root.querySelector('[data-order-input]');
        const removedInput = root.querySelector('[data-removed-input]');
        const countElement = root.querySelector('[data-gallery-count]');
        const maxFiles = Number(root.dataset.maxFiles);
        const modalElement = document.getElementById(@json($galleryId . '_editor'));
        const editorImage = modalElement.querySelector('[data-editor-image]');
        const applyCropButton = modalElement.querySelector('[data-apply-crop]');
        const editorModal = new bootstrap.Modal(modalElement);
        let cropper = null;
        let editingItem = null;

        Dropzone.autoDiscover = false;

        const items = () => [...grid.querySelectorAll('[data-gallery-item]')];
        const removedIds = () => {
            try {
                return JSON.parse(removedInput.value || '[]').map(Number).filter(Boolean);
            } catch (error) {
                return [];
            }
        };

        function syncState() {
            const currentItems = items();
            const temporaryPaths = currentItems
                .map((item) => item.dataset.temporaryPath || '')
                .filter(Boolean);
            const order = currentItems.map((item) => item.dataset.key);

            imageInput.value = [...new Set(temporaryPaths)].join('|');
            orderInput.value = JSON.stringify(order);
            countElement.textContent = currentItems.length;
            root.classList.toggle('has-images', currentItems.length > 0);
            grid.classList.toggle('d-none', currentItems.length === 0);
            dropzoneElement.classList.toggle('d-none', currentItems.length >= maxFiles);
        }

        function markExistingAsRemoved(item) {
            const id = Number(item.dataset.existingId || 0);
            if (!id) {
                return;
            }

            removedInput.value = JSON.stringify([...new Set([...removedIds(), id])]);
        }

        function createGalleryItem(path, url) {
            const item = document.createElement('article');
            item.className = 'product-gallery-item';
            item.dataset.galleryItem = '';
            item.dataset.key = `temporary:${path}`;
            item.dataset.temporaryPath = path;

            const image = document.createElement('img');
            image.src = url;
            image.alt = 'Ảnh sản phẩm';
            image.dataset.galleryImage = '';

            const badge = document.createElement('span');
            badge.className = 'product-gallery-primary-badge';
            badge.dataset.primaryBadge = '';
            badge.innerHTML = '<i class="bi bi-star-fill me-1"></i>Ảnh đại diện';

            const actions = document.createElement('div');
            actions.className = 'product-gallery-actions';
            actions.innerHTML = `
                <button type="button" class="btn btn-light btn-sm" data-edit-image title="Chỉnh sửa ảnh"><i class="bi bi-crop"></i></button>
                <button type="button" class="btn btn-danger btn-sm" data-remove-image title="Xóa ảnh"><i class="bi bi-trash"></i></button>
            `;

            const drag = document.createElement('span');
            drag.className = 'product-gallery-drag';
            drag.innerHTML = '<i class="bi bi-grip-vertical"></i>';

            item.append(image, badge, actions, drag);
            grid.appendChild(item);
            syncState();
        }

        new Sortable(grid, {
            animation: 180,
            draggable: '[data-gallery-item]',
            filter: 'button',
            preventOnFilter: false,
            ghostClass: 'sortable-ghost',
            onEnd: syncState,
        });

        const dropzone = new Dropzone(dropzoneElement, {
            url: @json(route('admin.media.upload.temp')),
            clickable: dropzoneElement,
            paramName: 'file',
            maxFiles: maxFiles,
            maxFilesize: {{ (int) $maxSize }},
            acceptedFiles: @json($acceptedFiles),
            parallelUploads: 3,
            uploadMultiple: false,
            previewsContainer: false,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            dictMaxFilesExceeded: `Chỉ được chọn tối đa ${maxFiles} ảnh.`,
            dictInvalidFileType: 'Định dạng ảnh không được hỗ trợ.',
            dictFileTooBig: 'Ảnh vượt quá dung lượng cho phép.',
            init: function () {
                this.on('success', function (file, response) {
                    if (!response.success) {
                        Swal.fire('Không thể tải ảnh', response.message || 'Vui lòng thử lại.', 'error');
                        return;
                    }

                    if (items().length >= maxFiles) {
                        Swal.fire('Đã đủ ảnh', `Sản phẩm chỉ được có tối đa ${maxFiles} ảnh.`, 'warning');
                        return;
                    }

                    createGalleryItem(response.path, response.url);
                });

                this.on('error', function (file, message) {
                    const text = typeof message === 'string' ? message : (message.message || 'Không thể tải ảnh.');
                    Swal.fire('Lỗi tải ảnh', text, 'error');
                });

                this.on('queuecomplete', function () {
                    this.removeAllFiles(true);
                    syncState();
                });
            },
        });

        grid.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-remove-image]');
            const editButton = event.target.closest('[data-edit-image]');
            const item = event.target.closest('[data-gallery-item]');

            if (!item) {
                return;
            }

            if (removeButton) {
                markExistingAsRemoved(item);
                item.remove();
                dropzone.options.maxFiles = maxFiles;
                syncState();
                return;
            }

            if (editButton) {
                editingItem = item;
                editorImage.src = item.querySelector('[data-gallery-image]').src;
                editorModal.show();
            }
        });

        modalElement.addEventListener('shown.bs.modal', function () {
            cropper?.destroy();
            cropper = new Cropper(editorImage, {
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
                background: false,
                checkOrientation: true,
            });
        });

        modalElement.addEventListener('hidden.bs.modal', function () {
            cropper?.destroy();
            cropper = null;
            editingItem = null;
            editorImage.src = '';
        });

        modalElement.querySelectorAll('[data-crop-action]').forEach((button) => {
            button.addEventListener('click', function () {
                if (!cropper) return;

                const actions = {
                    'rotate-left': () => cropper.rotate(-90),
                    'rotate-right': () => cropper.rotate(90),
                    'zoom-in': () => cropper.zoom(.1),
                    'zoom-out': () => cropper.zoom(-.1),
                    'reset': () => cropper.reset(),
                };
                actions[button.dataset.cropAction]?.();
            });
        });

        applyCropButton.addEventListener('click', function () {
            if (!cropper || !editingItem) {
                return;
            }

            applyCropButton.disabled = true;
            applyCropButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang xử lý';

            cropper.getCroppedCanvas({
                maxWidth: 2400,
                maxHeight: 2400,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            }).toBlob(async function (blob) {
                try {
                    if (!blob) throw new Error('Không thể tạo ảnh sau chỉnh sửa.');

                    const formData = new FormData();
                    formData.append('file', blob, 'product-edited.jpg');
                    formData.append('convert_to_webp', '1');

                    const response = await fetch(@json(route('admin.media.upload.temp')), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Không thể lưu ảnh đã chỉnh sửa.');
                    }

                    markExistingAsRemoved(editingItem);
                    delete editingItem.dataset.existingId;
                    editingItem.dataset.temporaryPath = result.path;
                    editingItem.dataset.key = `temporary:${result.path}`;
                    editingItem.querySelector('[data-gallery-image]').src = result.url;
                    syncState();
                    editorModal.hide();
                } catch (error) {
                    Swal.fire('Lỗi chỉnh sửa ảnh', error.message, 'error');
                } finally {
                    applyCropButton.disabled = false;
                    applyCropButton.innerHTML = '<i class="bi bi-check-lg me-1"></i>Áp dụng';
                }
            }, 'image/jpeg', .92);
        });

        syncState();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeProductGallery, { once: true });
        } else {
            initializeProductGallery();
        }
    })();
</script>
@endpush
