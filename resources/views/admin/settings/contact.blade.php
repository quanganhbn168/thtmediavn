@extends('layouts.admin')

@section('title', 'Thông tin liên lạc')
@section('page-title', 'Thông tin liên lạc & Công ty')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thông tin liên lạc</li>
    </ol>
@endsection

@section('content')
    <x-admin.settings-nav />
    <form id="admin-save-form" action="{{ route('admin.settings.contact.update') }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-12">

                <x-card type="primary" :outline="true" title="Thông tin liên hệ chính thức" :collapsible="true" :maximizable="true">
                    <div class="row">
                        <div class="col-md-6">
                            <x-input name="company_name" label="Tên doanh nghiệp / Đơn vị" :value="old('company_name', $settings->company_name)" />
                        </div>
                        <div class="col-md-6">
                            <x-input name="tax_code" label="Mã số thuế doanh nghiệp" :value="old('tax_code', $settings->tax_code)" />
                        </div>
                    </div>

                    <x-input name="address" label="Địa chỉ trụ sở" :value="old('address', $settings->address)" />
                    
                    <div class="row">
                        <div class="col-md-4">
                            <x-input name="phone" label="Số điện thoại bàn" :value="old('phone', $settings->phone)" />
                        </div>
                        <div class="col-md-4 d-flex align-items-end"><a href="{{ route('admin.settings.contact-channels.index') }}" class="btn btn-outline-primary mb-3 w-100"><i class="bi bi-person-lines-fill me-1"></i>Quản lý các số khác trong Danh bạ</a></div>
                        <div class="col-md-4">
                            <x-input name="email" type="email" label="Hộp thư điện tử (Email)" :value="old('email', $settings->email)" />
                        </div>
                        <div class="col-md-12 mt-3">
                            <div class="form-text text-muted">Nếu để trống, hệ thống fallback sang email liên hệ mặc định.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <x-input name="working_hours" label="Thời gian làm việc (Working Hours)" :value="old('working_hours', $settings->working_hours)" placeholder="Ví dụ: 8:00 - 17:30, Thứ 2 - Thứ 7..." />
                        </div>
                        <div class="col-md-6">
                            <x-input name="zalo" label="Liên kết Zalo (OA hoặc số điện thoại)" :value="old('zalo', $settings->zalo)" placeholder="Ví dụ: https://zalo.me/..." />
                        </div>
                    </div>

                    <!-- Mạng xã hội -->
                    <div class="card p-3 bg-light border mb-4 mt-3 shadow-sm">
                        <h6 class="font-weight-bold text-secondary mb-3"><i class="bi bi-share me-1"></i>Liên kết các Mạng xã hội</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <x-input name="facebook" label="Facebook Page URL" :value="old('facebook', $settings->facebook)" placeholder="https://facebook.com/..." />
                            </div>
                            <div class="col-md-6">
                                <x-input name="instagram" label="Instagram URL" :value="old('instagram', $settings->instagram)" placeholder="https://instagram.com/..." />
                            </div>
                            <div class="col-md-6">
                                <x-input name="youtube" label="Youtube Channel URL" :value="old('youtube', $settings->youtube)" placeholder="https://youtube.com/..." />
                            </div>
                            <div class="col-md-6">
                                <x-input name="tiktok" label="Tiktok Channel URL" :value="old('tiktok', $settings->tiktok)" placeholder="https://tiktok.com/@..." />
                            </div>
                        </div>
                    </div>

                    <x-textarea name="map_embed" label="Nhúng bản đồ Google Map (Thẻ <iframe> HTML)" :value="old('map_embed', $settings->map_embed)" rows="4" />
                    <div class="form-text text-muted mb-3">Nhúng mã <code>&lt;iframe&gt;</code> được sao chép trực tiếp từ chức năng chia sẻ bản đồ của Google Maps để hiển thị bản đồ trên website.</div>
                </x-card>
            </div>

            <!-- Nút lưu cấu hình ở dưới cùng -->
            <div class="col-12 mt-4 mb-5">
                <div class="card p-3 border shadow-sm rounded bg-white">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Lưu thông tin liên hệ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
