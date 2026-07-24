@props([
    'product',
    'options',
    'variants' => [],
])

@php
    $optionData = $options->map(fn ($option) => [
        'id' => (int) $option->id,
        'name' => $option->name,
        'slug' => $option->slug,
        'values' => $option->values->map(fn ($value) => [
            'id' => (int) $value->id,
            'name' => $value->value,
            'slug' => $value->slug,
        ])->values()->all(),
    ])->values();
    $selectedOptionIds = collect(old('option_ids', $product->options->pluck('id')->all()))
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->take(3)
        ->values();
    $valueToOption = $optionData->flatMap(fn ($option) => collect($option['values'])->mapWithKeys(
        fn ($value) => [$value['id'] => $option['id']]
    ));
    $selectedValuesByOption = collect(old('option_value_ids', []));
    if ($selectedValuesByOption->isEmpty()) {
        $selectedValuesByOption = collect($variants)
            ->flatMap(fn ($variant) => $variant['value_ids'] ?? [])
            ->unique()
            ->groupBy(fn ($valueId) => $valueToOption[(int) $valueId] ?? 0);
    }
    $hasVariants = (bool) old('has_variants', $selectedOptionIds->isNotEmpty());
    $attributes = $selectedOptionIds->map(fn ($optionId) => [
        'optionId' => (int) $optionId,
        'valueIds' => collect($selectedValuesByOption->get($optionId, []))->map(fn ($id) => (int) $id)->values()->all(),
    ])->values();
    if ($hasVariants && $attributes->isEmpty()) {
        $attributes->push(['optionId' => null, 'valueIds' => []]);
    }
    $variantData = collect($variants)->map(function ($variant) {
        $price = $variant['price'] ?? null;
        $comparePrice = $variant['compare_price'] ?? null;
        $listPrice = $variant['list_price'] ?? ($comparePrice ?: $price);
        $salePrice = $variant['sale_price'] ?? ($comparePrice && (float) $price < (float) $comparePrice ? $price : '');

        return [
            'id' => $variant['id'] ?? null,
            'name' => $variant['name'] ?? 'Mặc định',
            'sku' => $variant['sku'] ?? '',
            'barcode' => $variant['barcode'] ?? '',
            'list_price' => $listPrice ?? '',
            'sale_price' => $salePrice ?? '',
            'stock' => $variant['stock'] ?? 0,
            'weight' => $variant['weight'] ?? '',
            'value_ids' => collect($variant['value_ids'] ?? [])->map(fn ($id) => (int) $id)->values()->all(),
            'is_default' => (bool) ($variant['is_default'] ?? false),
            'is_active' => (bool) ($variant['is_active'] ?? true),
            'selected' => false,
        ];
    })->values();
    $defaultVariant = $variantData->firstWhere('is_default', true) ?? $variantData->first() ?? [
        'id' => null,
        'name' => 'Mặc định',
        'sku' => '',
        'barcode' => '',
        'list_price' => '',
        'sale_price' => '',
        'stock' => 0,
        'weight' => '',
        'is_active' => true,
    ];
@endphp

<div
    class="product-editor-variants"
    x-data="productVariantManager(@js([
        'hasVariants' => $hasVariants,
        'options' => $optionData,
        'attributes' => $attributes,
        'variants' => $variantData,
    ]))"
>
<x-card
    type="info"
    :outline="true"
    title="Thông tin bán hàng"
    class="mb-0"
