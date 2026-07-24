@extends('layouts.master')
@section('content')
<section class="section-space">
    <div class="container">
        <x-section-heading title="Sản phẩm yêu thích" :center="false" />
        @if($products->isEmpty())
            <div class="content-card text-center py-5">
                <i class="bi bi-heart display-3 text-primary"></i>
                <h2 class="h4 mt-3">Chưa có sản phẩm yêu thích</h2>
                <a class="btn btn-primary mt-2" href="{{ route('catalog') }}">Khám phá sản phẩm</a>
            </div>
        @else
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                @foreach($products as $model)
                    @php
                        $variant = null;
                        if (is_array($model)) {
                            $product = $model;
                        } else {
                            $variant = $model->default_variant;
                            $product = [
                                'id' => $model->id,
                                'slug' => $model->slug,
                                'name' => $model->name,
                                'brand' => $model->brand?->name ?? 'Không thương hiệu',
                                'price' => (float) ($variant?->effective_price ?? 0),
                                'old_price' => $variant?->compare_price !== null ? (float) $variant->compare_price : null,
                                'image' => $model->image_url,
                                'badges' => [],
                                'stock' => (bool) (($variant?->stock ?? 0) > 0),
                            ];
                        }
                    @endphp
                    <div class="col">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
