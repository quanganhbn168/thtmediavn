<div class="product-editor-layout combo-editor-layout">
    <div class="product-editor-main">
        <x-card type="primary" :outline="true" title="Thông tin chung" :collapsible="true" class="mb-0 product-editor-info">
            <div class="row g-3">
                <div class="col-lg-8">
                    <x-input id="combo_name" name="name" label="Tên Combo" :value="$combo->name" required />
                </div>
                <div class="col-lg-4">
                    <x-slug name="slug" label="Đường dẫn" :value="$combo->slug" source="combo_name" />
                </div>
                <div class="col-12">
                    <x-textarea name="summary" label="Mô tả ngắn" :value="$combo->summary" rows="3" />
                </div>
                <div class="col-12">
                    <x-tinymce name="description" label="Thông tin Combo" :value="$combo->description" />
                </div>
                <div class="col-12">
                    <x-tinymce name="ingredients" label="Thành phần cấu tạo" :value="$combo->ingredients" />
                </div>
                <div class="col-12">
                    <x-tinymce name="usage" label="Hướng dẫn sử dụng" :value="$combo->usage" />
                </div>
                <div class="col-12">
                    <x-tinymce name="product_notes" label="Lưu ý về Combo" :value="$combo->product_notes" />
                </div>
            </div>
        </x-card>

        <x-card type="secondary" :outline="true" title="Ảnh Combo" :collapsible="true" class="mb-0 product-editor-media">
            <x-product-image-upload
                name="image"
                label="Ảnh Combo"
                subject-label="Combo"
                :existing-images="$combo->getMedia('combo_images')->map(fn ($media) => ['id' => $media->id, 'url' => $media->getUrl()])->all()"
                :max-files="9"
            />
        </x-card>

        <x-card type="info" :outline="true" title="Thành phần Combo" :collapsible="true" class="mb-0 product-editor-composition">
            @if($combo->exists)
                <p class="text-muted mb-3">Combo này có <strong>{{ $combo->items->count() }}</strong> thành phần. Mỗi thành phần chọn sản phẩm, biến thể và số lượng để hệ thống tự trừ tồn kho.</p>
                <a class="btn btn-outline-primary" href="{{ route('admin.combos.components.index', $combo) }}"><i class="bi bi-diagram-3 me-1"></i>Quản lý thành phần</a>
            @else
                <p class="text-muted mb-0">Lưu Combo trước, sau đó thêm sản phẩm thành phần, biến thể và số lượng trừ tồn kho.</p>
            @endif
        </x-card>
    </div>

    <div class="product-editor-sidebar">
        <x-card type="primary" :outline="true" title="Cấu hình Combo" :collapsible="true" class="mb-0 product-editor-config">
            <div class="row g-3">
                <div class="col-12">
                    <x-select name="combo_category_id" label="Danh mục Combo" :options="$categories->pluck('name', 'id')" :selected="old('combo_category_id', $combo->combo_category_id)" />
                </div>
                <div class="col-12">
                    <div class="row g-2">
                        <div class="col-6"><x-input name="price" type="number" label="Giá bán" :value="old('price', $combo->price)" min="1" step="1" required /></div>
                        <div class="col-6"><x-input name="compare_price" type="number" label="Giá niêm yết" :value="old('compare_price', $combo->compare_price)" min="0" step="1" /></div>
                    </div>
                </div>
                <div class="col-12">
                    <x-select name="status" label="Trạng thái" :options="['active' => 'Đang bán', 'draft' => 'Bản nháp', 'archived' => 'Ngừng bán']" :selected="old('status', $combo->status ?: 'active')" />
                </div>
                <div class="col-12">
                    <x-input name="published_at" type="datetime-local" label="Ngày xuất bản" :value="old('published_at', $combo->published_at?->format('Y-m-d\TH:i'))" />
                </div>
                <div class="col-12">
                    <x-input name="sort_order" type="number" label="Thứ tự hiển thị" :value="old('sort_order', $combo->sort_order ?: 0)" min="0" step="1" />
                </div>
                <div class="col-12">
                    <div class="alert alert-info mb-0 small"><i class="bi bi-info-circle me-1"></i>Tồn kho Combo được tính từ các sản phẩm thành phần, không nhập tồn kho riêng cho Combo.</div>
                </div>
                <div class="col-12">
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check mb-2">
                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="combo_is_active" name="is_active" value="1" @checked(old('is_active', $combo->exists ? $combo->is_active : true))>
                        <label class="form-check-label cursor-pointer fw-semibold" for="combo_is_active">Hiển thị Combo</label>
                    </div>
                    <input type="hidden" name="is_featured" value="0">
                    <div class="form-check mb-2">
                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="combo_is_featured" name="is_featured" value="1" @checked(old('is_featured', $combo->is_featured))>
                        <label class="form-check-label cursor-pointer fw-semibold" for="combo_is_featured">Combo nổi bật</label>
                    </div>
                    <input type="hidden" name="allow_preorder" value="0">
                    <div class="form-check">
                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="combo_allow_preorder" name="allow_preorder" value="1" @checked(old('allow_preorder', $combo->allow_preorder))>
                        <label class="form-check-label cursor-pointer fw-semibold" for="combo_allow_preorder">Cho phép đặt trước</label>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card type="secondary" :outline="true" title="Tối ưu SEO" :collapsible="true" class="mb-0">
            <x-input name="seo_title" label="SEO title" :value="old('seo_title', $combo->seo_title)" placeholder="Tiêu đề hiển thị trên công cụ tìm kiếm" />
            <x-textarea name="seo_description" label="SEO description" :value="old('seo_description', $combo->seo_description)" rows="4" placeholder="Mô tả ngắn cho công cụ tìm kiếm" />
        </x-card>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <a class="btn btn-default" href="{{ route('admin.combos.index') }}">Quay lại</a>
    <button class="btn btn-primary" type="submit">{{ $combo->exists ? 'Lưu Combo' : 'Tạo Combo' }}</button>
</div>