>
    <x-slot:tools>
        <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
            <input type="hidden" name="has_variants" value="0">
            <input
                class="form-check-input mt-0"
                type="checkbox"
                role="switch"
                id="product_has_variants"
                name="has_variants"
                value="1"
                x-model="hasVariants"
                x-on:change="onVariantToggle"
            >
            <label class="form-check-label fw-semibold" for="product_has_variants">Sản phẩm có biến thể</label>
        </div>
    </x-slot:tools>

    <template x-if="!hasVariants">
        <div>
            <input type="hidden" name="variants[0][id]" value="{{ $defaultVariant['id'] }}">
            <input type="hidden" name="variants[0][name]" value="Mặc định">
            <input type="hidden" name="variants[0][is_default]" value="1">
            <input type="hidden" name="variants[0][is_active]" value="1">

            <div class="row g-3 variant-simple-grid">
                <div class="col-12">
                    <x-money-input
                        name="variants[0][list_price]"
                        label="Giá niêm yết"
                        :value="$defaultVariant['list_price']"
                        :min="0"
                        required
                    />
                </div>
                <div class="col-12">
                    <x-money-input
                        name="variants[0][sale_price]"
                        label="Giá giảm"
                        :value="$defaultVariant['sale_price']"
                        :min="0"
                        placeholder="Không giảm giá"
                    />
                </div>
                <div class="col-12">
                    <x-input name="variants[0][sku]" label="SKU" :value="$defaultVariant['sku']" placeholder="Tự sinh nếu để trống" />
                </div>
                <div class="col-12">
                    <x-input name="variants[0][barcode]" label="Mã vạch" :value="$defaultVariant['barcode']" />
                </div>
                <div class="col-12">
                    <x-input name="variants[0][stock]" type="number" label="Tồn kho" :value="$defaultVariant['stock']" min="0" required />
                </div>
                <input type="hidden" name="variants[0][weight]" value="{{ $defaultVariant['weight'] }}">
            </div>
        </div>
    </template>

    <template x-if="hasVariants">
        <div>
            <div class="variant-builder rounded border p-3 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div>
                        <h6 class="mb-1">Thuộc tính tạo biến thể</h6>
                        <p class="small text-body-secondary mb-0">Chọn tối đa 3 thuộc tính và các giá trị cần bán.</p>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" x-on:click="addAttribute" x-bind:disabled="attributes.length >= 3 || attributes.length >= options.length">
                        <i class="bi bi-plus-lg me-1"></i>Thêm thuộc tính
                    </button>
                </div>

                <template x-for="(attribute, index) in attributes" :key="attribute.uid">
                    <div class="row g-3 align-items-end border-top pt-3 mt-3 first-attribute-row">
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Thuộc tính <span x-text="index + 1"></span></label>
                            <select class="form-select" x-bind:data-option-index="index">
                            </select>
                        </div>
                        <div class="col-lg-7">
                            <label class="form-label fw-semibold">Giá trị</label>
                            <select class="form-select" multiple x-bind:data-value-index="index" x-bind:disabled="!attribute.optionId">
                            </select>
                        </div>
                        <div class="col-lg-1 text-end">
                            <button type="button" class="btn btn-outline-danger" title="Xóa thuộc tính" x-on:click="removeAttribute(index)" x-bind:disabled="attributes.length === 1">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </template>

                <template x-for="attribute in attributes" :key="`option-${attribute.uid}`">
                    <div>
                        <input type="hidden" name="option_ids[]" :value="attribute.optionId" x-bind:disabled="!attribute.optionId">
                        <template x-for="valueId in attribute.valueIds" :key="`value-${attribute.uid}-${valueId}`">
                            <input type="hidden" :name="`option_value_ids[${attribute.optionId}][]`" :value="valueId">
                        </template>
                    </div>
                </template>

                <div class="row g-3 align-items-end mt-2">
                    <div class="col-lg-3 col-md-6">
                        <x-money-input name="variant_defaults[list_price]" label="Giá niêm yết mặc định" :value="$defaultVariant['list_price']" :min="0" />
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-money-input name="variant_defaults[sale_price]" label="Giá giảm mặc định" :value="$defaultVariant['sale_price']" :min="0" />
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <x-input name="variant_defaults[stock]" type="number" label="Tồn kho mặc định" :value="$defaultVariant['stock']" min="0" />
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <button type="button" class="btn btn-primary" x-on:click="generateVariants">
                            <i class="bi bi-diagram-3 me-1"></i>Tạo biến thể
                            <span class="badge text-bg-light ms-1" x-text="combinationCount"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div x-show="variants.length > 0" x-cloak>
                <div class="variant-bulk-toolbar d-flex flex-wrap align-items-end gap-2 p-3 rounded border mb-3" x-show="selectedCount > 0" x-cloak>
                    <span class="badge text-bg-primary align-self-center"><span x-text="selectedCount"></span> đã chọn</span>
                    <div>
                        <label class="form-label small mb-1">Trường cập nhật</label>
                        <select class="form-select form-select-sm" x-model="bulkField">
                            <option value="list_price">Giá niêm yết</option>
                            <option value="sale_price">Giá giảm</option>
                            <option value="stock">Tồn kho</option>
                            <option value="is_active">Trạng thái bán</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small mb-1">Giá trị</label>
                        <input class="form-control form-control-sm" type="number" min="0" x-model="bulkValue" x-bind:placeholder="bulkField === 'is_active' ? '1 bật, 0 tắt' : 'Nhập giá trị'">
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" x-on:click="applyBulk">Áp dụng</button>
                </div>

                <div class="table-responsive border rounded">
                    <table class="table table-hover align-middle mb-0 variant-table">
                        <thead>
                            <tr>
                                <th class="text-center"><input type="checkbox" class="form-check-input" x-on:change="toggleAll($event.target.checked)" :checked="allSelected"></th>
                                <th>Biến thể</th>
                                <th>SKU</th>
                                <th style="min-width:150px">Giá niêm yết</th>
                                <th style="min-width:150px">Giá giảm</th>
                                <th style="width:110px">Tồn kho</th>
                                <th class="text-center">Bán</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(variant, index) in variants" :key="variant.uid">
                                <tr>
                                    <td class="text-center"><input type="checkbox" class="form-check-input" x-model="variant.selected"></td>
                                    <td>
                                        <strong x-text="variant.name"></strong>
                                        <input type="hidden" :name="`variants[${index}][id]`" :value="variant.id || ''">
                                        <input type="hidden" :name="`variants[${index}][name]`" :value="variant.name">
                                        <input type="hidden" :name="`variants[${index}][is_default]`" :value="index === 0 ? 1 : 0">
                                        <template x-for="valueId in variant.value_ids" :key="`${variant.uid}-${valueId}`">
                                            <input type="hidden" :name="`variants[${index}][value_ids][]`" :value="valueId">
                                        </template>
                                    </td>
                                    <td><input class="form-control form-control-sm" :name="`variants[${index}][sku]`" x-model="variant.sku"></td>
                                    <td><input class="form-control form-control-sm" type="number" min="0" :name="`variants[${index}][list_price]`" x-model="variant.list_price" required></td>
                                    <td><input class="form-control form-control-sm" type="number" min="0" :name="`variants[${index}][sale_price]`" x-model="variant.sale_price"></td>
                                    <td><input class="form-control form-control-sm" type="number" min="0" :name="`variants[${index}][stock]`" x-model="variant.stock"></td>
                                    <td class="text-center">
                                        <input type="hidden" :name="`variants[${index}][is_active]`" value="0">
                                        <input type="checkbox" class="form-check-input" :name="`variants[${index}][is_active]`" value="1" x-model="variant.is_active">
                                    </td>
                                    <td class="text-end"><button type="button" class="btn btn-default btn-sm text-danger" x-on:click="removeVariant(index)"><i class="bi bi-trash"></i></button></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center py-4 border rounded" x-show="variants.length === 0" x-cloak>
                <i class="bi bi-diagram-3 fs-2 text-body-secondary"></i>
                <p class="mb-0 mt-2 text-body-secondary">Chọn thuộc tính và bấm “Tạo biến thể”.</p>
            </div>
        </div>
    </template>
