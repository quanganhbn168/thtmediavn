@extends('layouts.admin')

@section('title', 'Cấu hình Trang giới thiệu')
@section('page-title', 'Trang Giới thiệu')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Trang Giới thiệu</li>
    </ol>
@endsection

@section('content')
    <x-admin.settings-nav />
    <form id="admin-save-form" action="{{ route('admin.settings.about.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <div class="col-12">

                <x-card type="primary" :outline="true" title="Giới thiệu doanh nghiệp & Sứ mệnh tầm nhìn" :collapsible="true" :maximizable="true">
                    <x-tinymce 
                        name="about_story" 
                        label="Bài viết giới thiệu chi tiết (Story)" 
                        :value="$settings->about_story" 
                        :translatable="true" 
                    />

                    <x-tinymce
                        name="about_history"
                        label="Lịch sử hình thành"
                        :value="$settings->about_history"
                        :translatable="true"
                    />

                    <x-textarea 
                        name="about_mission" 
                        label="Sứ mệnh phát triển (Mission)" 
                        :value="$settings->about_mission" 
                        :translatable="true" 
                        rows="3"
                    />

                    <x-textarea 
                        name="about_vision" 
                        label="Tầm nhìn dài hạn (Vision)" 
                        :value="$settings->about_vision" 
                        :translatable="true" 
                        rows="3"
                    />

                    <x-tinymce
                        name="about_core_values"
                        label="Giá trị cốt lõi"
                        :value="$settings->about_core_values"
                        :translatable="true"
                    />

                    <div class="border-top pt-3 mt-4">
                        <x-image-upload 
                            name="about_image" 
                            label="Hình ảnh đại diện trang giới thiệu" 
                            :existing-url="$assets->getFirstMediaUrl('about_image')" 
                            :width="800"
                        />
                    </div>
                </x-card>
            </div>

            <!-- Nút lưu cấu hình ở dưới cùng -->
            <div class="col-12 mt-4 mb-5">
                <div class="card p-3 border shadow-sm rounded bg-white">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Lưu cấu hình trang giới thiệu
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
