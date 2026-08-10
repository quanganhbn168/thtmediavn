@php
    $suffix = $filterSuffix ?? '';
    $isMobileFilter = str_contains($suffix, 'mobile');
    $filterAttributes = collect($attributeGroups ?? []);
@endphp

<form class="catalog-filter-form" action="{{ route('catalog') }}" method="get" data-catalog-filter-form>
    @if($searchTerm ?? false)<input type="hidden" name="q" value="{{ $searchTerm }}">@endif
    @if(($sort ?? 'featured') !== 'featured')<input type="hidden" name="sort" value="{{ $sort }}">@endif

    <div class="filter-card">
        <h2 class="filter-card-title">Loại sản phẩm</h2>
        <div class="filter-card-body">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="category" value="" id="category-all{{ $suffix }}" @checked(empty($activeCategory))>
                <label class="form-check-label filter-label" for="category-all{{ $suffix }}"><span>Tất cả sản phẩm</span></label>
            </div>
            @foreach($categories as $category)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="category" value="{{ $category['slug'] }}" id="category-{{ $category['slug'] }}{{ $suffix }}" @checked(($activeCategory ?? '') === $category['slug'])>
                    <label class="form-check-label filter-label" for="category-{{ $category['slug'] }}{{ $suffix }}"><span>{{ $category['title'] }}</span><small>{{ $category['products_count'] ?? 0 }}</small></label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="filter-card">
        <h2 class="filter-card-title">Thương hiệu</h2>
        <div class="filter-card-body">
            <div class="form-check"><input class="form-check-input" type="radio" name="brand" value="" id="brand-all{{ $suffix }}" @checked(empty($activeBrand))><label class="form-check-label filter-label" for="brand-all{{ $suffix }}"><span>Tất cả thương hiệu</span></label></div>
            @foreach($brands as $brand)
                <div class="form-check"><input class="form-check-input" type="radio" name="brand" value="{{ $brand->slug }}" id="brand-{{ $brand->slug }}{{ $suffix }}" @checked(($activeBrand ?? '') === $brand->slug)><label class="form-check-label filter-label" for="brand-{{ $brand->slug }}{{ $suffix }}"><span>{{ $brand->name }}</span><small>{{ $brand->products_count ?? 0 }}</small></label></div>
            @endforeach
        </div>
    </div>

    <div class="filter-card">
        <h2 class="filter-card-title">Khoảng giá</h2>
        <div class="filter-card-body">
            <div class="form-check"><input class="form-check-input" type="radio" name="price" value="" id="price-all{{ $suffix }}" @checked(empty($activePrice))><label class="form-check-label" for="price-all{{ $suffix }}">Tất cả mức giá</label></div>
            @foreach(['under-100' => 'Dưới 100.000₫', '100-300' => '100.000₫–300.000₫', '300-500' => '300.000₫–500.000₫', 'over-500' => 'Trên 500.000₫'] as $value => $label)
                <div class="form-check"><input class="form-check-input" type="radio" name="price" value="{{ $value }}" id="price-{{ $value }}{{ $suffix }}" @checked(($activePrice ?? '') === $value)><label class="form-check-label" for="price-{{ $value }}{{ $suffix }}">{{ $label }}</label></div>
            @endforeach
        </div>
    </div>

    @foreach($filterAttributes as $attribute)
        <div class="filter-card">
            <h2 class="filter-card-title">{{ $attribute->name }}</h2>
            <div class="filter-card-body">
                @foreach($attribute->values as $value)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="attribute_values[{{ $attribute->id }}][]" value="{{ $value->id }}" id="attribute-value-{{ $attribute->id }}-{{ $value->id }}{{ $suffix }}" @checked(in_array($value->id, $activeAttributeValues[$attribute->id] ?? []))>
                        <label class="form-check-label filter-label" for="attribute-value-{{ $attribute->id }}-{{ $value->id }}{{ $suffix }}"><span>{{ $value->value }}</span><small>{{ $value->products_count ?? 0 }}</small></label>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @foreach($optionGroups ?? [] as $option)
        <div class="filter-card">
            <h2 class="filter-card-title">{{ $option->name }}</h2>
            <div class="filter-card-body">
                @foreach($option->values as $value)
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="option_values[{{ $option->id }}][]" value="{{ $value->id }}" id="option-value-{{ $option->id }}-{{ $value->id }}{{ $suffix }}" @checked(in_array($value->id, $activeOptionValues[$option->id] ?? []))><label class="form-check-label filter-label" for="option-value-{{ $option->id }}-{{ $value->id }}{{ $suffix }}"><span>{{ $value->value }}</span><small>{{ $value->products_count ?? 0 }}</small></label></div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="filter-card">
        <h2 class="filter-card-title">Tình trạng hàng</h2>
        <div class="filter-card-body">
            <div class="form-check"><input class="form-check-input" type="radio" name="stock" value="" id="stock-all{{ $suffix }}" @checked(empty($activeStock))><label class="form-check-label" for="stock-all{{ $suffix }}">Tất cả</label></div>
            <div class="form-check"><input class="form-check-input" type="radio" name="stock" value="in-stock" id="stock-in{{ $suffix }}" @checked(($activeStock ?? '') === 'in-stock')><label class="form-check-label" for="stock-in{{ $suffix }}">Còn hàng</label></div>
            <div class="form-check"><input class="form-check-input" type="radio" name="stock" value="preorder" id="stock-preorder{{ $suffix }}" @checked(($activeStock ?? '') === 'preorder')><label class="form-check-label" for="stock-preorder{{ $suffix }}">Nhận đặt trước</label></div>
        </div>
    </div>

    <div @class(['catalog-filter-actions', 'catalog-filter-actions--mobile' => $isMobileFilter])>
        <a class="btn btn-outline-primary" href="{{ route('catalog') }}">Xóa bộ lọc</a>
        <button class="btn btn-primary" type="submit">{{ $isMobileFilter ? 'Xem '.$products->total().' sản phẩm' : 'Áp dụng bộ lọc' }}</button>
    </div>
</form>
