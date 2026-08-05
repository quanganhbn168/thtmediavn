@extends('layouts.admin')

@section('title', 'Sản phẩm')
@section('page-title', 'Sản phẩm')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Sản phẩm</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách sản phẩm"
    description="Quản lý sản phẩm, giá bán và tồn kho."
    icon="bi-box-seam"
    :create-url="route('admin.products.create')"
    create-label="Thêm sản phẩm"
    resource="product"
    :bulk-actions="['delete' => 'Xóa']"
    bulk-delete-warning="Sản phẩm được xóa sẽ rời khỏi tất cả chương trình liên quan."
>
    <x-slot:filters>
        <form action="{{ route('admin.products.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-xl-4 col-md-6">
                    <label for="product-search" class="form-label">Từ khóa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input class="form-control" id="product-search" name="search" value="{{ request('search') }}" placeholder="Tên sản phẩm">
                    </div>
                </div>
                <div class="col-xl-3 col-md-4">
                    <label for="product-category" class="form-label">Danh mục</label>
                    <select class="form-select" id="product-category" name="category">
                        <option value="">Tất cả</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label for="product-brand" class="form-label">Thương hiệu</label>
                    <select class="form-select" id="product-brand" name="brand">
                        <option value="">Tất cả</option>
                        @foreach($brands as $id => $name)
                            <option value="{{ $id }}" @selected((string) request('brand') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-2">
                    <label for="product-per-page" class="form-label">Số dòng</label>
                    <select class="form-select" id="product-per-page" name="per_page">
                        @foreach([10, 20, 25, 50] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-1 col-md-1 d-flex">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-funnel me-1"></i>Lọc
                    </button>
                </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                <th data-select-column class="text-center" style="width:48px">
                        <input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả">
                    </th>
                    <th>Sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th class="text-center" style="width:110px">Nổi bật</th>
                    <th class="text-center" style="width:110px">Trang chủ</th>
                    <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $defaultVariant = $product->variants->first(fn ($variant) => $variant->is_default && $variant->is_active)
                                ?: $product->variants->first(fn ($variant) => $variant->is_active)
                                ?: $product->variants->first();
                            $defaultPrice = (float) ($defaultVariant?->price ?? 0);
                        @endphp
                        <tr data-record-id="{{ $product->id }}">
                            <td data-select-column class="text-center">
                                <input form="admin-bulk-product-form" type="checkbox" name="ids[]" value="{{ $product->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $product->name }}">
                            </td>
                            <td>
                                <div class="d-flex gap-2 align-items-center">
                                    <img src="{{ $product->image_url }}" class="admin-product-thumb rounded" width="54" height="54" alt="">
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <small class="d-block text-muted">Mã SKU: {{ $defaultVariant?->sku ?: 'Chưa có' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category->name }}<small class="d-block text-muted">{{ $product->brand?->name }}</small></td>
                            <td><strong>{{ number_format($defaultPrice, 0, ',', '.') }}₫</strong></td>
                            <td class="text-center">
                                <x-toggle model="Product" :id="$product->id" field="is_featured" :checked="$product->is_featured" label="" />
                            </td>
                            <td class="text-center">
                                <x-toggle model="Product" :id="$product->id" field="is_home" :checked="$product->is_home" label="" />
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @if($product->is_active && $product->slug)
                                        <a class="btn btn-default" href="{{ route('product.show', $product->slug) }}" target="_blank" rel="noopener" title="Xem trên website">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @endif
                                    <a class="btn btn-default" href="{{ route('admin.products.edit', $product) }}" title="Chỉnh sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="submit" form="delete-product-{{ $product->id }}" class="btn btn-default text-danger" title="Xóa">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">Chưa có sản phẩm.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @foreach($products as $product)
            <form
                id="delete-product-{{ $product->id }}"
                action="{{ route('admin.products.destroy', $product) }}"
                method="post"
                class="d-none"
                data-admin-delete-form
                data-delete-title="Xóa sản phẩm này?"
                data-delete-warning="Sản phẩm có thể có biến thể, hình ảnh và dữ liệu liên quan."
            >
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <x-slot:footer>
            @if($products->hasPages())
                {{ $products->links() }}
            @endif
        </x-slot:footer>
    </x-admin.index-card>
@endsection
