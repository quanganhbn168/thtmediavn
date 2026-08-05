@php
    $storedRows = $combo->items->map(fn ($item): array => [
        'product_id' => $item->product_id,
        'product_variant_id' => $item->product_variant_id,
        'quantity' => $item->quantity,
        'sort_order' => $item->sort_order,
    ])->all();
    $rows = collect(old('items', $storedRows))->map(fn ($row): array => (array) $row)->values();
    if ($rows->isEmpty()) {
        $rows = collect([['product_id' => '', 'product_variant_id' => '', 'quantity' => 1, 'sort_order' => 0]]);
    }
    $variantOptions = $componentProducts->mapWithKeys(fn ($product): array => [
        (string) $product->id => $product->activeVariants->map(fn ($variant): array => [
            'id' => $variant->id,
            'name' => $variant->name ?: 'Mặc định',
            'sku' => $variant->sku,
        ])->values()->all(),
    ]);
@endphp

<x-card type="info" :outline="true" title="Sản phẩm trong Combo" :collapsible="true" class="mb-0">
    <div data-combo-item-manager data-variant-options='@json($variantOptions)'>
        <p class="text-muted small mb-3">Chọn sản phẩm/biến thể thực tế và số lượng dùng trong một Combo. Tồn kho sẽ trừ theo các dòng này.</p>
        @error('items')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
        <div class="vstack gap-2" data-combo-rows>
            @foreach($rows as $index => $row)
                @php($selectedProduct = $componentProducts->firstWhere('id', (int) ($row['product_id'] ?? 0)))
                <div class="row g-2 align-items-end" data-combo-row>
                    <div class="col-md-6">
                        <label class="form-label small" for="combo-product-{{ $index }}">Sản phẩm</label>
                        <select class="form-select form-select-sm" id="combo-product-{{ $index }}" name="items[{{ $index }}][product_id]" data-combo-product>
                            <option value="">Chọn sản phẩm</option>
                            @foreach($componentProducts as $productOption)
                                <option value="{{ $productOption->id }}" @selected((int) ($row['product_id'] ?? 0) === (int) $productOption->id)>{{ $productOption->name }}</option>
                            @endforeach
                        </select>
                        @error("items.$index.product_id")<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small" for="combo-variant-{{ $index }}">Biến thể</label>
                        <select class="form-select form-select-sm" id="combo-variant-{{ $index }}" name="items[{{ $index }}][product_variant_id]" data-combo-variant>
                            <option value="">{{ $selectedProduct?->activeVariants?->count() > 1 ? 'Chọn biến thể' : 'Mặc định' }}</option>
                            @foreach($selectedProduct?->activeVariants ?? [] as $variant)
                                <option value="{{ $variant->id }}" @selected((int) ($row['product_variant_id'] ?? 0) === (int) $variant->id)>{{ $variant->name ?: 'Mặc định' }}{{ $variant->sku ? ' · '.$variant->sku : '' }}</option>
                            @endforeach
                        </select>
                        @error("items.$index.product_variant_id")<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small" for="combo-quantity-{{ $index }}">SL</label>
                        <input class="form-control form-control-sm" id="combo-quantity-{{ $index }}" type="number" min="1" max="999" name="items[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? 1 }}">
                    </div>
                    <div class="col-md-1 d-flex">
                        <button class="btn btn-outline-danger btn-sm w-100" type="button" data-remove-combo-item title="Xóa dòng"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            @endforeach
        </div>
        <button class="btn btn-outline-primary btn-sm mt-3" type="button" data-add-combo-item><i class="bi bi-plus-lg me-1"></i>Thêm sản phẩm</button>
    </div>
</x-card>

<template data-combo-item-template>
    <div class="row g-2 align-items-end" data-combo-row>
        <div class="col-md-6">
            <label class="form-label small" for="combo-product-__INDEX__">Sản phẩm</label>
            <select class="form-select form-select-sm" id="combo-product-__INDEX__" name="items[__INDEX__][product_id]" data-combo-product>
                <option value="">Chọn sản phẩm</option>
                @foreach($componentProducts as $productOption)<option value="{{ $productOption->id }}">{{ $productOption->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small" for="combo-variant-__INDEX__">Biến thể</label>
            <select class="form-select form-select-sm" id="combo-variant-__INDEX__" name="items[__INDEX__][product_variant_id]" data-combo-variant><option value="">Chọn sản phẩm trước</option></select>
        </div>
        <div class="col-md-1">
            <label class="form-label small" for="combo-quantity-__INDEX__">SL</label>
            <input class="form-control form-control-sm" id="combo-quantity-__INDEX__" type="number" min="1" max="999" name="items[__INDEX__][quantity]" value="1">
        </div>
        <div class="col-md-1 d-flex"><button class="btn btn-outline-danger btn-sm w-100" type="button" data-remove-combo-item title="Xóa dòng"><i class="bi bi-trash"></i></button></div>
    </div>
</template>

@pushOnce('js', 'combo-item-manager-js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-combo-item-manager]').forEach((root) => {
        const rows = root.querySelector('[data-combo-rows]');
        const template = document.querySelector('template[data-combo-item-template]');
        const variants = JSON.parse(root.dataset.variantOptions || '{}');

        const refreshVariants = (row, selected = '') => {
            const productId = row.querySelector('[data-combo-product]')?.value || '';
            const select = row.querySelector('[data-combo-variant]');
            const options = variants[productId] || [];
            select.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = options.length > 1 ? 'Chọn biến thể' : 'Mặc định';
            select.appendChild(placeholder);
            options.forEach((variant) => {
                const option = document.createElement('option');
                option.value = variant.id;
                option.textContent = variant.name + (variant.sku ? ' · ' + variant.sku : '');
                option.selected = String(variant.id) === String(selected);
                select.appendChild(option);
            });
            if (options.length === 1 && ! selected) select.value = String(options[0].id);
        };

        root.querySelectorAll('[data-combo-row]').forEach((row) => {
            const selected = row.querySelector('[data-combo-variant]')?.value || '';
            refreshVariants(row, selected);
        });
        root.addEventListener('change', (event) => {
            if (event.target.matches('[data-combo-product]')) refreshVariants(event.target.closest('[data-combo-row]'));
        });
        root.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-remove-combo-item]');
            if (remove) {
                const allRows = rows.querySelectorAll('[data-combo-row]');
                if (allRows.length > 1) remove.closest('[data-combo-row]').remove();
                return;
            }
            if (event.target.closest('[data-add-combo-item]')) {
                const index = rows.querySelectorAll('[data-combo-row]').length;
                const html = template.innerHTML.replaceAll('__INDEX__', String(index));
                rows.insertAdjacentHTML('beforeend', html);
            }
        });
    });
});
</script>
@endPushOnce
