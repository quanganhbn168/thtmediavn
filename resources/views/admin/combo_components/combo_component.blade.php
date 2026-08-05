@php
    $selectedProductId = (int) old('product_id', $comboItem->product_id);
    $selectedVariantId = (int) old('product_variant_id', $comboItem->product_variant_id);
    $variantOptions = $componentProducts->mapWithKeys(fn ($product): array => [
        (string) $product->id => $product->activeVariants->map(fn ($variant): array => [
            'id' => $variant->id,
            'name' => $variant->name ?: 'Mặc định',
            'sku' => $variant->sku,
        ])->values()->all(),
    ]);
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <x-card type="primary" :outline="true" title="Sản phẩm thành phần" :collapsible="true">
            <p class="text-muted small">Chọn sản phẩm hoặc biến thể thực tế. Khi bán Combo, hệ thống sẽ trừ tồn kho theo số lượng dưới đây.</p>
            @error('product_id')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
            <div class="mb-3">
                <label class="form-label" for="combo-component-product">Sản phẩm</label>
                <select class="form-select" id="combo-component-product" name="product_id" data-combo-component-product required>
                    <option value="">Chọn sản phẩm</option>
                    @foreach($componentProducts as $productOption)
                        <option value="{{ $productOption->id }}" @selected($selectedProductId === (int) $productOption->id)>{{ $productOption->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="combo-component-variant">Biến thể</label>
                <select class="form-select" id="combo-component-variant" name="product_variant_id" data-combo-component-variant>
                    <option value="">Chọn sản phẩm trước</option>
                </select>
                @error('product_variant_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </x-card>
    </div>
    <div class="col-lg-4">
        <x-card type="info" :outline="true" title="Số lượng" :collapsible="true">
            <x-input name="quantity" type="number" label="Số lượng trong một Combo" :value="old('quantity', $comboItem->quantity ?: 1)" min="1" max="999" required />
            <x-input name="sort_order" type="number" label="Thứ tự hiển thị" :value="old('sort_order', $comboItem->sort_order ?: 0)" min="0" step="1" />
        </x-card>
    </div>
</div>
<div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-default" href="{{ route('admin.combos.components.index', $combo) }}">Quay lại</a><button class="btn btn-primary" type="submit">{{ $comboItem->exists ? 'Lưu thành phần' : 'Thêm thành phần' }}</button></div>

@pushOnce('js', 'combo-component-editor-js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const productSelect = document.querySelector('[data-combo-component-product]');
    const variantSelect = document.querySelector('[data-combo-component-variant]');
    if (!productSelect || !variantSelect) return;
    const variants = @json($variantOptions);
    const selectedVariant = @json($selectedVariantId);
    const refreshVariants = (selected = '') => {
        const options = variants[productSelect.value] || [];
        variantSelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = options.length > 1 ? 'Chọn biến thể' : 'Mặc định';
        variantSelect.appendChild(placeholder);
        options.forEach((variant) => {
            const option = document.createElement('option');
            option.value = variant.id;
            option.textContent = variant.name + (variant.sku ? ' · ' + variant.sku : '');
            option.selected = String(variant.id) === String(selected);
            variantSelect.appendChild(option);
        });
        if (options.length === 1 && ! selected) variantSelect.value = String(options[0].id);
    };
    refreshVariants(selectedVariant);
    productSelect.addEventListener('change', () => refreshVariants());
});
</script>
@endPushOnce
