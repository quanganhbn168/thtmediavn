<div class="row g-4">
    <div class="col-lg-8">
        <x-card type="primary" :outline="true" title="Thông tin danh mục" :collapsible="true"><div class="row g-3">
            <div class="col-md-7"><x-input id="combo_category_name" name="name" label="Tên danh mục" :value="$category->name" placeholder="Ví dụ: Gói nội dung" required /></div>
            <div class="col-md-5"><x-slug name="slug" label="Đường dẫn" :value="$category->slug" source="combo_category_name" /></div>
            <div class="col-12"><x-textarea name="description" label="Mô tả" :value="$category->description" rows="6" /></div>
        </div></x-card>
        <x-card type="secondary" :outline="true" title="Tối ưu SEO" :collapsible="true">
            <x-input name="seo_title" label="SEO title" :value="old('seo_title', $category->seo_title)" placeholder="Tiêu đề hiển thị trên công cụ tìm kiếm" />
            <x-textarea name="seo_description" label="SEO description" :value="old('seo_description', $category->seo_description)" rows="4" placeholder="Mô tả ngắn cho công cụ tìm kiếm" />
        </x-card>
    </div>
    <div class="col-lg-4"><x-card type="info" :outline="true" title="Hiển thị" :collapsible="true">
        <x-input name="sort_order" type="number" label="Thứ tự" :value="old('sort_order', $category->sort_order ?: 0)" min="0" step="1" />
        <input type="hidden" name="is_active" value="0">
        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" id="combo_category_active" value="1" @checked(old('is_active', $category->exists ? $category->is_active : true))><label class="form-check-label" for="combo_category_active">Hiển thị danh mục</label></div>
    </x-card></div>
</div>
<div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-default" href="{{ route('admin.combo-categories.index') }}">Quay lại</a><button class="btn btn-primary" type="submit">{{ $category->exists ? 'Lưu thay đổi' : 'Tạo danh mục' }}</button></div>
