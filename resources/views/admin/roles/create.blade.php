@extends('layouts.admin')

@section('title', 'Thêm vai trò')
@section('page-title', 'Thêm vai trò')

@section('content')
    <form id="admin-save-form" action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
        <div class="card card-outline card-primary">
            <div class="card-body">
                <x-input name="name" label="Tên vai trò" :required="true" />
                <div class="alert alert-info">
                    Quyền xem bảng điều khiển được cấp tự động. Hãy chọn ít nhất một nhóm chức năng cho vai trò.
                </div>
                @foreach($permissions as $group => $items)
                    <fieldset class="border rounded p-3 mb-3">
                        <legend class="float-none w-auto px-2 fs-6 text-uppercase">{{ $group }}</legend>
                        <div class="row g-2">
                            @foreach($items as $permission)
                                @php $definition = config('admin.permissions.'.$permission->name, []); @endphp
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-check">
                                        <input id="permission-{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}"
                                               type="checkbox" class="form-check-input" @checked(in_array($permission->id, old('permissions', [])))>
                                        <label for="permission-{{ $permission->id }}" class="form-check-label">
                                            {{ $definition['label'] ?? $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
                @error('permissions')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-default">Hủy</a>
            <button class="btn btn-primary">Tạo vai trò</button>
        </div>
    </form>
@endsection
