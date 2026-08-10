@extends('layouts.admin')
@section('title', 'Thêm liên hệ')
@section('page-title', 'Thêm liên hệ vào danh bạ')
@section('content')
<x-admin.settings-nav />
<form id="admin-save-form" action="{{ route('admin.settings.contact-channels.store') }}" method="POST">
    @csrf
    <div class="card card-outline card-primary"><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><x-input name="name" label="Tên hiển thị" placeholder="VD: Hotline tư vấn" :required="true" /></div>
        <div class="col-md-3"><x-select name="type" label="Loại liên hệ" :options="$types" :selected="old('type', 'phone')" :required="true" /></div>
        <div class="col-md-3"><x-input name="sort_order" type="number" label="Thứ tự" value="0" min="0" /></div>
        <div class="col-md-6"><x-input name="value" label="Giá trị" placeholder="Số điện thoại, email hoặc ID" :required="true" /></div>
        <div class="col-md-6"><x-input name="url" type="url" label="URL tùy chỉnh" placeholder="Để trống để hệ thống tự tạo tel:, mailto:, zalo.me" /></div>
        <div class="col-md-6"><x-input name="icon" label="Bootstrap icon tùy chọn" placeholder="VD: bi-headset" /></div>
        <div class="col-12"><div class="row g-3">
            @foreach(['is_primary' => 'Liên hệ chính', 'show_topbar' => 'Hiện trên topbar', 'show_footer' => 'Hiện ở footer', 'show_floating' => 'Hiện nút nổi', 'is_active' => 'Đang hoạt động'] as $field => $label)
                <div class="col-md"><div class="form-check form-switch"><input id="{{ $field }}" name="{{ $field }}" value="1" type="checkbox" class="form-check-input" @checked(old($field, $field === 'is_active'))><label for="{{ $field }}">{{ $label }}</label></div></div>
            @endforeach
        </div></div>
    </div></div></div>
    <div class="d-flex justify-content-end gap-2 mt-3"><a href="{{ route('admin.settings.contact-channels.index') }}" class="btn btn-default">Hủy</a><button class="btn btn-primary">Thêm liên hệ</button></div>
</form>
@endsection
