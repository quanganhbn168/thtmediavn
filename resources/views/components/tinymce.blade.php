@props([
    'name',
    'label' => null,
    'value' => null,
    'rows' => 10,
    'required' => false,
    'translatable' => false,
    'id' => null,
])

@php
    $inputId = $id ?? 'tinymce_' . str_replace(['[', ']'], ['_', ''], $name) . '_' . Str::random(4);
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
                    <textarea 
                        name="{{ $name }}[{{ $langCode }}]" 
                        id="{{ $tabId }}_field"
                        rows="{{ $rows }}"
                        class="form-control tinymce-editor {{ $hasError ? 'is-invalid' : '' }}" 
                        {{ $attributes }}
                    >{{ old($name . '.' . $langCode, $translations[$langCode] ?? '') }}</textarea>
                    @error($name . '.' . $langCode)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach
        </div>
    @else
        <textarea 
            name="{{ $name }}" 
            id="{{ $inputId }}"
            rows="{{ $rows }}"
            class="form-control tinymce-editor @error($name) is-invalid @enderror" 
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        >{{ old($name, $value) }}</textarea>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    @endif
</div>

@pushOnce('css')
<style>
    #mediaManagerModal {
        z-index: 99999 !important;
    }
    .media-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #e9ecef;
        background: #fff;
    }
    .media-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important;
        border-color: #0d6efd;
    }
    .media-thumb-wrapper {
        position: relative;
        background: #f8f9fa;
        height: 120px;
        overflow: hidden;
        border-top-left-radius: .375rem;
        border-top-right-radius: .375rem;
    }
    .media-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .media-card-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(13, 110, 253, 0.85);
        opacity: 0;
        transition: opacity 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .media-card:hover .media-card-overlay {
        opacity: 1;
    }
    .media-grid {
        max-height: 480px;
        overflow-y: auto;
        padding-top: 5px;
        padding-bottom: 5px;
    }
</style>
@endpushOnce

