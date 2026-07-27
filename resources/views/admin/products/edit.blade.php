@extends('layouts.admin')

@section('title', 'Sửa sản phẩm')
@section('page-title', 'Sửa sản phẩm')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Sản phẩm</a></li>
        <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
    </ol>
@endsection

@section('content')
    @php
        $variantRows = old('variants', $product->variants->map(fn ($variant) => [
            'id' => $variant->id,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'barcode' => $variant->barcode,
            'price' => $variant->price,
            'compare_price' => $variant->compare_price,
            'stock' => $variant->stock,
            'weight' => $variant->weight,
            'value_ids' => $variant->values->pluck('id')->all(),
            'is_default' => $variant->is_default,
            'is_active' => $variant->is_active,
        ])->all());

        if ($variantRows === []) {
            $variantRows = [['name' => 'Mặc định', 'is_active' => true, 'is_default' => true]];
        }

        $selectedAttributeValueIds = old('attribute_value_ids', $product->attributeValues->pluck('id')->all());
    @endphp

    <form id="admin-save-form" action="{{ route('admin.products.update', $product) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="product-editor-layout">
            <div class="product-editor-main">
                <x-card type="primary" :outline="true" title="Thông tin chung" :collapsible="true" class="mb-0 product-editor-info">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <x-input id="product_name" name="name" label="Tên sản phẩm" :value="$product->name" required />
                        </div>
                        <div class="col-lg-4">
                            <x-slug name="slug" label="Đường dẫn" :value="$product->slug" source="product_name" />
                        </div>
                        <div class="col-12">
                            <x-textarea name="summary" label="Mô tả ngắn" :value="$product->summary" rows="3" />
                        </div>
                        <div class="col-12">
                            <x-tinymce name="description" label="Thông tin sản phẩm" :value="$product->description" />
                        </div>
                        <div class="col-12">
                            <x-tinymce name="ingredients" label="Thành phần cấu tạo" :value="$product->ingredients" />
                        </div>
                        <div class="col-12">
                            <x-tinymce name="usage" label="Hướng dẫn sử dụng" :value="$product->usage" />
                        </div>
                    </div>
                </x-card>

                <x-card type="secondary" :outline="true" title="Ảnh sản phẩm" :collapsible="true" class="mb-0 product-editor-media">
                    <x-product-image-upload
                        name="image"
                        label="Ảnh sản phẩm"
                        :existing-images="$product->getMedia('product_images')->map(fn ($media) => ['id' => $media->id, 'url' => $media->getUrl()])->all()"
                        :max-files="9"
                    />
                </x-card>

                @if($filterAttributes->isNotEmpty())
                    <x-card type="info" :outline="true" title="Phân loại để lọc" :collapsible="true" class="mb-0">
                        <p class="text-muted mb-3">Các lựa chọn này dùng cho bộ lọc và có thể xuất hiện trong mega menu sản phẩm; không tạo thêm biến thể hay SKU.</p>
                        <div class="row g-3">
                            @foreach($filterAttributes as $attribute)
                                <div class="col-md-6" data-product-filter-attribute data-category-ids='@json($attribute->categories->pluck("id")->values())'>
                                    <div class="border rounded p-3 h-100">
                                        <h3 class="h6 fw-semibold mb-2">{{ $attribute->name }}</h3>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($attribute->values as $value)
                                                <div class="form-check form-check-inline me-0">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="attribute_value_ids[]"
                                                        value="{{ $value->id }}"
                                                        id="product_attribute_value_{{ $value->id }}"
                                                        @checked(in_array($value->id, $selectedAttributeValueIds))
                                                    >
                                                    <label class="form-check-label" for="product_attribute_value_{{ $value->id }}">{{ $value->value }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('attribute_value_ids')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </x-card>
                @endif

                <x-admin.product-variant-manager :product="$product" :options="$options" :variants="$variantRows" />
            </div>

            <div class="product-editor-sidebar">
                <x-card type="primary" :outline="true" title="Cấu hình sản phẩm" :collapsible="true" class="mb-0 product-editor-config">
                    <div class="row g-3">
                        <div class="col-12">
                            <x-select id="product_category_id" name="product_category_id" label="Danh mục" :options="$categories" :selected="old('product_category_id', $product->product_category_id)" required />
                        </div>
                        <div class="col-12">
                            <x-select name="brand_id" label="Thương hiệu" :options="$brands" :selected="old('brand_id', $product->brand_id)" />
                        </div>
                        <div class="col-12">
                            <x-select
                                name="status"
                                label="Trạng thái"
                                :options="['active' => 'Đang bán', 'draft' => 'Bản nháp', 'archived' => 'Ngừng bán']"
                                :selected="old('status', $product->status ?: 'active')"
                            />
                        </div>
                        <div class="col-12">
                            <x-select
                                name="variant_selection_mode"
                                label="Cách khách chọn biến thể"
                                :options="[
                                    'combination' => 'Chọn theo tổ hợp (Màu / Dung lượng)',
                                    'options' => 'Chọn lần lượt từng thuộc tính',
                                ]"
                                :selected="old('variant_selection_mode', $product->variant_selection_mode ?: 'combination')"
                            />
                        </div>
                        <div class="col-12">
                            <x-input name="published_at" type="datetime-local" label="Ngày xuất bản" :value="$product->published_at?->format('Y-m-d\TH:i')" />
                        </div>

                        <div class="col-12">
                            <div class="form-check mb-2">
                                <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="product_is_active" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                                <label class="form-check-label cursor-pointer font-weight-bold" for="product_is_active">Hiển thị sản phẩm</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="product_is_featured" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                                <label class="form-check-label cursor-pointer font-weight-bold" for="product_is_featured">Sản phẩm nổi bật (hiển thị ở trang chủ)</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="product_allow_preorder" name="allow_preorder" value="1" @checked(old('allow_preorder', $product->allow_preorder))>
                                <label class="form-check-label cursor-pointer font-weight-bold" for="product_allow_preorder">Cho phép đặt trước</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="product_track_inventory" name="track_inventory" value="1" @checked(old('track_inventory', $product->track_inventory))>
                                <label class="form-check-label cursor-pointer font-weight-bold" for="product_track_inventory">Theo dõi tồn kho</label>
                            </div>
                        </div>
                    </div>
                </x-card>

            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a class="btn btn-default" href="{{ route('admin.products.index') }}">Quay lại</a>
            @if($product->is_active && $product->slug)
                <a class="btn btn-outline-primary" href="{{ route('product.show', $product->slug) }}" target="_blank" rel="noopener">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Xem trên website
                </a>
            @endif
            <button class="btn btn-info text-white" type="submit" name="submit_action" value="save_and_create">
                <i class="bi bi-plus-circle me-1"></i>Lưu &amp; tạo mới <span class="d-none d-md-inline">(Ctrl + Shift + S)</span>
            </button>
            <button class="btn btn-primary" type="submit">Lưu sản phẩm</button>
        </div>
    </form>
    @include('admin.products._filter-attribute-script')
@endsection
