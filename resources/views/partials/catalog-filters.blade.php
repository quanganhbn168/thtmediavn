<form action="{{ route('catalog') }}" method="get">
    @if($searchTerm ?? false)<input type="hidden" name="q" value="{{ $searchTerm }}">@endif
    <div class="filter-card">
        <h2 class="filter-card-title">Loại sản phẩm</h2>
        <div class="filter-card-body">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="category" value="" id="category-all{{ $filterSuffix ?? '' }}" {{ empty($activeCategory) ? 'checked' : '' }} onchange="this.form.submit()">
                <label class="form-check-label" for="category-all{{ $filterSuffix ?? '' }}">Tất cả sản phẩm</label>
            </div>
            @foreach($categories as $category)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="category" value="{{ $category['slug'] }}" id="category-{{ $category['slug'] }}{{ $filterSuffix ?? '' }}" {{ ($activeCategory ?? '') === $category['slug'] ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label" for="category-{{ $category['slug'] }}{{ $filterSuffix ?? '' }}">{{ $category['title'] }}</label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="filter-card">
        <h2 class="filter-card-title">Chọn mức giá</h2>
        <div class="filter-card-body">
            @foreach(['under-100' => 'Dưới 100.000₫', '100-300' => 'Từ 100.000₫ - 300.000₫', '300-500' => 'Từ 300.000₫ - 500.000₫', 'over-500' => 'Trên 500.000₫'] as $value => $label)
                <div class="form-check"><input class="form-check-input" type="radio" name="price" value="{{ $value }}" id="price-{{ $value }}{{ $filterSuffix ?? '' }}" @checked(($activePrice ?? '') === $value) onchange="this.form.submit()"><label class="form-check-label" for="price-{{ $value }}{{ $filterSuffix ?? '' }}">{{ $label }}</label></div>
            @endforeach
        </div>
    </div>

    <div class="filter-card">
        <h2 class="filter-card-title">Thương hiệu</h2>
        <div class="filter-card-body">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="brand" value="" id="brand-all{{ $filterSuffix ?? '' }}" {{ empty($activeBrand) ? 'checked' : '' }} onchange="this.form.submit()">
                <label class="form-check-label" for="brand-all{{ $filterSuffix ?? '' }}">Tất cả thương hiệu</label>
            </div>
            @foreach($brands as $brand)
                @php($brandId = $brand->slug)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="brand" value="{{ $brand->slug }}" id="brand-{{ $brandId }}{{ $filterSuffix ?? '' }}" {{ ($activeBrand ?? '') === $brand->slug ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label" for="brand-{{ $brandId }}{{ $filterSuffix ?? '' }}">{{ $brand->name }}</label>
                </div>
            @endforeach
        </div>
    </div>

    @if(!empty($optionGroups ?? []))
        @foreach($optionGroups as $option)
            @php($optionId = $option->id)
            <div class="filter-card">
                <h2 class="filter-card-title">{{ $option->name }}</h2>
                <div class="filter-card-body">
                    @foreach($option->values as $value)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="option_values[{{ $optionId }}][]" value="{{ $value->id }}" id="option-value-{{ $optionId }}-{{ $value->id }}{{ $filterSuffix ?? '' }}" @checked(in_array($value->id, $activeOptionValues[$optionId] ?? [])) onchange="this.form.submit()">
                            <label class="form-check-label" for="option-value-{{ $optionId }}-{{ $value->id }}{{ $filterSuffix ?? '' }}">{{ $value->value }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    @if(!empty($attributeGroups ?? []))
        @foreach($attributeGroups as $attribute)
            @php($attributeId = $attribute->id)
            <div class="filter-card">
                <h2 class="filter-card-title">{{ $attribute->name }}</h2>
                <div class="filter-card-body">
                @foreach($attribute->values as $value)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="attribute_values[{{ $attributeId }}][]" value="{{ $value->id }}" id="attribute-value-{{ $attributeId }}-{{ $value->id }}{{ $filterSuffix ?? '' }}" @checked(in_array($value->id, $activeAttributeValues[$attributeId] ?? [])) onchange="this.form.submit()">
                        <label class="form-check-label" for="attribute-value-{{ $attributeId }}-{{ $value->id }}{{ $filterSuffix ?? '' }}">{{ $value->value }}</label>
                    </div>
                @endforeach
                </div>
            </div>
            @endforeach
    @endif
    <noscript><button class="btn btn-primary w-100" type="submit">Áp dụng bộ lọc</button></noscript>
</form>
