@extends('layouts.admin')

@section('title', $coupon->exists ? 'Sửa mã giảm giá' : 'Thêm mã giảm giá')
@section('page-title', $coupon->exists ? 'Sửa mã giảm giá' : 'Thêm mã giảm giá')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.coupons.index') }}">Mã giảm giá</a></li>
    <li class="breadcrumb-item active">{{ $coupon->exists ? 'Chỉnh sửa' : 'Tạo mới' }}</li>
</ol>
@endsection

@section('content')
<form id="admin-save-form" method="post" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}">
    @csrf
    @if($coupon->exists) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <x-card type="primary" :outline="true" title="Thông tin ưu đãi">
                <div class="row g-3">
                    <div class="col-md-4"><x-input name="code" label="Mã giảm giá" :value="old('code', $coupon->code)" required /></div>
                    <div class="col-md-8"><x-input name="name" label="Tên chương trình" :value="old('name', $coupon->name)" required /></div>
                    <div class="col-md-4"><x-select name="type" label="Loại giảm giá" :options="['fixed' => 'Giảm tiền', 'percent' => 'Phần trăm', 'free_shipping' => 'Miễn phí giao hàng']" :selected="old('type', $coupon->type ?: 'fixed')" required /></div>
                    <div class="col-md-4"><x-input name="value" type="number" step="0.01" min="0" label="Giá trị" :value="old('value', $coupon->value)" /></div>
                    <div class="col-md-4"><x-input name="max_discount" type="number" step="1000" min="0" label="Giảm tối đa" :value="old('max_discount', $coupon->max_discount)" /></div>
                    <div class="col-md-4"><x-input name="minimum_order" type="number" step="1000" min="0" label="Đơn tối thiểu" :value="old('minimum_order', $coupon->minimum_order)" /></div>
                    <div class="col-md-4"><x-input name="usage_limit" type="number" min="1" label="Tổng lượt dùng" :value="old('usage_limit', $coupon->usage_limit)" /></div>
                    <div class="col-md-4"><x-input name="usage_limit_per_user" type="number" min="1" label="Lượt mỗi khách" :value="old('usage_limit_per_user', $coupon->usage_limit_per_user)" /></div>
                    <div class="col-md-6"><x-input name="starts_at" type="datetime-local" label="Bắt đầu" :value="old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i'))" /></div>
                    <div class="col-md-6"><x-input name="ends_at" type="datetime-local" label="Kết thúc" :value="old('ends_at', $coupon->ends_at?->format('Y-m-d\TH:i'))" /></div>
                </div>
            </x-card>

            <x-card type="secondary" :outline="true" title="Phạm vi áp dụng" class="mt-4">
                @php
                    $selectedProducts = collect(old('product_ids', $coupon->products?->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all();
                    $selectedCategories = collect(old('category_ids', $coupon->categories?->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all();
                @endphp
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="coupon-products">Sản phẩm</label>
                        <select class="form-select" id="coupon-products" name="product_ids[]" multiple size="10">
                            @foreach($products as $id => $name)<option value="{{ $id }}" @selected(in_array((string) $id, $selectedProducts, true))>{{ $name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <x-admin.category-tree-select
                            id="coupon-categories"
                            name="category_ids"
                            label="Danh mục"
                            :categories="$categories"
                            :selected="$selectedCategories"
                            :multiple="true"
                            :leaf-only="true"
                            :active-only="true"
                        />
                    </div>
                </div>
                <div class="form-text mt-2">Không chọn sản phẩm hoặc danh mục để áp dụng cho toàn bộ catalog khả dụng.</div>
            </x-card>
        </div>

        <div class="col-lg-4">
            <x-card type="info" :outline="true" title="Trạng thái">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="coupon-active" value="1" @checked(old('is_active', $coupon->exists ? $coupon->is_active : true))>
                    <label class="form-check-label fw-semibold" for="coupon-active">Kích hoạt</label>
                </div>
            </x-card>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 mb-5">
        <a class="btn btn-default" href="{{ route('admin.coupons.index') }}">Quay lại</a>
        <button class="btn btn-primary" type="submit">Lưu mã giảm giá</button>
    </div>
</form>
@endsection
