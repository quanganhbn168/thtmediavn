@extends('layouts.admin')

@section('title', 'Cấu hình Trang chủ')
@section('page-title', 'Cấu hình Trang chủ')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Cấu hình Trang chủ</li>
    </ol>
@endsection

@section('content')
    <x-admin.settings-nav />
    <form id="admin-save-form" action="{{ route('admin.settings.homepage.update') }}" method="POST">
        @csrf
        
        <!-- Thanh công cụ đặt phía trên card -->
        <div class="d-flex justify-content-end gap-2 mb-3 d-print-none">
            <button type="submit" class="btn btn-success font-weight-bold shadow-sm">
                <i class="bi bi-save me-1"></i> Lưu cấu hình trang chủ
            </button>
        </div>

        <div class="row">
            <div class="col-12">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <x-card type="primary" :outline="true" title="Hiển thị Banner & Các nhóm nội dung Trang chủ" :collapsible="true" :maximizable="true">
                    <div class="alert alert-light border border-light-subtle mb-4">
                        <div class="font-weight-bold"><i class="bi bi-images me-1"></i>Quản lý ảnh trang chủ theo Slider</div>
                        <div class="small text-body-secondary mt-1">
                            Website lấy trực tiếp các ảnh đang bật trong bộ “Slider chính trang chủ”, hiển thị bằng Swiper và đặt phần nội dung ở bên trái. Ảnh chuẩn: <strong>1920 × 720 px</strong> (8:3); chừa khoảng 45% phía phải cho sản phẩm hoặc người mẫu.
                        </div>
                        <a class="btn btn-sm btn-outline-primary mt-3" href="{{ route('admin.sliders.index') }}"><i class="bi bi-images me-1"></i>Quản lý Slider chính trang chủ</a>
                    </div>
                    <input type="hidden" name="homepage_banner_type" value="slider">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kiểu hiển thị banner trên cùng</label>
                        <div class="form-control bg-body-tertiary">Slider ảnh (đang được website hỗ trợ)</div>
                        <div class="form-text">Video nền và ảnh tĩnh sẽ được mở khi phần hiển thị frontend tương ứng được triển khai.</div>
                    </div>

                    <div class="mb-4 card p-3 bg-light border shadow-sm">
                        <label class="form-label font-weight-bold text-secondary"><i class="bi bi-check-all me-1"></i>Bật/Tắt các khối nội dung hiển thị ở Trang chủ</label>
                        <div class="row">
                        @php
                            $sectionsList = [
                                'categories' => 'Khối Danh mục nổi bật',
                                'flash_sale' => 'Khối Flash Sale',
                                'featured_products' => 'Khối Sản phẩm nổi bật',
                                'brands' => 'Khối Thương hiệu',
                                'testimonials' => 'Khối Cảm nhận khách hàng',
                                'posts' => 'Khối Blog',
                            ];
                            $activeSections = old('homepage_sections', $settings->homepage_sections ?? []);
                        @endphp
                            @foreach($sectionsList as $secKey => $secVal)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="homepage_sections[]" value="{{ $secKey }}" id="chk_sec_{{ $secKey }}" {{ in_array($secKey, $activeSections) ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-bold cursor-pointer" for="chk_sec_{{ $secKey }}">{{ $secVal }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <h6 class="font-weight-bold text-success mb-3"><i class="bi bi-pencil-square me-1"></i>Tiêu đề các khối hiển thị ở Trang chủ</h6>
                        
                        <div class="row">
                            @php
                                $sectionTitleFields = [
                                    'categories' => 'Tiêu đề khối Danh mục',
                                    'flash_sale' => 'Tiêu đề khối Flash Sale',
                                'featured_products' => 'Tiêu đề khối Sản phẩm nổi bật',
                                'brands' => 'Tiêu đề khối Thương hiệu',
                                'testimonials' => 'Tiêu đề khối Cảm nhận khách hàng',
                                'posts' => 'Tiêu đề khối Blog',
                                ];
                            @endphp
                            @foreach($sectionTitleFields as $section => $label)
                                <div class="col-md-6">
                                    <div class="mb-3 p-3 border rounded bg-white shadow-xs">
                                        <x-input 
                                            name="homepage_section_titles[{{ $section }}]" 
                                            :label="$label" 
                                            :value="$settings->homepage_section_titles[$section] ?? []" 
                                            :translatable="true" 
                                        />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </form>
@endsection
