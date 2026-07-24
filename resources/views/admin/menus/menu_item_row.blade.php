<ol class="dd-list">
    @foreach($items as $item)
        <li class="dd-item" data-id="{{ $item->id }}">
            <!-- Handle kéo thả -->
            <div class="dd-handle">
                <i class="bi bi-arrows-move me-2 text-secondary"></i>
                @if($item->icon)
                    <i class="{{ $item->icon }} me-1 text-primary"></i>
                @else
                    <i class="bi bi-link-45deg me-1 text-success"></i>
                @endif
                <span class="fw-bold">{{ $item->getTranslation('title', 'vi') }}</span>
                <span class="text-muted ms-2" style="font-size: 0.8rem; font-weight: normal;">(Link: {{ $item->url }})</span>
                
                @if(!$item->is_active)
                    <span class="badge bg-warning ms-2" style="font-size: 0.7rem;">Ẩn</span>
                @endif
            </div>

            <!-- Nút thao tác -->
            <div class="dd-actions">
                <button type="button" class="btn btn-xs btn-info text-white edit-item-btn" 
                        data-id="{{ $item->id }}"
                        data-title-vi="{{ $item->getTranslation('title', 'vi') }}"
                        data-title-en="{{ $item->getTranslation('title', 'en') }}"
                        data-url="{{ $item->url }}"
                        data-target="{{ $item->target }}"
                        data-icon="{{ $item->icon }}"
                        data-active="{{ $item->is_active ? '1' : '0' }}"
                        title="Sửa liên kết">
                    <i class="bi bi-pencil-fill"></i>
                </button>
                <button type="button" class="btn btn-xs btn-danger delete-item-btn" data-id="{{ $item->id }}" title="Xóa liên kết">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>

            <!-- Đệ quy hiển thị các con -->
            @if($item->children && $item->children->isNotEmpty())
                @include('admin.menus.menu_item_row', ['items' => $item->children])
            @endif
        </li>
    @endforeach
</ol>