</x-card>
</div>

@pushOnce('css', 'product-variant-manager-css')
<style>
    [x-cloak] { display: none !important; }
    .product-editor-layout { display: grid; grid-template-columns: minmax(0, 9fr) minmax(270px, 3fr); grid-template-areas: "media media" "info sidebar" "info variants"; gap: 1rem; align-items: start; }
    .product-editor-layout.has-product-variants { grid-template-areas: "media media" "info sidebar" "variants variants"; }
    .product-editor-main { display: contents; }
    .product-editor-media { grid-area: media; }
    .product-editor-info { grid-area: info; }
    .product-editor-sidebar { grid-area: sidebar; }
    .product-editor-variants { grid-area: variants; }
    .variant-builder { background: var(--bs-tertiary-bg); }
    .first-attribute-row:first-of-type { margin-top: 0 !important; border-top: 0 !important; padding-top: 0 !important; }
    .variant-table th { white-space: nowrap; }
    @media (max-width: 991.98px) {
        .product-editor-layout { grid-template-columns: 1fr; grid-template-areas: "media" "info" "sidebar" "variants"; }
    }
</style>
@endPushOnce

@pushOnce('js', 'product-variant-manager-js')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productVariantManager', (initial) => ({
        hasVariants: Boolean(initial.hasVariants),
        options: initial.options || [],
        attributes: (initial.attributes || []).map((item) => ({ ...item, uid: crypto.randomUUID() })),
        variants: (initial.variants || []).map((item) => ({ ...item, uid: crypto.randomUUID(), selected: false })),
        bulkField: 'list_price',
        bulkValue: '',

        init() {
            if (this.hasVariants && this.attributes.length === 0) this.addAttribute();
            this.syncLayoutMode();
            this.$nextTick(() => this.initSelects());
            this.$root.closest('form')?.addEventListener('submit', (event) => this.validateBeforeSubmit(event));
        },

        get selectedCount() { return this.variants.filter((item) => item.selected).length; },
        get allSelected() { return this.variants.length > 0 && this.variants.every((item) => item.selected); },
        get combinationCount() {
            if (!this.attributes.length || this.attributes.some((item) => !item.optionId || !item.valueIds.length)) return 0;
            return this.attributes.reduce((total, item) => total * item.valueIds.length, 1);
        },

        onVariantToggle() {
            if (this.hasVariants && this.attributes.length === 0) this.addAttribute();
            this.syncLayoutMode();
            this.$nextTick(() => this.initSelects());
        },

        syncLayoutMode() {
            this.$root.closest('.product-editor-layout')?.classList.toggle('has-product-variants', this.hasVariants);
        },

        addAttribute() {
            if (this.attributes.length >= 3 || this.attributes.length >= this.options.length) return;
            this.attributes.push({ optionId: null, valueIds: [], uid: crypto.randomUUID() });
            this.$nextTick(() => this.initSelects());
        },

        removeAttribute(index) {
            if (this.attributes.length <= 1) return;
            this.attributes.splice(index, 1);
            this.$nextTick(() => this.initSelects(true));
        },

        availableOptions(index) {
            const used = this.attributes.map((item, itemIndex) => itemIndex === index ? null : Number(item.optionId)).filter(Boolean);
            return this.options.filter((option) => !used.includes(Number(option.id)));
        },

        valuesFor(optionId) {
            return this.options.find((option) => Number(option.id) === Number(optionId))?.values || [];
        },

        initSelects(rebuild = false) {
            if (typeof TomSelect === 'undefined') {
                window.setTimeout(() => this.initSelects(rebuild), 50);
                return;
            }
            this.$root.querySelectorAll('[data-option-index]').forEach((element) => {
                if (element.tomselect && rebuild) element.tomselect.destroy();
                if (element.tomselect) return;
                const index = Number(element.dataset.optionIndex);
                const attribute = this.attributes[index];
                new TomSelect(element, {
                    placeholder: 'Chọn thuộc tính',
                    allowEmptyOption: true,
                    valueField: 'value',
                    labelField: 'text',
                    searchField: 'text',
                    options: this.availableOptions(index).map((option) => ({
                        value: String(option.id),
                        text: option.name,
                    })),
                    items: attribute.optionId ? [String(attribute.optionId)] : [],
                    onChange: (value) => {
                        this.attributes[index].optionId = value ? Number(value) : null;
                        this.attributes[index].valueIds = [];
                        this.$nextTick(() => this.initSelects(true));
                    },
                });
            });

            this.$root.querySelectorAll('[data-value-index]').forEach((element) => {
                if (element.tomselect && rebuild) element.tomselect.destroy();
                if (element.tomselect) return;
                const index = Number(element.dataset.valueIndex);
                const attribute = this.attributes[index];
                new TomSelect(element, {
                    plugins: ['remove_button'],
                    placeholder: 'Chọn giá trị',
                    valueField: 'value',
                    labelField: 'text',
                    searchField: 'text',
                    options: this.valuesFor(attribute.optionId).map((value) => ({
                        value: String(value.id),
                        text: value.name,
                    })),
                    items: attribute.valueIds.map(String),
                    onChange: (values) => {
                        this.attributes[index].valueIds = (values || []).map(Number);
                    },
                });
            });
        },

        generateVariants() {
            if (this.combinationCount === 0) {
                Swal.fire('Chưa đủ thuộc tính', 'Mỗi thuộc tính phải có ít nhất một giá trị.', 'warning');
                return;
            }
            if (this.combinationCount > 100) {
                Swal.fire('Quá nhiều biến thể', 'Một sản phẩm chỉ được tạo tối đa 100 tổ hợp.', 'warning');
                return;
            }

            const defaults = {
                list_price: this.$root.querySelector('[name="variant_defaults[list_price]"]')?.value || '',
                sale_price: this.$root.querySelector('[name="variant_defaults[sale_price]"]')?.value || '',
                stock: this.$root.querySelector('[name="variant_defaults[stock]"]')?.value || 0,
            };
            const current = new Map(this.variants.map((variant) => [this.signature(variant.value_ids), variant]));
            const combinations = this.attributes.reduce((rows, attribute) => {
                return rows.flatMap((row) => attribute.valueIds.map((valueId) => [...row, Number(valueId)]));
            }, [[]]);
            const baseSku = this.slugify(document.querySelector('[name="slug"]')?.value || document.querySelector('[name="name"]')?.value || 'SP');

            this.variants = combinations.map((valueIds, index) => {
                const signature = this.signature(valueIds);
                if (current.has(signature)) return { ...current.get(signature), selected: false };
                const values = valueIds.map((id) => this.valueById(id)).filter(Boolean);
                return {
                    uid: crypto.randomUUID(), id: null, value_ids: valueIds,
                    name: values.map((value) => value.name).join(' / '),
                    sku: `${baseSku}-${values.map((value) => value.slug).join('-')}`.toUpperCase(),
                    barcode: '', list_price: defaults.list_price, sale_price: defaults.sale_price,
                    stock: defaults.stock, weight: '', is_default: index === 0, is_active: true, selected: false,
                };
            });
        },

        valueById(id) {
            return this.options.flatMap((option) => option.values).find((value) => Number(value.id) === Number(id));
        },
        signature(ids) { return [...(ids || [])].map(Number).sort((a, b) => a - b).join('-'); },
        slugify(value) { return String(value).normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').replace(/[^a-zA-Z0-9]+/g, '-').replace(/^-|-$/g, '') || 'SP'; },
        toggleAll(checked) { this.variants.forEach((item) => item.selected = checked); },
        removeVariant(index) { this.variants.splice(index, 1); },

        applyBulk() {
            if (this.bulkValue === '') return;
            this.variants.filter((item) => item.selected).forEach((item) => {
                item[this.bulkField] = this.bulkField === 'is_active' ? Number(this.bulkValue) === 1 : this.bulkValue;
            });
        },

        validateBeforeSubmit(event) {
            const rows = this.hasVariants ? this.variants : [{
                list_price: this.$root.querySelector('[name="variants[0][list_price]"]')?.value,
                sale_price: this.$root.querySelector('[name="variants[0][sale_price]"]')?.value,
            }];
            if (this.hasVariants && (!this.attributes.length || !this.variants.length)) {
                event.preventDefault();
                Swal.fire('Chưa tạo biến thể', 'Hãy chọn thuộc tính và tạo ít nhất một biến thể.', 'warning');
                return;
            }
            const invalid = rows.find((row) => !Number(row.list_price) || (row.sale_price !== '' && Number(row.sale_price) >= Number(row.list_price)));
            if (invalid) {
                event.preventDefault();
                Swal.fire('Giá chưa hợp lệ', 'Giá niêm yết là bắt buộc và giá giảm phải nhỏ hơn giá niêm yết.', 'warning');
            }
        },
    }));
});
</script>
@endPushOnce
