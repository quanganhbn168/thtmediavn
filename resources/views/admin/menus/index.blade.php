@extends('layouts.admin')

@section('title', 'Quản lý Menu hệ thống')
@section('page-title', 'Quản lý Menu')
@section('breadcrumbs')
<ol class="breadcrumb float-sm-end">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Quản lý Menu</li>
</ol>
@endsection

@section('content')
<div class="row">
    <!-- CỘT TRÁI: Danh sách các Menu điều hướng dọc -->
    <div class="col-md-3 mb-4">
        <!-- Nút thêm mới đặt phía trên card -->
        <div class="d-flex justify-content-end gap-2 mb-3 d-print-none">
            <button type="button" class="btn btn-success font-weight-bold shadow-sm w-100" data-bs-toggle="modal" data-bs-target="#createMenuModal">
                <i class="bi bi-plus-circle me-1"></i> Tạo Menu mới
            </button>
        </div>

        <x-card type="primary" :outline="true" title="Danh sách Menu" bodyClass="p-0" class="mb-4" :collapsible="true">
            <div class="nav flex-column nav-pills" id="menus-list-tab" role="tablist" aria-orientation="vertical">
                @forelse($menus as $m)
                    <a href="{{ route('admin.menus.index', ['id' => $m->id]) }}" 
                       class="nav-link text-start py-3 px-4 border-bottom rounded-0 d-flex justify-content-between align-items-center {{ $activeMenu && $activeMenu->id == $m->id ? 'active' : '' }}">
                        <div>
                            <i class="bi bi-list-nested me-2"></i>
                            <span class="fw-bold">{{ $m->getTranslation('name', 'vi') }}</span>
                            @foreach($menuUsage[$m->id] ?? [] as $usage)
                                <span class="badge bg-info-subtle text-info-emphasis ms-1 mt-1">{{ $usage }}</span>
                            @endforeach
                        </div>
                        @if($m->location)
                            <span class="badge bg-secondary text-xs">{{ $m->location }}</span>
                        @endif
                    </a>
                @empty
                    <span class="text-muted p-4 text-center d-block">Chưa có menu nào</span>
                @endforelse
            </div>
        </x-card>
    </div>

    <!-- CỘT PHẢI: Nội dung kéo thả thiết kế Menu đang chọn -->
    <div class="col-md-9 mb-4">
        @if(!$activeMenu)
            <div class="card card-outline card-primary p-5 text-center">
                <i class="bi bi-menu-button-wide text-muted mb-3" style="font-size: 3rem;"></i>
                <h4 class="text-secondary font-weight-bold">Chưa chọn menu thiết kế</h4>
                <p class="text-muted">Vui lòng chọn một menu ở cột trái hoặc bấm "Tạo Menu mới" để bắt đầu thiết kế cấu trúc điều hướng.</p>
            </div>
        @else
            <!-- 1. Form thông tin chung của Menu -->
            <x-card type="primary" :outline="true" title="Cấu hình Menu: {{ $activeMenu->getTranslation('name', 'vi') }}" class="mb-4" :collapsible="true">
                <x-slot name="tools">
                    <button type="button" class="btn btn-danger btn-sm delete-menu-btn" data-id="{{ $activeMenu->id }}" data-name="{{ $activeMenu->getTranslation('name', 'vi') }}">
                        <i class="bi bi-trash me-1"></i> Xóa Menu
                    </button>
                </x-slot>

                <form id="admin-save-form" action="{{ route('admin.menus.update', $activeMenu->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row align-items-end">
                        <div class="col-md-5">
                            <x-input 
                                name="name" 
                                label="Tên menu" 
                                :value="$activeMenu" 
                                :translatable="true" 
                                :required="true"
                            />
                        </div>
                        <div class="col-md-4">
                            <x-select 
                                name="location" 
                                label="Vị trí hiển thị" 
                                :options="['header' => 'Header', 'footer' => 'Footer']"
                                :selected="$activeMenu->location"
                                placeholder="Chưa phân vị trí"
                            />
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-center justify-content-between">
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="active_menu_is_active" value="1" {{ $activeMenu->is_active ? 'checked' : '' }}>
                                <label class="form-check-label font-weight-bold" for="active_menu_is_active">Hoạt động</label>
                            </div>
                            <button type="submit" class="btn btn-primary px-3 font-weight-bold mt-2">
                                <i class="bi bi-save me-1"></i> Lưu
                            </button>
                        </div>
                    </div>
                </form>

                @if(!empty($menuUsage[$activeMenu->id]))
                    <div class="alert alert-info py-2 mb-0">
                        <i class="bi bi-broadcast-pin me-1"></i>
                        Đang được gán cho: <strong>{{ implode(' · ', $menuUsage[$activeMenu->id]) }}</strong>.
                        <a href="{{ route('admin.settings.menu') }}" class="alert-link">Đổi vị trí hiển thị</a>
                    </div>
                @endif
            </x-card>

            <!-- 2. Khu vực thiết kế liên kết kéo thả -->
            <div class="row">
                <!-- Bên trái: Các nguồn liên kết có sẵn -->
                <div class="col-md-5 mb-4">
                    <h5 class="font-weight-bold text-secondary mb-3"><i class="bi bi-plus-square me-1"></i>Thêm liên kết vào Menu</h5>
                    
                    <div class="accordion shadow-sm" id="menuSourcesAccordion">
                        <!-- Accordion 1: Liên kết tự chọn (Custom Link) -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingCustomLink">
                                <button class="accordion-button font-weight-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCustomLink">
                                    <i class="bi bi-link-45deg me-2 text-success"></i> Liên kết tự chọn (Custom URL)
                                </button>
                            </h2>
                            <div id="collapseCustomLink" class="accordion-collapse collapse show" data-bs-parent="#menuSourcesAccordion">
                                <div class="accordion-body">
                                    <form action="{{ route('admin.menus.items.add', $activeMenu->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="type" value="custom">
                                        
                                        <x-input name="custom_title" label="Tiêu đề hiển thị" :translatable="true" :required="true" id="custom_title_input" />
                                        <x-input name="custom_url" label="Đường dẫn (URL)" placeholder="Ví dụ: /lien-he hoặc https://..." :required="true" />
                                        
                                        <div class="row">
                                            <div class="col-6">
                                                <x-input name="custom_icon" label="Biểu tượng (Icon)" placeholder="Ví dụ: bi-house" />
                                            </div>
                                            <div class="col-6">
                                                <x-select 
                                                    name="custom_target" 
                                                    label="Mở tab mới" 
                                                    :options="['_self' => 'Không', '_blank' => 'Có']" 
                                                    selected="_self" 
                                                />
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-sm btn-primary w-100 font-weight-bold mt-2">
                                            <i class="bi bi-plus-lg me-1"></i> Thêm vào Menu
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion 2: Trang tĩnh (Pages) -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPages">
                                <button class="accordion-button collapsed font-weight-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePages">
                                    <i class="bi bi-file-earmark-richtext me-2 text-info"></i> Trang tĩnh
                                </button>
                            </h2>
                        <div id="collapsePages" class="accordion-collapse collapse" data-bs-parent="#menuSourcesAccordion">
                                <div class="accordion-body" style="max-height: 250px; overflow-y: auto;">
                                    <form action="{{ route('admin.menus.items.add', $activeMenu->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="type" value="pages">

                                        @if($systemPages->isNotEmpty())
                                            <div class="mb-2">
                                                <div class="small text-muted fw-semibold">Trang hệ thống</div>
                                            </div>

                                            @foreach($systemPages as $systemPage)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $systemPage['id'] }}" id="chk_system_page_{{ $systemPage['id'] }}">
                                                    <label class="form-check-label cursor-pointer" for="chk_system_page_{{ $systemPage['id'] }}">
                                                        {{ $systemPage['title']['vi'] ?? $systemPage['title'] }}
                                                    </label>
                                                </div>
                                            @endforeach

                                            @if($pages->isNotEmpty())
                                                <hr class="my-2">
                                            @endif
                                        @endif

                                        <div class="small text-muted fw-semibold">Trang tĩnh</div>
                                        
                                        @forelse($pages as $p)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $p->id }}" id="chk_page_{{ $p->id }}">
                                                <label class="form-check-label cursor-pointer" for="chk_page_{{ $p->id }}">
                                                    {{ $p->getTranslation('name', 'vi') }}
                                                </label>
                                            </div>
                                        @empty
                                            <span class="text-muted d-block small">Không có trang tĩnh nào.</span>
                                        @endforelse

                                        @if($systemPages->isNotEmpty() || $pages->isNotEmpty())
                                            <button type="submit" class="btn btn-sm btn-primary w-100 font-weight-bold mt-3">
                                                <i class="bi bi-plus-lg me-1"></i> Thêm vào Menu
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion 3: Danh mục sản phẩm -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingProductCategories">
                                <button class="accordion-button collapsed font-weight-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProductCategories">
                                    <i class="bi bi-grid me-2 text-primary"></i> Danh mục sản phẩm
                                </button>
                            </h2>
                            <div id="collapseProductCategories" class="accordion-collapse collapse" data-bs-parent="#menuSourcesAccordion">
                                <div class="accordion-body" style="max-height: 300px; overflow-y: auto;">
                                    <form action="{{ route('admin.menus.items.add', $activeMenu->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="type" value="product_categories">

                                        @if($productCategories->isNotEmpty())
                                            <x-admin.category-tree-select
                                                id="menu_product_category_ids"
                                                name="ids"
                                                label="Chọn danh mục"
                                                :categories="$productCategories"
                                                :selected="old('ids', [])"
                                                :multiple="true"
                                            />
                                        @else
                                            <span class="text-muted d-block small">Chưa có danh mục sản phẩm.</span>
                                        @endif

                                        @if($productCategories->isNotEmpty())
                                            <button type="submit" class="btn btn-sm btn-primary w-100 font-weight-bold mt-3">
                                                <i class="bi bi-plus-lg me-1"></i> Thêm vào Menu
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion 4: Bài viết / Cẩm nang -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPosts">
                                <button class="accordion-button collapsed font-weight-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePosts">
                                    <i class="bi bi-newspaper me-2 text-danger"></i> Bài viết / Cẩm nang
                                </button>
                            </h2>
                            <div id="collapsePosts" class="accordion-collapse collapse" data-bs-parent="#menuSourcesAccordion">
                                <div class="accordion-body" style="max-height: 250px; overflow-y: auto;">
                                    <form action="{{ route('admin.menus.items.add', $activeMenu->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="type" value="posts">
                                        
                                        @forelse($posts as $po)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $po->id }}" id="chk_post_{{ $po->id }}">
                                                <label class="form-check-label cursor-pointer" for="chk_post_{{ $po->id }}">
                                                    {{ $po->getTranslation('name', 'vi') }}
                                                </label>
                                            </div>
                                        @empty
                                            <span class="text-muted d-block small">Không có bài viết nào.</span>
                                        @endforelse

                                        @if($posts->isNotEmpty())
                                            <button type="submit" class="btn btn-sm btn-primary w-100 font-weight-bold mt-3">
                                                <i class="bi bi-plus-lg me-1"></i> Thêm vào Menu
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Bên phải: Cấu trúc menu kéo thả Nestable2 -->
                <div class="col-md-7 mb-4">
                    <x-card type="success" :outline="true" title="Cấu trúc cây Menu" bodyClass="p-3 bg-white" style="min-height: 400px;" :collapsible="true" :maximizable="true">
                        <x-slot name="tools">
                            <button type="button" class="btn btn-sm btn-success font-weight-bold shadow-sm" id="save-menu-order-btn">
                                <i class="bi bi-save me-1"></i> Lưu cấu trúc Menu
                            </button>
                        </x-slot>

                        <div class="alert alert-light border py-2 small mb-3">
                            <i class="bi bi-diagram-3 me-1"></i>
                            Kéo thả để sắp xếp tối đa <strong>3 cấp</strong>: cấp 1 là nhóm/menu chính, cấp 2 là menu con, cấp 3 là liên kết chi tiết. Máy chủ sẽ kiểm tra lại quy tắc này khi lưu.
                        </div>

                        <div class="dd" id="nestable">
                            @if(count($menuItems) > 0)
                                @include('admin.menus.menu_item_row', ['items' => $menuItems])
                            @else
                                <div class="dd-empty">Kéo thả các liên kết từ bên trái vào đây để xây dựng menu...</div>
                            @endif
                        </div>
                    </x-card>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- ==========================================
     MODAL: TẠO MENU CHA MỚI
     ========================================== -->
