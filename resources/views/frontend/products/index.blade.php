@extends('layouts.master')

@section('title', 'Sản phẩm — ' . $website['name'])

@section('content')
<div class="breadcrumb-wrap">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sản phẩm</li>
            </ol>
        </nav>
    </div>
</div>

<section class="page-hero">
    <div class="container">
        <h1>Sản phẩm</h1>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="d-lg-none mb-3">
            <button class="btn btn-outline-primary w-100" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                <i class="bi bi-funnel me-2"></i>Bộ lọc sản phẩm
            </button>
        </div>
        <div class="row g-4">
            <aside class="col-lg-3 d-none d-lg-block">
                @include('partials.catalog-filters', ['filterSuffix' => '-desktop'])
            </aside>
            <div class="col-lg-9">
                <div class="catalog-toolbar d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                    <div>
                        <strong>{{ $products->total() }}</strong> sản phẩm
                        @if($searchTerm)<span class="text-muted">cho “{{ $searchTerm }}”</span>@endif
                    </div>
                    <form class="d-flex align-items-center gap-2" action="{{ route('catalog') }}" method="get">
                        @if($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory }}">@endif
                        @if($activeBrand)<input type="hidden" name="brand" value="{{ $activeBrand }}">@endif
                        @if($searchTerm)<input type="hidden" name="q" value="{{ $searchTerm }}">@endif
                        @if($activePrice)<input type="hidden" name="price" value="{{ $activePrice }}">@endif
                        @if($activeStock)<input type="hidden" name="stock" value="{{ $activeStock }}">@endif
                        @foreach($activeOptionValues as $optionId => $values)
                            @foreach($values as $valueId)
                                <input type="hidden" name="option_values[{{ $optionId }}][]" value="{{ $valueId }}">
                            @endforeach
                        @endforeach
                        @foreach($activeAttributeValues as $attributeId => $values)
                            @foreach($values as $valueId)
                                <input type="hidden" name="attribute_values[{{ $attributeId }}][]" value="{{ $valueId }}">
                            @endforeach
                        @endforeach
                        <label class="small text-muted flex-shrink-0" for="catalog-sort">Sắp xếp:</label>
                        <select class="form-select form-select-sm" id="catalog-sort" name="sort" onchange="this.form.submit()">
                            <option value="featured" {{ $sort === 'featured' ? 'selected' : '' }}>Nổi bật</option>
                            <option value="price-asc" {{ $sort === 'price-asc' ? 'selected' : '' }}>Giá tăng dần</option>
                            <option value="price-desc" {{ $sort === 'price-desc' ? 'selected' : '' }}>Giá giảm dần</option>
                            <option value="name-asc" {{ $sort === 'name-asc' ? 'selected' : '' }}>Tên A–Z</option>
                        </select>
                    </form>
                </div>

                @if($products->isNotEmpty())
                    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
                        @foreach($products as $product)
                            <div class="col"><x-product-card :product="$product" /></div>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $products->links() }}</div>
                @else
                    <div class="catalog-empty content-card">
                        <i class="bi bi-search display-4 text-primary"></i>
                        <h2 class="h4 fw-bold mt-3">Không tìm thấy sản phẩm</h2>
                        <p class="text-muted">Thử thay đổi từ khóa hoặc xóa bộ lọc đang chọn.</p>
                        <a class="btn btn-primary" href="{{ route('catalog') }}">Xem toàn bộ sản phẩm</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title h5" id="filterOffcanvasLabel">Bộ lọc sản phẩm</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
    </div>
    <div class="offcanvas-body">
        @include('partials.catalog-filters', ['filterSuffix' => '-mobile'])
    </div>
</div>
@endsection

