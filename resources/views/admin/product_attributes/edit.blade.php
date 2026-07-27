@extends('layouts.admin')

@php($valuesText = old('values_text', $attribute->values->pluck('value')->implode("\n")))

@section('title', 'Sửa thuộc tính lọc')
@section('page-title', 'Sửa thuộc tính lọc')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.product-attributes.index') }}">Thuộc tính lọc</a></li>
        <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
    </ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.product-attributes.update', $attribute) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <div class="col-lg-8">
                <x-card type="primary" :outline="true" title="Thông tin nhóm lọc" :collapsible="true" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-7"><x-input id="product_attribute_name" name="name" label="Tên thuộc tính" :value="$attribute->name" required /></div>
                        <div class="col-md-5"><x-slug name="slug" label="Đường dẫn" :value="$attribute->slug" source="product_attribute_name" /></div>
                    </div>
                </x-card>

                <x-card type="secondary" :outline="true" title="Các giá trị" :collapsible="true">
                    <x-textarea name="values_text" label="Mỗi dòng một giá trị" :value="$valuesText" rows="10" />
                    <p class="text-muted small mb-0">Giá trị đã được gắn vào sản phẩm sẽ được giữ an toàn nếu anh xóa dòng đó; chúng hiện lại sau khi lưu để tránh làm mất bộ lọc của sản phẩm cũ.</p>
                </x-card>
                <x-card type="secondary" :outline="true" title="Áp dụng cho danh mục" :collapsible="true" class="mt-4">
                    <p class="text-muted small">Sản phẩm trong danh mục con sẽ kế thừa bộ lọc của danh mục cha.</p>
                    @php($selectedCategoryIds = old('category_ids', $attribute->categories->pluck('id')->all()))
                    <div class="row g-2">@foreach($categories as $categoryId => $categoryName)<div class="col-md-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="category_ids[]" value="{{ $categoryId }}" id="attribute_category_{{ $categoryId }}" @checked(in_array($categoryId, $selectedCategoryIds))><label class="form-check-label" for="attribute_category_{{ $categoryId }}">{{ $categoryName }}</label></div></div>@endforeach</div>
                </x-card>
            </div>
            <div class="col-lg-4">
                <x-card type="info" :outline="true" title="Hiển thị" :collapsible="true" class="mb-4">
                    <x-input name="sort_order" type="number" label="Thứ tự" :value="old('sort_order', $attribute->sort_order)" min="0" step="1" />
                    <div class="border-top pt-3">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch"><input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_active" id="product_attribute_is_active" value="1" @checked((bool) old('is_active', $attribute->is_active))><label class="form-check-label cursor-pointer fw-semibold" for="product_attribute_is_active">Kích hoạt thuộc tính</label></div>
                    </div>
                    <div class="border-top pt-3 mt-3">
                        <input type="hidden" name="show_in_product_menu" value="0">
                        <div class="form-check form-switch"><input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="show_in_product_menu" id="product_attribute_show_in_product_menu" value="1" @checked((bool) old('show_in_product_menu', $attribute->show_in_product_menu))><label class="form-check-label cursor-pointer fw-semibold" for="product_attribute_show_in_product_menu">Hiển thị trong mega menu sản phẩm</label></div>
                        <p class="text-muted small mb-0 mt-2">Menu sẽ dùng đúng tên thuộc tính này; chỉ hiện các giá trị đang có sản phẩm.</p>
                    </div>
                </x-card>
                <x-card type="secondary" :outline="true" title="Sử dụng" :collapsible="true"><p class="text-muted small mb-0">Sau khi thêm giá trị, vào từng sản phẩm và chọn trong mục <strong>Phân loại để lọc</strong>.</p></x-card>
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 mb-5"><a class="btn btn-default" href="{{ route('admin.product-attributes.index') }}"><i class="bi bi-arrow-left me-1"></i>Quay lại</a><button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>Lưu thay đổi</button></div>
    </form>
@endsection
