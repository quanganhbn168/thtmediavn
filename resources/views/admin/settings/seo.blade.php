@extends('layouts.admin')

@section('title', 'Cấu hình SEO')
@section('page-title', 'Cấu hình SEO hệ thống')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Cấu hình SEO</li>
    </ol>
@endsection

@section('content')
    <x-admin.settings-nav />
    <form id="admin-save-form" action="{{ route('admin.settings.seo.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <div class="col-12">

                <x-card type="primary" :outline="true" title="Tối ưu hóa công cụ tìm kiếm (SEO)" :collapsible="true" :maximizable="true">
                    <x-input 
                        name="seo_title" 
                        label="SEO Title mặc định" 
                        :value="$settings->seo_title" 
                        :translatable="true" 
                        placeholder="Tiêu đề chuẩn SEO xuất hiện ở tab trình duyệt..."
                    />

                    <x-textarea 
                        name="seo_description" 
                        label="SEO Description mặc định" 
                        :value="$settings->seo_description" 
                        :translatable="true" 
                        rows="3"
                        placeholder="Mô tả chuẩn SEO xuất hiện ở kết quả tìm kiếm Google..."
                    />

                    <x-input 
                        name="seo_keywords" 
                        label="Từ khóa SEO mặc định (Keywords)" 
                        :value="$settings->seo_keywords" 
                        :translatable="true" 
                        placeholder="Ví dụ: truyền thông, nội dung, thương hiệu..."
                    />

                    <x-textarea 
                        name="google_analytics_code" 
                        label="Mã nhúng theo dõi (Google Analytics, Tag Manager, Facebook Pixel...)" 
                        :value="old('google_analytics_code', $settings->google_analytics_code)" 
                        rows="4" 
                        placeholder="Dán mã script tracking tại đây..."
                    />

                    <div class="border-top pt-3 mt-4">
                        <x-image-upload 
                            name="seo_image" 
                            label="Ảnh chia sẻ mạng xã hội mặc định (OG Image)" 
                            :existing-url="$assets->getFirstMediaUrl('seo_image')" 
                            :width="1200"
                        />
                    </div>
                </x-card>
            </div>

            <!-- Nút lưu cấu hình ở dưới cùng -->
            <div class="col-12 mt-4 mb-5">
                <div class="card p-3 border shadow-sm rounded bg-white">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Lưu cấu hình SEO
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
