@extends('layouts.admin')

@section('title', 'Chỉnh sửa ảnh trình chiếu')
@section('page-title', 'Chỉnh sửa ảnh trình chiếu')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.sliders.index') }}">Bộ trình chiếu</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.sliders.edit', $item->slider) }}">{{ $item->slider->name }}</a></li>
    <li class="breadcrumb-item active">Ảnh #{{ $item->id }}</li>
</ol>
@endsection

@section('content')
<form id="admin-save-form" action="{{ route('admin.slider-items.update', $item) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="card card-outline card-info h-100">
                <div class="card-header"><h3 class="card-title"><i class="bi bi-image me-2"></i>Hình ảnh</h3></div>
                <div class="card-body">
                    <x-image-upload
                        name="image"
                        label="Ảnh slide"
                        :value="$item->getFirstMedia('slide_image') ? 'media/'.$item->getFirstMedia('slide_image')->id.'/'.$item->getFirstMedia('slide_image')->file_name : null"
                        placeholder="Kéo thả hoặc chọn ảnh slide"
                        :width="1920"
                    />
                    <div class="alert alert-light border mb-0 small">
                        <i class="bi bi-aspect-ratio me-1 text-primary"></i><strong>Chuẩn slider chính trang chủ: 1920 × 720 px</strong> (tỷ lệ 8:3). Nội dung chữ được website đặt ở bên trái, nên để sản phẩm/người mẫu vào khoảng 45% bên phải của ảnh và không chèn chữ trực tiếp vào ảnh.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title"><i class="bi bi-card-text me-2"></i>Nội dung hiển thị</h3></div>
                <div class="card-body">
                    <x-input name="title" label="Tiêu đề" :value="$item" :translatable="true" />
                    <x-input name="sub_title" label="Phụ đề" :value="$item" :translatable="true" />

                    <div class="row">
                        <div class="col-md-6">
                            <div class="border rounded p-3 mb-3 h-100">
                                <h6 class="mb-3"><i class="bi bi-cursor-fill me-1 text-primary"></i>Nút liên kết 1</h6>
                                <x-input name="button_text_1" label="Nhãn nút" :value="is_array($item->buttons) ? ($item->buttons[0]['text'] ?? []) : []" :translatable="true" />
                                <x-input name="button_link_1" label="Đường dẫn" :value="is_array($item->buttons) ? ($item->buttons[0]['link'] ?? '') : ''" placeholder="/san-pham hoặc https://..." />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 mb-3 h-100">
                                <h6 class="mb-3"><i class="bi bi-cursor me-1 text-primary"></i>Nút liên kết 2</h6>
                                <x-input name="button_text_2" label="Nhãn nút" :value="is_array($item->buttons) ? ($item->buttons[1]['text'] ?? []) : []" :translatable="true" />
                                <x-input name="button_link_2" label="Đường dẫn" :value="is_array($item->buttons) ? ($item->buttons[1]['link'] ?? '') : ''" placeholder="/lien-he hoặc https://..." />
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <x-input name="sort_order" type="number" label="Thứ tự hiển thị" :value="$item->sort_order" min="0" max="9999" :required="true" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="slider_item_is_active" value="1" @checked((bool) old('is_active', $item->is_active))>
                                <label class="form-check-label" for="slider_item_is_active">Hiển thị ảnh này</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('admin.sliders.edit', $item->slider) }}" class="btn btn-default"><i class="bi bi-x-lg me-1"></i>Hủy</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Lưu thay đổi</button>
    </div>
</form>
@endsection
