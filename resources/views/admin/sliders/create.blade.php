@extends('layouts.admin')

@section('title', 'Thêm bộ trình chiếu')
@section('page-title', 'Thêm bộ trình chiếu')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.sliders.index') }}">Bộ trình chiếu</a></li>
    <li class="breadcrumb-item active" aria-current="page">Thêm mới</li>
</ol>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <form id="admin-save-form" action="{{ route('admin.sliders.store') }}" method="POST">
            @csrf
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-plus-circle me-2"></i>Thông tin bộ trình chiếu</h3>
                </div>
                <div class="card-body">
                    <x-input
                        name="name"
                        label="Tên bộ trình chiếu"
                        :value="old('name')"
                        :translatable="true"
                        :required="true"
                        id="slider_name"
                    />

                    <x-select
                        name="key"
                        label="Vị trí hiển thị"
                        :options="$sliderTypes"
                        :selected="old('key')"
                        placeholder="Chọn vị trí sử dụng bộ trình chiếu"
                        :required="true"
                        id="slider_type"
                    />
                    <div class="alert alert-info border-0">
                        <div><i class="bi bi-info-circle me-1"></i>Mỗi vị trí chỉ có một bộ trình chiếu. Mã kỹ thuật được quản lý bằng <code>SliderType</code>, không cần nhập thủ công.</div>
                    </div>

                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" @checked((bool) old('is_active', true))>
                        <label class="form-check-label" for="is_active">Kích hoạt ngay sau khi tạo</label>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.sliders.index') }}" class="btn btn-default">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Lưu và thêm slide
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
