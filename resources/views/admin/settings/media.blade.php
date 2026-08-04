@extends('layouts.admin')

@section('title', 'Cấu hình Media')
@section('page-title', 'Cấu hình Media & Banner mặc định')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Cấu hình Media</li>
    </ol>
@endsection

@section('content')
    <x-admin.settings-nav />
    <form id="admin-save-form" action="{{ route('admin.settings.media.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <div class="col-12">

                <x-card type="primary" :outline="true" title="Quy tắc upload & Banner mặc định" :collapsible="true" :maximizable="true">
                    
                    <div class="card p-3 bg-light border mb-4 shadow-sm">
                        <h6 class="font-weight-bold text-secondary mb-3"><i class="bi bi-file-earmark-arrow-up me-1"></i>Quy tắc tải lên ảnh & tệp tin</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <x-input name="media_allowed_extensions" label="Định dạng tệp cho phép tải lên" :value="old('media_allowed_extensions', $settings->media_allowed_extensions)" :required="true" placeholder="Ví dụ: jpg,png,webp,pdf..." />
                                <div class="form-text mt-n2 mb-3">Chỉ nhận: JPG, JPEG, PNG, WEBP, GIF, AVIF, ICO, PDF, DOC, DOCX, MP4, WEBM, MOV. Tệp thực thi và SVG không được phép.</div>
                            </div>
                            <div class="col-md-6">
                                <x-input name="media_max_size" type="number" label="Dung lượng tệp tối đa cho phép (MB)" :value="old('media_max_size', $settings->media_max_size)" :required="true" />
                            </div>
                        </div>
                        <div class="row mt-2 align-items-center">
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="media_webp_conversion" id="settings_media_webp_conversion" value="1" {{ old('media_webp_conversion', $settings->media_webp_conversion) ? 'checked' : '' }}>
                                    <label class="form-check-label font-weight-bold cursor-pointer" for="settings_media_webp_conversion">Tự động nén tối ưu sang định dạng WebP</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <x-input name="media_quality" type="number" label="Chất lượng nén tối ưu (0-100)" :value="old('media_quality', $settings->media_quality)" :required="true" />
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <h6 class="font-weight-bold text-info mb-3"><i class="bi bi-image me-1"></i>Banners mặc định theo các Module chính</h6>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <x-image-upload 
                                    name="default_product_banner" 
                                    label="Banner danh mục sản phẩm" 
                                    :existing-url="$assets->getFirstMediaUrl('default_product_banner')" 
                                    :width="1920"
                                />
                            </div>

                            <div class="col-md-4">
                                <x-image-upload 
                                    name="default_promotion_banner" 
                                    label="Banner chương trình khuyến mãi" 
                                    :existing-url="$assets->getFirstMediaUrl('default_promotion_banner')" 
                                    :width="1920"
                                />
                            </div>

                            <div class="col-md-4">
                                <x-image-upload 
                                    name="default_post_banner" 
                                    label="Banner danh mục Tin tức / Bài viết" 
                                    :existing-url="$assets->getFirstMediaUrl('default_post_banner')" 
                                    :width="1920"
                                />
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Nút lưu cấu hình ở dưới cùng -->
            <div class="col-12 mt-4 mb-5">
                <div class="card p-3 border shadow-sm rounded bg-white">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Lưu cấu hình Media
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
