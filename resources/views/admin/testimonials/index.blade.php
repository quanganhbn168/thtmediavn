@extends('layouts.admin')

@section('title', 'Cảm nhận khách hàng')
@section('page-title', 'Cảm nhận khách hàng')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Cảm nhận khách hàng</li>
    </ol>
@endsection

@section('content')
    <x-admin.index-card
        title="Nội dung testimonial"
        description="Nội dung thương hiệu tự biên tập, tách riêng hoàn toàn với đánh giá sản phẩm và bình luận."
        icon="bi-chat-quote"
        :create-url="route('admin.testimonials.create')"
        create-label="Thêm cảm nhận"
        resource="testimonial"
        :reorderable="true"
        :reorder-enabled="! request()->hasAny(['search', 'per_page'])"
        :order-start="$testimonials->firstItem() ?? 1"
    >
        <x-slot:filters>
            <form action="{{ route('admin.testimonials.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label" for="testimonial-search">Từ khóa</label>
                    <div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input id="testimonial-search" class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Tên, nhãn hiển thị hoặc nội dung"></div>
                </div>
                <div class="col-md-2"><label class="form-label" for="testimonial-per-page">Số dòng</label><select id="testimonial-per-page" name="per_page" class="form-select">@foreach([10, 20, 25, 50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>@endforeach</select></div>
                <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Lọc</button>@if(request()->hasAny(['search', 'per_page']))<a class="btn btn-default" href="{{ route('admin.testimonials.index') }}" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a>@endif</div>
            </form>
        </x-slot:filters>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Khách hàng</th><th>Nội dung</th><th class="text-center" style="width:100px">Sao</th><th class="text-center" style="width:110px">Hiển thị</th><th class="text-end" style="width:125px">Thao tác</th></tr></thead>
                <tbody>
                    @forelse($testimonials as $testimonial)
                        <tr data-record-id="{{ $testimonial->id }}">
                            <td data-select-column class="text-center"><input form="admin-bulk-testimonial-form" type="checkbox" name="ids[]" value="{{ $testimonial->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $testimonial->name }}"></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($avatar = $testimonial->getFirstMediaUrl('testimonial_avatar'))<img src="{{ $avatar }}" class="rounded-circle object-fit-cover" width="42" height="42" alt="">@else<span class="rounded-circle bg-light text-secondary" style="width:42px;height:42px;display:inline-grid;place-items:center"><i class="bi bi-person"></i></span>@endif
                                    <span><a class="fw-semibold text-decoration-none" href="{{ route('admin.testimonials.edit', $testimonial) }}">{{ $testimonial->name }}</a><small class="d-block text-muted">{{ $testimonial->label ?: 'Không có nhãn phụ' }} · Thứ tự {{ $testimonial->sort_order }} @if($testimonial->getFirstMediaUrl('testimonial_before') && $testimonial->getFirstMediaUrl('testimonial_after')) · <span class="text-primary fw-semibold">Có ảnh trước / sau</span>@endif</small></span>
                                </div>
                            </td>
                            <td><span class="text-body-secondary">{{ Str::limit($testimonial->content, 110) }}</span></td>
                            <td class="text-center text-warning">{{ str_repeat('★', $testimonial->rating) }}</td>
                            <td class="text-center"><x-toggle model="Testimonial" :id="$testimonial->id" field="is_active" :checked="$testimonial->is_active" /></td>
                            <td class="text-end"><div class="btn-group btn-group-sm"><a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="btn btn-default" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a><button type="submit" form="delete-testimonial-{{ $testimonial->id }}" class="btn btn-default text-danger" title="Xóa"><i class="bi bi-trash"></i></button></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5"><div class="admin-empty"><span><i class="bi bi-chat-quote"></i></span><h5>Chưa có cảm nhận khách hàng</h5><p>Thêm nội dung testimonial biên tập để hiển thị trên trang chủ.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @foreach($testimonials as $testimonial)
            <form id="delete-testimonial-{{ $testimonial->id }}" action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa cảm nhận này?">@csrf @method('DELETE')</form>
        @endforeach

        <x-slot:footer>@if($testimonials->hasPages()){{ $testimonials->links() }}@endif</x-slot:footer>
    </x-admin.index-card>
@endsection
