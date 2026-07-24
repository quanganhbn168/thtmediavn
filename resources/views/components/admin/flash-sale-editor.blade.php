@props([
    'sale',
    'editorItems' => [],
    'action',
    'method' => 'POST',
    'submitLabel' => 'Lưu Flash Sale',
    'showSaveAndCreate' => false,
])

@php
    $startsAt = old('starts_at', $sale->starts_at?->format('Y-m-d\\TH:i') ?: now()->format('Y-m-d\\TH:i'));
    $endsAt = old('ends_at', $sale->ends_at?->format('Y-m-d\\TH:i') ?: now()->addDay()->format('Y-m-d\\TH:i'));
    $isActive = old('is_active', $sale->exists ? $sale->is_active : true);
@endphp

<form
    id="admin-save-form"
    action="{{ $action }}"
    method="post"
    x-data="flashSaleEditor(@js($editorItems), @js(route('admin.flash-sales.products')))"
    x-init="init()"
>
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <x-card type="primary" :outline="true" title="Thông tin chương trình">
                <div class="row g-3">
                    <div class="col-12">
                        <x-input name="name" label="Tên chương trình" :value="old('name', $sale->name)" required />
                    </div>
                    <div class="col-md-6">
                        <x-input name="starts_at" type="datetime-local" label="Thời gian bắt đầu" :value="$startsAt" required />
                    </div>
                    <div class="col-md-6">
                        <x-input name="ends_at" type="datetime-local" label="Thời gian kết thúc" :value="$endsAt" required />
                    </div>
                </div>
            </x-card>
        </div>
        <div class="col-xl-4">
            <x-card type="info" :outline="true" title="Trạng thái chương trình">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check form-switch mt-1">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="flash-sale-is-active" value="1" @checked($isActive)>
                    <label class="form-check-label fw-semibold" for="flash-sale-is-active">Kích hoạt khi đến thời gian áp dụng</label>
                </div>
                <p class="small text-body-secondary mb-0 mt-3">Giá sale chỉ hiển thị trong khoảng thời gian đã đặt.</p>
            </x-card>
        </div>
    </div>

    <x-card type="secondary" :outline="true" title="Sản phẩm tham gia">
        <x-slot:tools>
            <span class="badge text-bg-light me-2" x-text="`${items.length} biến thể đã chọn`"></span>
            <button class="btn btn-primary btn-sm" type="button" @click="openPicker()">
                <i class="bi bi-plus-lg me-1"></i>Thêm sản phẩm
            </button>
        </x-slot:tools>

        <div class="flash-sale-editor-note mb-3">
            <i class="bi bi-info-circle"></i>
            <span>Tìm sản phẩm trong hộp chọn. Danh sách đã chọn luôn được giữ nguyên khi tìm kiếm hoặc chuyển trang; số lượng Flash Sale được nhập tùy ý.</span>
        </div>

        @if($errors->has('items'))
            <div class="alert alert-danger py-2">{{ $errors->first('items') }}</div>
        @endif
        @if($errors->has('items.*'))
            <div class="alert alert-danger py-2">
                <ul class="mb-0 ps-3">
                    @foreach(collect($errors->get('items.*'))->flatten()->unique() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flash-sale-empty text-center py-5" x-show="items.length === 0" x-cloak>
            <i class="bi bi-bag-plus"></i>
            <h5 class="mt-3 mb-1">Chưa có sản phẩm nào</h5>
            <p class="text-body-secondary mb-3">Chọn sản phẩm và biến thể muốn áp dụng giá Flash Sale.</p>
            <button class="btn btn-primary" type="button" @click="openPicker()"><i class="bi bi-plus-lg me-1"></i>Chọn sản phẩm</button>
        </div>

        <div class="flash-sale-items" x-show="items.length > 0" x-cloak>
            <template x-for="(item, index) in items" :key="item.key">
                <article class="flash-sale-item">
                    <div class="flash-sale-item-head">
                        <div class="flash-sale-editor-product">
                            <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                            <img :src="item.product_image" alt="" width="58" height="58">
                            <div class="min-w-0">
                                <span class="flash-sale-item-kicker" x-text="`Sản phẩm #${index + 1}`"></span>
                                <strong class="d-block flash-sale-product-name" x-text="item.product_name"></strong>
                            </div>
                        </div>
                        <button class="btn btn-outline-danger btn-sm" type="button" @click="removeItem(index)" aria-label="Bỏ sản phẩm"><i class="bi bi-trash3 me-1"></i>Bỏ</button>
                    </div>

                    <div class="flash-sale-item-controls">
                        <div class="flash-sale-control">
                            <label>Biến thể áp dụng</label>
                            <select class="form-select" :name="`items[${index}][product_variant_id]`" x-model.number="item.product_variant_id" @change="updateVariant(item)">
                                <template x-for="variant in item.variants" :key="variant.id">
                                    <option :value="variant.id" x-text="`${variant.name} · ${formatMoney(variant.price)}`"></option>
                                </template>
                            </select>
                        </div>
                        <div class="flash-sale-control">
                            <label>Giá gốc</label>
                            <div class="flash-sale-value" x-text="formatMoney(item.base_price)"></div>
                        </div>
                        <div class="flash-sale-control">
                            <label>Mức giảm</label>
                            <div class="input-group">
                                <input class="form-control" type="number" min="0.01" step="0.01" :max="item.discount_type === 'percent' ? 100 : Math.max(item.base_price - 0.01, 0.01)" :name="`items[${index}][discount_value]`" x-model.number="item.discount_value" @input="recalculate(item)">
                                <select class="form-select" :name="`items[${index}][discount_type]`" x-model="item.discount_type" @change="recalculate(item)">
                                    <option value="percent">%</option>
                                    <option value="fixed">₫</option>
                                </select>
                            </div>
                        </div>
                        <div class="flash-sale-control flash-sale-price-control">
                            <label>Giá Flash Sale</label>
                            <div class="flash-sale-price" x-text="formatMoney(salePrice(item))"></div>
                            <small x-text="`Tiết kiệm ${formatMoney(savings(item))}`"></small>
                        </div>
                        <div class="flash-sale-control">
                            <label>Số lượng chạy sale</label>
                            <div class="input-group">
                                <input class="form-control" type="number" min="1" step="1" :name="`items[${index}][quantity]`" x-model.number="item.quantity">
                                <span class="input-group-text">SP</span>
                            </div>
                            <small x-show="item.sold > 0" x-text="item.sold > 0 ? `Đã bán: ${item.sold}` : ''"></small>
                        </div>
                    </div>
                </article>
            </template>
        </div>
    </x-card>

    <div class="d-flex justify-content-end gap-2 mt-3 mb-4">
        <a class="btn btn-default" href="{{ route('admin.flash-sales.index') }}">Quay lại</a>
        @if($showSaveAndCreate)
            <button type="submit" class="btn btn-outline-primary" name="submit_action" value="save_and_create">Lưu và thêm mới</button>
        @endif
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>

    <div class="modal fade" id="flash-sale-product-picker" tabindex="-1" aria-labelledby="flash-sale-product-picker-title" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="flash-sale-product-picker-title">Chọn sản phẩm Flash Sale</h5>
                        <p class="small text-body-secondary mb-0">Một sản phẩm có thể thêm nhiều biến thể, mỗi biến thể chỉ xuất hiện một lần.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input class="form-control" type="search" x-model="search" @input.debounce.350ms="loadProducts(1)" placeholder="Tìm theo tên sản phẩm hoặc slug" autocomplete="off">
                    </div>

                    <template x-if="pickerError">
                        <div class="alert alert-danger py-2" x-text="pickerError"></div>
                    </template>
                    <div class="text-center py-5" x-show="isLoading"><span class="spinner-border text-primary" role="status"></span></div>
                    <div class="row g-3" x-show="!isLoading && pickerProducts.length > 0">
                        <template x-for="product in pickerProducts" :key="product.product_id">
                            <div class="col-md-6 col-xl-4">
                                <div class="card h-100 flash-sale-picker-product">
                                    <div class="card-body d-flex gap-3">
                                        <img :src="product.product_image" alt="" width="58" height="58">
                                        <div class="min-w-0 flex-grow-1">
                                            <strong class="d-block text-truncate" x-text="product.product_name"></strong>
                                            <small class="text-body-secondary d-block mt-1" x-text="`${product.variants.length} biến thể đang bán`"></small>
                                            <small class="text-body-secondary d-block" x-text="`Từ ${formatMoney(product.variants[0].price)}`"></small>
                                            <button class="btn btn-primary btn-sm mt-2" type="button" :disabled="availableVariants(product).length === 0" @click="addProduct(product)">
                                                <i class="bi bi-plus-lg me-1"></i><span x-text="availableVariants(product).length ? 'Thêm biến thể' : 'Đã chọn hết'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="text-center text-body-secondary py-5" x-show="!isLoading && pickerProducts.length === 0 && !pickerError">Không tìm thấy sản phẩm phù hợp.</div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <small class="text-body-secondary" x-text="pickerMeta.total ? `${pickerMeta.total} sản phẩm` : ''"></small>
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary" type="button" @click="loadProducts(pickerMeta.current_page - 1)" :disabled="isLoading || pickerMeta.current_page <= 1">Trước</button>
                        <button class="btn btn-outline-secondary" type="button" @click="loadProducts(pickerMeta.current_page + 1)" :disabled="isLoading || pickerMeta.current_page >= pickerMeta.last_page">Sau</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@pushOnce('css', 'flash-sale-editor-css')
<style>
    [x-cloak] { display: none !important; }
    .flash-sale-editor-note { display: flex; gap: .6rem; padding: .75rem .9rem; color: var(--bs-secondary-color); background: var(--bs-tertiary-bg); border-radius: .5rem; font-size: .875rem; }
    .flash-sale-editor-note i { color: var(--bs-primary); }
    .flash-sale-empty > i { font-size: 2.5rem; color: var(--bs-secondary-color); }
    .flash-sale-items { display: grid; gap: .9rem; }
    .flash-sale-item { overflow: hidden; border: 1px solid var(--bs-border-color); border-radius: .7rem; background: var(--bs-body-bg); box-shadow: 0 1px 2px rgba(0, 0, 0, .03); }
    .flash-sale-item-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .85rem 1rem; border-bottom: 1px solid var(--bs-border-color); background: var(--bs-tertiary-bg); }
    .flash-sale-editor-product { display: flex; align-items: center; gap: .85rem; min-width: 0; }
    .flash-sale-editor-product img, .flash-sale-picker-product img { flex: 0 0 auto; object-fit: cover; border: 1px solid var(--bs-border-color); border-radius: .45rem; background: #fff; }
    .flash-sale-editor-product img { width: 58px; height: 58px; }
    .flash-sale-picker-product img { width: 58px; height: 58px; }
    .flash-sale-item-kicker { display: block; margin-bottom: .2rem; color: var(--bs-secondary-color); font-size: .72rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .flash-sale-product-name { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-height: 1.35; }
    .flash-sale-item-controls { display: grid; grid-template-columns: minmax(220px, 2.1fr) minmax(120px, 1fr) minmax(165px, 1.25fr) minmax(155px, 1.2fr) minmax(150px, 1fr); gap: .75rem; align-items: end; padding: 1rem; }
    .flash-sale-control label { display: block; margin-bottom: .4rem; color: var(--bs-secondary-color); font-size: .72rem; font-weight: 700; letter-spacing: .035em; text-transform: uppercase; }
    .flash-sale-control .input-group .form-select { max-width: 68px; }
    .flash-sale-control small { display: block; min-height: 1.1rem; margin-top: .35rem; color: var(--bs-secondary-color); font-size: .76rem; }
    .flash-sale-value { min-height: 38px; padding: .48rem .7rem; border: 1px solid var(--bs-border-color); border-radius: .375rem; background: var(--bs-tertiary-bg); font-weight: 700; white-space: nowrap; }
    .flash-sale-price-control { padding: .65rem .75rem; border: 1px solid rgba(var(--bs-danger-rgb), .2); border-radius: .5rem; background: rgba(var(--bs-danger-rgb), .05); }
    .flash-sale-price-control label { color: var(--bs-danger); }
    .flash-sale-price { color: var(--bs-danger); font-size: 1.1rem; font-weight: 800; white-space: nowrap; }
    .flash-sale-price-control small { color: var(--bs-success); }
    .min-w-0 { min-width: 0; }
    @media (max-width: 1199.98px) { .flash-sale-item-controls { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 767.98px) { .flash-sale-editor-note { align-items: flex-start; } .flash-sale-item-head { align-items: flex-start; } .flash-sale-item-controls { grid-template-columns: 1fr; } }
</style>
@endPushOnce

@pushOnce('js', 'flash-sale-editor-js')
<script>
    window.flashSaleEditor = function (initialItems, pickerUrl) {
        return {
            items: Array.isArray(initialItems) ? initialItems : [],
            pickerUrl,
            search: '',
            pickerProducts: [],
            pickerMeta: { current_page: 1, last_page: 1, total: 0 },
            isLoading: false,
            pickerError: '',
            pickerModal: null,

            init() {
                this.items = this.items.map((item) => this.prepareItem(item));
                this.pickerModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('flash-sale-product-picker'));
            },

            prepareItem(item) {
                const prepared = {
                    ...item,
                    key: item.key || `${item.product_id}-${item.product_variant_id}-${Date.now()}-${Math.random()}`,
                    product_id: Number(item.product_id),
                    product_variant_id: Number(item.product_variant_id),
                    base_price: Number(item.base_price || 0),
                    discount_value: Number(item.discount_value || 0),
                    quantity: Math.max(1, Number(item.quantity || 1)),
                    sold: Number(item.sold || 0),
                    variants: (item.variants || []).map((variant) => ({ ...variant, id: Number(variant.id), price: Number(variant.price), stock: Number(variant.stock) })),
                };
                this.updateVariant(prepared);
                return prepared;
            },

            openPicker() {
                this.pickerModal.show();
                if (this.pickerProducts.length === 0) this.loadProducts(1);
            },

            async loadProducts(page = 1) {
                if (page < 1 || this.isLoading) return;
                this.isLoading = true;
                this.pickerError = '';
                try {
                    const url = new URL(this.pickerUrl, window.location.origin);
                    url.searchParams.set('page', String(page));
                    url.searchParams.set('q', this.search);
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('Không thể tải danh sách sản phẩm.');
                    const payload = await response.json();
                    this.pickerProducts = payload.data || [];
                    this.pickerMeta = payload.meta || this.pickerMeta;
                } catch (error) {
                    this.pickerError = error.message || 'Không thể tải danh sách sản phẩm.';
                } finally {
                    this.isLoading = false;
                }
            },

            availableVariants(product) {
                const selected = new Set(this.items.filter((item) => Number(item.product_id) === Number(product.product_id)).map((item) => Number(item.product_variant_id)));
                return (product.variants || []).filter((variant) => !selected.has(Number(variant.id)));
            },

            addProduct(product) {
                const variant = this.availableVariants(product)[0];
                if (!variant) return;
                this.items.push(this.prepareItem({
                    ...product,
                    key: `${product.product_id}-${variant.id}-${Date.now()}-${Math.random()}`,
                    product_variant_id: Number(variant.id),
                    base_price: Number(variant.price),
                    discount_type: 'percent',
                    discount_value: 10,
                    quantity: 100,
                    sold: 0,
                }));
            },

            selectedVariant(item) {
                return (item.variants || []).find((variant) => Number(variant.id) === Number(item.product_variant_id)) || null;
            },

            updateVariant(item) {
                const variant = this.selectedVariant(item);
                if (!variant) return;
                item.base_price = Number(variant.price || 0);
                this.recalculate(item);
            },

            recalculate(item) {
                const maxDiscount = item.discount_type === 'percent' ? 100 : Math.max(Number(item.base_price) - 0.01, 0.01);
                item.discount_value = Math.max(0.01, Math.min(Number(item.discount_value || 0), maxDiscount));
            },

            salePrice(item) {
                const price = Number(item.base_price || 0);
                const discount = Number(item.discount_value || 0);
                return item.discount_type === 'percent'
                    ? Math.max(0, price * (1 - discount / 100))
                    : Math.max(0, price - discount);
            },

            savings(item) {
                return Math.max(0, Number(item.base_price || 0) - this.salePrice(item));
            },

            formatMoney(value) {
                return `${new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 }).format(Math.round(Number(value || 0)))}₫`;
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },
        };
    };
</script>
@endPushOnce
