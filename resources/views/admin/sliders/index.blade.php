@extends('layouts.admin')

@section('title', 'Quản lý bộ trình chiếu')
@section('page-title', 'Bộ trình chiếu')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Bộ trình chiếu</li>
</ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách bộ trình chiếu"
    description="Quản lý nội dung, thứ tự slide và trạng thái hiển thị."
    icon="bi-images"
    :create-url="route('admin.sliders.create')"
    create-label="Thêm bộ trình chiếu"
    resource="slider"
    bulk-delete-warning="Toàn bộ slide và hình ảnh bên trong cũng sẽ bị xóa."
>
    <x-slot:filters>
        <form action="{{ route('admin.sliders.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label for="slider-search" class="form-label">Từ khóa</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" class="form-control" id="slider-search" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên hoặc mã định danh">
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="slider-type" class="form-label">Vị trí hiển thị</label>
                <select class="form-select" id="slider-type" name="type">
                    <option value="">Tất cả vị trí</option>
                    @foreach($sliderTypes as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label for="slider-status" class="form-label">Trạng thái</label>
                <select class="form-select" id="slider-status" name="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" @selected(request('status') === 'active')>Đang hoạt động</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Ngừng hoạt động</option>
                </select>
            </div>
            <div class="col-lg-1 col-md-3">
                <label for="slider-per-page" class="form-label">Hiển thị</label>
                <select class="form-select" id="slider-per-page" name="per_page">
                    @foreach([10, 25, 50] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }} dòng</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Lọc</button>
                @if(request()->hasAny(['search', 'type', 'status', 'per_page']))
                    <a href="{{ route('admin.sliders.index') }}" class="btn btn-default" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover text-nowrap align-middle mb-0">
            <thead>
                <tr>
                    <th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th>
                    <th style="width:80px">ID</th>
                    <th>Tên bộ trình chiếu</th>
                    <th>Vị trí hiển thị</th>
                    <th class="text-center">Số slide</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end" style="width:130px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sliders as $slider)
                    <tr data-record-id="{{ $slider->id }}">
                        <td data-select-column class="text-center"><input form="admin-bulk-slider-form" type="checkbox" name="ids[]" value="{{ $slider->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $slider->name }}"></td>
                        <td class="text-body-secondary">#{{ $slider->id }}</td>
                        <td><a href="{{ route('admin.sliders.edit', $slider) }}" class="fw-semibold text-decoration-none">{{ $slider->name }}</a></td>
                        <td><span class="fw-semibold">{{ $slider->type_label }}</span><code class="d-block mt-1">{{ $slider->key }}</code></td>
                        <td class="text-center"><span class="badge text-bg-info">{{ $slider->items_count }}</span></td>
                        <td class="text-center"><x-toggle model="Slider" :id="$slider->id" field="is_active" :checked="$slider->is_active" /></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.sliders.edit', $slider) }}" class="btn btn-default" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a>
                                <button type="submit" form="delete-slider-{{ $slider->id }}" class="btn btn-default text-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="admin-empty"><span><i class="bi bi-images"></i></span><h5>Chưa có bộ trình chiếu</h5><p>Hãy tạo bản ghi đầu tiên hoặc thay đổi bộ lọc.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach($sliders as $slider)
        <form id="delete-slider-{{ $slider->id }}" action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa {{ $slider->name }}?" data-delete-warning="Toàn bộ slide và hình ảnh bên trong cũng sẽ bị xóa.">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    <x-slot:footer>
        @if($sliders->hasPages())
            {{ $sliders->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