<x-modal 
    id="createMenuModal" 
    title="Tạo Menu cha mới" 
    size="md" 
    :formAction="route('admin.menus.store')"
    formId="create-menu-form"
>
    <x-input name="name" label="Tên menu" :translatable="true" :required="true" id="modal_menu_name" />
    <x-select name="location" label="Vị trí hiển thị" :options="['header' => 'Header', 'footer' => 'Footer']" placeholder="Chưa phân vị trí" />
    <div class="form-check form-switch mt-2 mb-3">
        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="modal_menu_is_active" value="1" checked>
        <label class="form-check-label font-weight-bold" for="modal_menu_is_active">Hoạt động ngay lập tức</label>
    </div>
</x-modal>

<!-- ==========================================
     MODAL: CHỈNH SỬA MENU ITEM CHI TIẾT
     ========================================== -->
<x-modal 
    id="editMenuItemModal" 
    title="Cấu hình liên kết chi tiết" 
    size="md" 
    formId="edit-menu-item-form"
    formMethod="PUT"
    :hideSubmit="true"
>
    <!-- Hidden input ID để AJAX sử dụng -->
    <input type="hidden" id="edit_item_id">

    <x-input name="title" label="Tiêu đề hiển thị" :translatable="true" :required="true" id="edit_item_title" />
    <x-input name="url" label="Đường dẫn liên kết (URL)" :required="true" id="edit_item_url" />
    
    <div class="row">
        <div class="col-6">
            <x-input name="icon" label="Biểu tượng (Icon)" placeholder="Ví dụ: bi-house" id="edit_item_icon" />
        </div>
        <div class="col-6">
            <x-select 
                name="target" 
                label="Mở tab mới" 
                :options="['_self' => 'Không (_self)', '_blank' => 'Có (_blank)']" 
                id="edit_item_target"
            />
        </div>
    </div>

    <div class="form-check form-switch mt-2 mb-3">
        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="edit_item_is_active" value="1">
        <label class="form-check-label font-weight-bold" for="edit_item_is_active">Hoạt động hiển thị</label>
    </div>

    <div class="d-grid mt-4 pt-3 border-top">
        <button type="button" class="btn btn-primary py-2 font-weight-bold shadow-sm" id="save-item-details-btn">
            <i class="bi bi-save me-1"></i> Cập nhật liên kết
        </button>
    </div>
