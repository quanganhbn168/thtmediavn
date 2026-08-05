<div class="product-editor-layout">
    <div class="product-editor-main">
        <x-card type="primary" :outline="true" title="Thông tin Combo" :collapsible="true" class="mb-0 product-editor-info">
            <div class="row g-3">
                <div class="col-lg-8"><x-input id="combo_name" name="name" label="Tên Combo" :value="$combo->name" required /></div>
                <div class="col-lg-4"><x-slug name="slug" label="Đường dẫn" :value="$combo->slug" source="combo_name" /></div>
                <div class="col-12"><x-textarea name="summary" label="Mô tả ngắn" :value="$combo->summary" rows="3" /></div>
                <div class="col-12"><x-tinymce name="description" label="Thông tin Combo" :value="$combo->description" /></div>
            </div>
        </x-card>
        <x-card type="secondary" :outline="true" title="Ảnh Combo" :collapsible="true" class="mb-0 product-editor-media">
            <x-product-image-upload name="image" label="Ảnh Combo" :existing-images="$combo->getMedia('combo_images')->map(fn ($media) => ['id' => $media->id, 'url' => $media->getUrl()])->all()" :max-files="1" />
        </x-card>
        @include('components.admin.combo-item-manager', ['combo' => $combo, 'componentProducts' => $componentProducts])
    </div>
    <div class="product-editor-sidebar">
        <x-card type="info" :outline="true" title="Giá và hiển thị" :collapsible="true" class="mb-0">
            <x-select name="combo_category_id" label="Danh mục Combo" :options="$categories->pluck('name', 'id')" :selected="old('combo_category_id', $combo->combo_category_id)" />
            <div class="row g-2"><div class="col-6"><x-input name="price" type="number" label="Giá bán" :value="old('price', $combo->price)" min="1" step="1" required /></div><div class="col-6"><x-input name="compare_price" type="number" label="Giá niêm yết" :value="old('compare_price', $combo->compare_price)" min="0" step="1" /></div></div>
            <x-select name="status" label="Trạng thái" :options="['active' => 'Đang bán', 'draft' => 'Bản nháp', 'archived' => 'Ngừng bán']" :selected="old('status', $combo->status ?: 'active')" />
            <x-input name="sort_order" type="number" label="Thứ tự" :value="old('sort_order', $combo->sort_order ?: 0)" min="0" step="1" />
            <x-input name="published_at" type="datetime-local" label="Ngày xuất bản" :value="old('published_at', $combo->published_at?->format('Y-m-d\TH:i'))" />
            <div class="border-top pt-3"><input type="hidden" name="is_active" value="0"><div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="is_active" id="combo_is_active" value="1" @checked(old('is_active', $combo->exists ? $combo->is_active : true))><label class="form-check-label" for="combo_is_active">Hiển thị Combo</label></div><input type="hidden" name="allow_preorder" value="0"><div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="allow_preorder" id="combo_allow_preorder" value="1" @checked(old('allow_preorder', $combo->allow_preorder))><label class="form-check-label" for="combo_allow_preorder">Cho phép đặt trước</label></div><input type="hidden" name="is_featured" value="0"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_featured" id="combo_is_featured" value="1" @checked(old('is_featured', $combo->is_featured))><label class="form-check-label" for="combo_is_featured">Combo nổi bật</label></div></div>
        </x-card>
    </div>
</div>
<div class="d-flex justify-content-end gap-2 mt-3"><a class="btn btn-default" href="{{ route('admin.combos.index') }}">Quay lại</a><button class="btn btn-primary" type="submit">{{ $combo->exists ? 'Lưu Combo' : 'Tạo Combo' }}</button></div>
