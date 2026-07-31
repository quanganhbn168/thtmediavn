@extends('layouts.admin')

@section('title', 'Cài đặt menu')
@section('page-title', 'Cài đặt menu website')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Cài đặt menu</li>
    </ol>
@endsection

@section('content')
    <x-admin.settings-nav />

    <form id="admin-save-form" action="{{ route('admin.settings.menu.update') }}" method="POST">
        @csrf

        <div class="d-flex flex-wrap justify-content-end gap-2 mb-3 d-print-none">
            <a class="btn btn-outline-primary" href="{{ route('admin.menus.index') }}">
                <i class="bi bi-diagram-3 me-1"></i> Quản lý cấu trúc menu
            </a>
            <button type="submit" class="btn btn-success font-weight-bold shadow-sm">
                <i class="bi bi-save me-1"></i> Lưu cài đặt menu
            </button>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <x-card type="primary" :outline="true" title="Menu đầu trang" :collapsible="true">
                    <div class="alert alert-light border mb-4">
                        <div class="fw-semibold"><i class="bi bi-info-circle me-1"></i>Cách bố trí</div>
                        <div class="small text-body-secondary mt-1">
                            Chọn rõ từng menu đang dùng: Menu điều hướng hiển thị thành hàng liên kết ngang; Mega menu hiển thị mục cấp 1 ở cột trái, cấp 2 là tiêu đề nhóm và cấp 3 là liên kết chi tiết. Không có menu nào được tự chọn theo thứ tự tạo.
                        </div>
                    </div>

                    <x-select
                        name="header_menu_id"
                        label="Menu điều hướng chính"
                        :options="$headerMenus"
                        :selected="old('header_menu_id', $settings->header_menu_id)"
                        placeholder="Chưa gán menu Header"
                    />

                    <x-select
                        name="mega_menu_id"
                        label="Menu danh mục dạng Mega menu"
                        :options="$headerMenus"
                        :selected="old('mega_menu_id', $settings->mega_menu_id)"
                        placeholder="Dùng danh mục sản phẩm tự động"
                    />
                    <div class="form-text mt-n2">Mega menu chỉ dùng cấu trúc tối đa 3 cấp và phải là menu riêng với Header chính. Khi chưa chọn, website dùng cây danh mục sản phẩm hiện có.</div>
                </x-card>
            </div>

            <div class="col-xl-5">
                <x-card type="success" :outline="true" title="Menu chân trang" :collapsible="true">
                    <x-select
                        name="footer_menu_1_id"
                        label="Cột menu Footer 1"
                        :options="$footerMenus"
                        :selected="old('footer_menu_1_id', $settings->footer_menu_1_id)"
                        placeholder="Chưa gán cột Footer 1"
                    />

                    <x-select
                        name="footer_menu_2_id"
                        label="Cột menu Footer 2"
                        :options="$footerMenus"
                        :selected="old('footer_menu_2_id', $settings->footer_menu_2_id)"
                        placeholder="Chưa gán cột Footer 2"
                    />
                    <div class="form-text">Tên menu được dùng làm tiêu đề cột. Mỗi cột cần chọn một menu riêng; website không tự lấy menu đầu tiên/thứ hai.</div>
                </x-card>
            </div>
        </div>
    </form>
@endsection