</x-modal>

<!-- Form ẩn phục vụ xóa Menu cha -->
<form action="" method="POST" id="delete-menu-form">
    @csrf
    @method('DELETE')
</form>

@push('css')
<link rel="stylesheet" href="{{ asset('vendor/nestable/nestable.min.css') }}">
<style>
    /* CSS Premium cho Drag & Drop Nestable2 */
    .dd {
        position: relative;
        display: block;
        margin: 0;
        padding: 0;
        max-width: 100%;
        list-style: none;
    }
    .dd-list {
        display: block;
        position: relative;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .dd-list .dd-list {
        padding-left: 30px;
    }
    .dd-item, .dd-empty, .dd-placeholder {
        display: block;
        position: relative;
        margin: 0;
        padding: 0;
        min-height: 20px;
    }
    .dd-handle {
        display: block;
        height: 48px;
        margin: 8px 0;
        padding: 12px 15px;
        color: #333;
        text-decoration: none;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        border-radius: 6px;
        box-sizing: border-box;
        cursor: move;
        transition: all 0.15s ease-in-out;
    }
    .dd-handle:hover {
        background: #e9ecef;
        border-color: #ced4da;
    }
    .dd-item > button {
        display: block;
        position: relative;
        cursor: pointer;
        float: left;
        width: 25px;
        height: 30px;
        margin: 9px 0 0 5px;
        padding: 0;
        text-indent: 100%;
        white-space: nowrap;
        overflow: hidden;
        border: 0;
        background: transparent;
        font-size: 14px;
        line-height: 1;
        text-align: center;
        font-weight: bold;
        z-index: 11;
    }
    .dd-item > button:before {
        content: '+';
        display: block;
        position: absolute;
        width: 100%;
        text-align: center;
        text-indent: 0;
        color: #0d6efd;
        font-weight: bold;
    }
    .dd-item > button[data-action="collapse"]:before {
        content: '-';
        color: #dc3545;
    }
    .dd-placeholder, .dd-empty {
        margin: 8px 0;
        padding: 0;
        min-height: 48px;
        background: #f1f3f9;
        border: 2px dashed #b5bac9;
        box-sizing: border-box;
        border-radius: 6px;
    }
    .dd-empty {
        border: 2px dashed #ced4da;
        min-height: 120px;
        background-color: #f8f9fa;
        text-align: center;
        padding-top: 45px;
        color: #6c757d;
    }
    .dd-dragel {
        position: absolute;
        pointer-events: none;
        z-index: 9999;
    }
    .dd-dragel > .dd-item > .dd-handle {
        margin-top: 0;
        border-color: #0d6efd;
        background: #e7f1ff;
    }
    .dd-actions {
        position: absolute;
        right: 12px;
        top: 10px;
        z-index: 10;
    }
    .dd-actions .btn-xs {
        padding: 0.15rem 0.35rem;
        font-size: 0.75rem;
    }
    .accordion-button:not(.collapsed) {
        background-color: #e7f1ff;
        color: #0d6efd;
    }
</style>
@endpush

@push('js')
<script src="{{ asset('vendor/jquery-4.0.0.min.js') }}"></script>
<script src="{{ asset('vendor/nestable/nestable.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        @if($activeMenu)
            // 1. Khởi tạo kéo thả Nestable2 cho Menu
            $('#nestable').nestable({
                maxDepth: 3 // Giới hạn menu sâu tối đa 3 cấp
            });

            // 2. Click Lưu cấu trúc cây Menu
            $('#save-menu-order-btn').on('click', function () {
                const button = $(this);
                const serializedData = $('#nestable').nestable('serialize');
                
                if (serializedData.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Thông báo',
                        text: 'Cấu trúc menu trống, không có gì để lưu!'
                    });
                    return;
                }

                button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Đang lưu...');

                $.ajax({
                    url: "{{ route('admin.menus.items.order', $activeMenu->id) }}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        structure: serializedData
                    },
                    success: function (response) {
                        button.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Lưu cấu trúc Menu');
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công',
                                text: response.message
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: response.message || 'Có lỗi xảy ra.'
                            });
                        }
                    },
                    error: function (xhr) {
                        button.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Lưu cấu trúc Menu');
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi kết nối',
                            text: xhr.responseJSON?.message || 'Không thể gửi dữ liệu lên máy chủ.'
                        });
                    }
                });
            });

            // 3. Xử lý mở Modal sửa nhanh liên kết và đổ dữ liệu
            const editModal = new bootstrap.Modal(document.getElementById('editMenuItemModal'));
            
            $(document).on('click', '.edit-item-btn', function () {
                const btn = $(this);
                const id = btn.data('id');
                const titleVi = btn.data('title-vi');
                const titleEn = btn.data('title-en');
                const url = btn.data('url');
                const target = btn.data('target');
                const icon = btn.data('icon');
                const active = btn.data('active');

                // Điền dữ liệu vào form
                $('#edit_item_id').val(id);
                $('#input_edit_item_title_vi_field').val(titleVi);
                $('#input_edit_item_title_en_field').val(titleEn);
                $('#input_edit_item_url').val(url);
                $('#input_edit_item_icon').val(icon);
                $('#edit_item_target').val(target);
                $('#edit_item_is_active').prop('checked', active == 1);

                editModal.show();
            });

            // 4. Lưu cập nhật chi tiết liên kết (Modal submit)
            $('#save-item-details-btn').on('click', function () {
                const btn = $(this);
                const id = $('#edit_item_id').val();
                
                const titleVi = $('#input_edit_item_title_vi_field').val();
                const titleEn = $('#input_edit_item_title_en_field').val();
                const url = $('#input_edit_item_url').val();
                const icon = $('#input_edit_item_icon').val();
                const target = $('#edit_item_target').val();
                const isActive = $('#edit_item_is_active').is(':checked');

                if (!titleVi || !url) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Thông báo',
                        text: 'Tiêu đề tiếng Việt và Đường dẫn không được trống.'
                    });
                    return;
                }

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Đang cập nhật...');

                $.ajax({
                    url: "{{ route('admin.menus.items.update', [$activeMenu, ':id']) }}".replace(':id', id),
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        title: {
                            vi: titleVi,
                            en: titleEn
                        },
                        url: url,
                        icon: icon,
                        target: target,
                        is_active: isActive ? '1' : '0'
                    },
                    success: function (response) {
                        btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Cập nhật liên kết');
                        if (response.success) {
                            editModal.hide();
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công',
                                text: response.message
                            }).then(() => {
                                // Reload lại trang để cập nhật cấu trúc DOM mới nhất
                                window.location.reload();
                            });
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Cập nhật liên kết');
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: xhr.responseJSON?.message || 'Có lỗi xảy ra.'
                        });
                    }
                });
            });

            // 5. Xóa liên kết (và con của nó) qua AJAX
            $(document).on('click', '.delete-item-btn', function () {
                const btn = $(this);
                const id = btn.data('id');
                const itemDom = btn.closest('.dd-item');

                Swal.fire({
                    title: 'Xóa liên kết này?',
                    text: 'Hành động này sẽ XÓA liên kết này và TOÀN BỘ các liên kết con trực thuộc nó!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Đồng ý xóa',
                    cancelButtonText: 'Hủy bỏ'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.menus.items.delete', [$activeMenu, ':id']) }}".replace(':id', id),
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Đã xóa',
                                        text: response.message
                                    });
                                    // Xóa DOM item
                                    itemDom.remove();
                                    
                                    // Nếu không còn item nào, hiện thông báo trống
                                    if ($('#nestable > .dd-list').children().length === 0) {
                                        $('#nestable').html('<div class="dd-empty">Kéo thả các liên kết từ bên trái vào đây để xây dựng menu...</div>');
                                    }
                                }
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Lỗi',
                                    text: xhr.responseJSON?.message || 'Không thể xóa liên kết.'
                                });
                            }
                        });
                    }
                });
            });

            // 6. Xóa Menu cha
            const deleteMenuForm = document.getElementById('delete-menu-form');
            $(document).on('click', '.delete-menu-btn', function () {
                const btn = $(this);
                const id = btn.data('id');
                const name = btn.data('name');
                const url = "{{ route('admin.menus.destroy', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: `Xóa menu "${name}"?`,
                    text: 'Hành động này sẽ xóa vĩnh viễn menu này và tất cả các liên kết bên trong!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Xác nhận xóa',
                    cancelButtonText: 'Bỏ qua'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteMenuForm.action = url;
                        deleteMenuForm.submit();
                    }
                });
            });
        @endif
    });
</script>
@endpush
@endsection
