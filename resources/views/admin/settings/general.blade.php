@extends('layouts.admin')

@section('title', 'Cài đặt chung')
@section('page-title', 'Cài đặt chung hệ thống')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Cài đặt chung</li>
    </ol>
@endsection

@section('content')
    <x-admin.settings-nav />
    <form id="admin-save-form" action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <div class="col-12">

                <x-card type="primary" :outline="true" title="Cài đặt cơ bản & Nhận diện Website" :collapsible="true" :maximizable="true">
                    <div class="mb-4">
                        <label class="form-label font-weight-bold">Trạng thái hoạt động Website</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="site_status" id="settings_site_status" value="1" {{ old('site_status', $settings->site_status) ? 'checked' : '' }}>
                            <label class="form-check-label font-weight-bold cursor-pointer" for="settings_site_status">Cho phép khách truy cập Website (Bật / Tắt bảo trì)</label>
                        </div>
                    </div>

                    @php
                        $timezones = \DateTimeZone::listIdentifiers();
                        $timezoneOptions = array_combine($timezones, $timezones);
                    @endphp
                    <x-select 
                        name="timezone" 
                        label="Múi giờ hệ thống" 
                        :options="$timezoneOptions" 
                        :selected="old('timezone', $settings->timezone)" 
                        :required="true"
                    />

                    <x-input 
                        name="site_name" 
                        label="Tiêu đề chính của Website (Site Name)" 
                        :value="$settings->site_name" 
                        :translatable="true" 
                        :required="true"
                        id="setting_site_name"
                    />

                    <x-textarea 
                        name="site_description" 
                        label="Mô tả ngắn Website (Site Description)" 
                        :value="$settings->site_description" 
                        :translatable="true" 
                        rows="3"
                    />

                    <x-input 
                        name="copyright" 
                        label="Bản quyền chân trang (Copyright)" 
                        :value="$settings->copyright" 
                        :translatable="true" 
                        placeholder="Ví dụ: © 2026 THT MEDIA VN..."
                    />

                    <div class="row border-top pt-3 mt-4">
                        <div class="col-md-4">
                            <x-image-upload 
                                name="logo" 
                                label="Logo chính (Header sáng màu)" 
                                :existing-url="$assets->getFirstMediaUrl('logo')" 
                                :width="400"
                            />
                        </div>
                        <div class="col-md-4">
                            <x-image-upload 
                                name="logo_footer" 
                                label="Logo phụ (Footer tối màu)" 
                                :existing-url="$assets->getFirstMediaUrl('logo_footer')" 
                                :width="400"
                            />
                        </div>
                        <div class="col-md-4">
                            <x-image-upload 
                                name="favicon" 
                                label="Favicon Icon (.ico, .png)" 
                                :existing-url="$assets->getFirstMediaUrl('favicon')" 
                                :width="64"
                                :height="64"
                                :convertToWebp="false"
                            />
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Nút lưu cấu hình ở dưới cùng -->
            <div class="col-12 mt-4 mb-5">
                <div class="card p-3 border shadow-sm rounded bg-white">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Lưu cấu hình chung
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