@pushOnce('js')
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        tinymce.init({
            selector: '.tinymce-editor',
            license_key: 'gpl',
            height: 400,
            menubar: true,
            plugins: 'lists link image media table code wordcount advlist autolink charmap preview searchreplace visualblocks',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table link image media code | preview removeformat',
            branding: false,
            promotion: false,
            relative_urls: false,
            remove_script_host: false,
            image_caption: true,
            image_title: true,
            content_style: 'body { font-family:Source Sans 3,Helvetica,Arial,sans-serif; font-size:15px }',
            
            // Tải tệp tin phương tiện cục bộ qua AJAX kéo thả
            images_upload_handler: function (blobInfo, progress) {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', "{{ route('admin.media.upload.editor') }}");

                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    xhr.setRequestHeader('X-CSRF-TOKEN', token);

                    xhr.upload.onprogress = (e) => {
                        progress(e.loaded / e.total * 100);
                    };

                    xhr.onload = () => {
                        if (xhr.status === 403) {
                            reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                            return;
                        }

                        if (xhr.status < 200 || xhr.status >= 300) {
                            reject('HTTP Error: ' + xhr.status);
                            return;
                        }

                        const json = JSON.parse(xhr.responseText);

                        if (!json || typeof json.location !== 'string') {
                            reject('Invalid JSON: ' + xhr.responseText);
                            return;
                        }

                        resolve(json.location);
                    };

                    xhr.onerror = () => {
                        reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
                    };

                    const formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());

                    xhr.send(formData);
                });
            },

            // Tích hợp Trình Quản Lý Thư Viện Media khi click icon File Picker
            file_picker_types: 'image',
            file_picker_callback: function (callback, value, meta) {
                const modalEl = document.getElementById('mediaManagerModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();

                window.activeTinyMceCallback = callback;
                loadMediaLibrary();
            },

            setup: function (editor) {
                editor.on('change keyup', function () {
                    editor.save();
                });

                const submitAdminForm = function (form, saveAndCreate = false) {
                    const saveAndCreateButton = form.querySelector(
                        '[type="submit"][name="submit_action"][value="save_and_create"]'
                    );
                    const regularSaveButton = Array.from(
                        form.querySelectorAll('button[type="submit"], input[type="submit"]')
                    ).find((button) => button !== saveAndCreateButton);
                    const submitButton = saveAndCreate && saveAndCreateButton
                        ? saveAndCreateButton
                        : (regularSaveButton || saveAndCreateButton);

                    if (submitButton) {
                        form.requestSubmit(submitButton);
                    } else {
                        form.requestSubmit();
                    }
                };

                // Phím tắt Ctrl + S / Cmd + S khi đang gõ trong TinyMCE editor
                editor.addShortcut('meta+s', 'Save Form', function () {
                    editor.save();
                    const form = editor.getElement().closest('form');
                    if (form) submitAdminForm(form);
                });

                // Phím tắt Ctrl + Shift + S / Cmd + Shift + S khi đang gõ trong TinyMCE editor
                editor.addShortcut('meta+shift+s', 'Save and Create New', function () {
                    editor.save();
                    const form = editor.getElement().closest('form');
                    if (form) submitAdminForm(form, true);
                });
            }
        });

        // Hỗ trợ resize trong Tabs
        const tabElList = document.querySelectorAll('button[data-bs-toggle="tab"]')
        tabElList.forEach(tabEl => {
            tabEl.addEventListener('shown.bs.tab', event => {
                const targetId = event.target.getAttribute('data-bs-target');
                const textareas = document.querySelectorAll(targetId + ' textarea.tinymce-editor');
                textareas.forEach(textarea => {
                    const id = textarea.id;
                    const editor = tinymce.get(id);
                    if (editor) {
                        editor.theme.resizeTo(null, editor.getContainer().offsetHeight);
                    }
                });
            });
        });

        // logic cho Trình Quản Lý Media (Modal)
        const modalEl = document.getElementById('mediaManagerModal');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', function () {
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => {
                        backdrop.style.zIndex = '99998';
                    });
                }, 10);
            });
        }
        let allMediaData = {
            media_library: [],
            editor: [],
            settings: []
        };

        window.loadMediaLibrary = function() {
            const grids = document.querySelectorAll('.media-grid');
            grids.forEach(grid => {
                grid.innerHTML = '<div class="col-12 py-5 text-center"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2 mb-0">Đang tải danh sách ảnh...</p></div>';
            });

            fetch("{{ route('admin.media.list') }}")
                .then(response => response.json())
                .then(data => {
                    allMediaData = data;
                    renderAllGrids();
                })
                .catch(error => {
                    grids.forEach(grid => {
                        grid.innerHTML = '<div class="col-12 py-5 text-center text-danger"><i class="bi bi-exclamation-triangle-fill" style="font-size: 2rem;"></i><p class="mt-2 mb-0">Lỗi tải danh sách ảnh. Vui lòng thử lại.</p></div>';
                    });
                });
        };

        function renderAllGrids(searchQuery = '') {
            renderTabGrid('media_library', 'grid-media-library', searchQuery);
            renderTabGrid('editor', 'grid-editor', searchQuery);
            renderTabGrid('settings', 'grid-settings', searchQuery);
        }

        function renderTabGrid(key, containerId, searchQuery = '') {
            const container = document.getElementById(containerId);
            if (!container) return;

            const items = allMediaData[key] || [];
            const filteredItems = items.filter(item => {
                return item.name.toLowerCase().includes(searchQuery.toLowerCase());
            });

            if (filteredItems.length === 0) {
                container.innerHTML = '<div class="col-12 py-5 text-center"><i class="bi bi-image" style="font-size: 2.5rem; color: #adb5bd;"></i><p class="text-muted mt-2 mb-0">Không tìm thấy hình ảnh nào phù hợp.</p></div>';
                return;
            }

            let html = '';
            filteredItems.forEach(item => {
                html += `
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="card h-100 media-card shadow-sm border-0 rounded-3">
                            <div class="media-thumb-wrapper">
                                <img src="${item.url}" class="media-thumb" alt="${item.name}">
                                <div class="media-card-overlay">
                                    <button type="button" class="btn btn-sm btn-light font-weight-bold px-3 py-1.5 shadow-sm rounded-pill" onclick="selectMediaUrl('${item.url}')">
                                        Chọn ảnh
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-2 d-flex flex-column justify-content-between" style="min-height: 60px;">
                                <div class="text-truncate font-weight-bold text-dark text-xs" title="${item.name}" style="font-size: 0.8rem;">
                                    ${item.name}
                                </div>
                                <div class="d-flex justify-content-between text-secondary mt-1" style="font-size: 0.7rem;">
                                    <span>${item.size}</span>
                                    <span>${item.created_at.split(' ')[0]}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        window.selectMediaUrl = function(url) {
            if (window.activeTinyMceCallback) {
                window.activeTinyMceCallback(url, { alt: '' });
                const modalEl = document.getElementById('mediaManagerModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        };

        // Lắng nghe tìm kiếm
        const searchInput = document.getElementById('mediaSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                renderAllGrids(e.target.value);
            });
        }

        // Khởi tạo Dropzone cho Media Manager Modal
        if (document.getElementById('mediaManagerDropzone')) {
            new Dropzone('#mediaManagerDropzone', {
                url: "{{ route('admin.media.upload.editor') }}",
                maxFiles: 1,
                maxFilesize: 10,
                acceptedFiles: 'image/*',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                init: function() {
                    this.on("sending", function(file, xhr, formData) {
                        const message = document.querySelector('#mediaManagerDropzone .dz-message');
                        if (message) {
                            message.innerHTML = '<div class="spinner-border text-success" role="status"></div><h5 class="mt-3">Đang tải lên và tối ưu ảnh...</h5>';
                        }
                    });

                    this.on("success", function(file, response) {
                        if (response.location) {
                            // Chèn ngay vào TinyMCE
                            selectMediaUrl(response.location);
                            
                            // Reset Dropzone
                            this.removeAllFiles();
                            resetDropzoneModalUI();
                        } else {
                            Swal.fire('Lỗi', 'Có lỗi xảy ra khi tải ảnh lên.', 'error');
                            this.removeAllFiles();
                            resetDropzoneModalUI();
                        }
                    });

                    this.on("error", function(file, message) {
                        let text = typeof message === 'string' ? message : (message.error || 'Lỗi tải ảnh.');
                        Swal.fire('Thất bại', text, 'error');
                        this.removeAllFiles();
                        resetDropzoneModalUI();
                    });
                }
            });
        }

        function resetDropzoneModalUI() {
            const el = document.getElementById('mediaManagerDropzone');
            if (el) {
                el.innerHTML = `
                    <div class="dz-message py-4">
                        <i class="bi bi-cloud-arrow-up text-success" style="font-size: 3.5rem;"></i>
                        <h5 class="mt-3 font-weight-bold">Kéo thả ảnh vào đây hoặc bấm để chọn tệp tin</h5>
                        <p class="text-muted text-sm mb-0">Ảnh tải lên tại đây sẽ được tối ưu tự động và chuyển vào Thư mục Editor</p>
                    </div>
                `;
            }
        }
    });
</script>
@endpushOnce

<!-- Modal Trình Quản Lý Ảnh -->
<div class="modal fade" id="mediaManagerModal" tabindex="-1" aria-labelledby="mediaManagerModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold text-primary" id="mediaManagerModalLabel">
                    <i class="bi bi-images me-2"></i>Thư viện phương tiện
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 border-bottom pb-3">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-pills" id="mediaManagerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-3 font-weight-bold" id="tab-media-library" data-bs-toggle="tab" data-bs-target="#panel-media-library" type="button" role="tab">
                                Thư viện Media
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 font-weight-bold" id="tab-editor" data-bs-toggle="tab" data-bs-target="#panel-editor" type="button" role="tab">
                                Ảnh Editor (TinyMCE)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 font-weight-bold" id="tab-settings" data-bs-toggle="tab" data-bs-target="#panel-settings" type="button" role="tab">
                                Ảnh Hệ thống (Settings)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 font-weight-bold btn-success text-white" id="tab-upload" data-bs-toggle="tab" data-bs-target="#panel-upload" type="button" role="tab">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Tải ảnh mới
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Search Input -->
                    <div class="position-relative" style="width: 250px;">
                        <input type="text" id="mediaSearchInput" class="form-control form-control-sm ps-5 rounded-pill" placeholder="Tìm kiếm tên file...">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    </div>
                </div>

                <div class="tab-content" id="mediaManagerTabContent">
                    <!-- Tab 1: Spatie Media Library -->
                    <div class="tab-pane fade show active" id="panel-media-library" role="tabpanel">
                        <div class="row g-3 media-grid" id="grid-media-library" style="max-height: 450px; overflow-y: auto;">
                            <!-- Grid ảnh -->
                        </div>
                    </div>

                    <!-- Tab 2: Editor -->
                    <div class="tab-pane fade" id="panel-editor" role="tabpanel">
                        <div class="row g-3 media-grid" id="grid-editor" style="max-height: 450px; overflow-y: auto;">
                            <!-- Grid ảnh -->
                        </div>
                    </div>

                    <!-- Tab 3: Settings -->
                    <div class="tab-pane fade" id="panel-settings" role="tabpanel">
                        <div class="row g-3 media-grid" id="grid-settings" style="max-height: 450px; overflow-y: auto;">
                            <!-- Grid ảnh -->
                        </div>
                    </div>

                    <!-- Tab 4: Tải ảnh mới -->
                    <div class="tab-pane fade" id="panel-upload" role="tabpanel">
                        <div id="mediaManagerDropzone" class="dropzone border-dashed border-2 rounded p-5 text-center cursor-pointer bg-light d-flex flex-column align-items-center justify-content-center" style="border-color: #198754 !important; min-height: 250px;">
                            <div class="dz-message py-4">
                                <i class="bi bi-cloud-arrow-up text-success" style="font-size: 3.5rem;"></i>
                                <h5 class="mt-3 font-weight-bold">Kéo thả ảnh vào đây hoặc bấm để chọn tệp tin</h5>
                                <p class="text-muted text-sm mb-0">Ảnh tải lên tại đây sẽ được tối ưu tự động và chuyển vào Thư mục Editor</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
