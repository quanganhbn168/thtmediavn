@extends('layouts.admin')

@section('title', 'Tài khoản quản trị')
@section('page-title', 'Tài khoản quản trị')

@section('content')
    <x-admin.index-header description="Quản lý tài khoản truy cập admin và vai trò được cấp."
                          :create-url="route('admin.users.create')" create-label="Thêm tài khoản" />

    <x-admin.filter-panel>
        <form action="{{ route('admin.users.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-6">
                <label class="form-label">Từ khóa</label>
                <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Họ tên hoặc email">
            </div>
            <div class="col-lg-3">
                <label class="form-label">Vai trò</label>
                <select name="role" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($roles as $id => $name)
                        <option value="{{ $id }}" @selected(request('role') === $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1">Lọc</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-default" aria-label="Đặt lại bộ lọc">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </x-admin.filter-panel>

    <x-admin.table-card title="Danh sách tài khoản">
        <x-slot:tools><span class="badge text-bg-light">{{ $users->total() }} tài khoản</span></x-slot:tools>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Họ tên</th><th>Email</th><th>Vai trò</th><th>Ngày tạo</th><th class="text-end">Thao tác</th></tr></thead>
                <tbody>
                @forelse($users as $user)
                    @php($canManage = $user->canBeManagedBy(auth()->user()))
                    <tr>
                        <td>
                            @if($canManage)
                                <a href="{{ route('admin.users.edit', $user) }}" class="fw-semibold text-decoration-none">{{ $user->name }}</a>
                            @else
                                <span class="fw-semibold">{{ $user->name }}</span>
                            @endif
                            @if($user->is(auth()->user())) <span class="badge text-bg-info">Bạn</span> @endif
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @forelse($user->roles as $role)
                                <span class="badge text-bg-primary">{{ $role->name }}</span>
                            @empty
                                —
                            @endforelse
                        </td>
                        <td>{{ $user->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            @if($canManage)
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-default btn-sm" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a>
                                @unless($user->is(auth()->user()))
                                    <button form="delete-user-{{ $user->id }}" class="btn btn-default btn-sm text-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                                @endunless
                            @else
                                <span class="text-body-secondary small"><i class="bi bi-lock me-1"></i>Ngoài phạm vi quyền</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5">Chưa có tài khoản.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @foreach($users as $user)
            @if($user->canBeManagedBy(auth()->user()) && ! $user->is(auth()->user()))
                <form id="delete-user-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-none" data-admin-delete-form>
                    @csrf @method('DELETE')
                </form>
            @endif
        @endforeach

        <x-slot:footer>@if($users->hasPages()){{ $users->links() }}@endif</x-slot:footer>
    </x-admin.table-card>
@endsection
