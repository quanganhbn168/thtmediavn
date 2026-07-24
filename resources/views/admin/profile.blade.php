@extends('layouts.admin')

@section('title', 'Hồ sơ cá nhân')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Hồ Sơ Cá Nhân</h3>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row g-3">
            <!-- Cột bên trái: Ảnh đại diện & Thông tin chung -->
            <div class="col-md-4">
                <div class="card card-outline card-primary mb-3">
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3 text-uppercase shadow-sm"
                             style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold; border: 3px solid var(--bs-primary);">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <h3 class="h5 mb-1 font-weight-bold">{{ $user->name }}</h3>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-shield-check text-success me-1"></i>
                            {{ $user->roles->pluck('name')->implode(', ') ?: 'Quản trị viên' }}
                        </p>
                        
                        <ul class="list-group list-group-flush text-start small">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-secondary"><i class="bi bi-envelope me-1"></i> Email:</span>
                                <span class="fw-semibold">{{ $user->email }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-secondary"><i class="bi bi-calendar3 me-1"></i> Ngày tham gia:</span>
                                <span class="fw-semibold">{{ $user->created_at?->format('d/m/Y') ?? 'N/A' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Thống kê hệ thống -->
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Thống kê hoạt động</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between py-2 px-3">
                                <span class="text-secondary">Đơn hàng trong hệ thống:</span>
                                <span class="badge bg-primary rounded-pill">{{ \App\Models\Order::count() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-2 px-3">
                                <span class="text-secondary">Yêu cầu liên hệ mới:</span>
                                <span class="badge bg-warning text-dark rounded-pill">{{ \App\Models\Contact::count() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-2 px-3">
                                <span class="text-secondary">Quản lý sản phẩm:</span>
                                <span class="badge bg-success rounded-pill">{{ \App\Models\CustomServiceRequest::count() }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Cột bên phải: Cập nhật thông tin tài khoản -->
            <div class="col-md-8">
                <div class="card card-outline card-primary">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="profile-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active py-3 px-4" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">
                                    <i class="bi bi-gear-fill me-1"></i> Cài đặt tài khoản
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content">
                            <!-- Tab Settings -->
                            <div class="tab-pane fade show active" id="settings" role="tabpanel">
                                <form id="admin-save-form" action="{{ route('admin.profile.update') }}" method="POST">
                                    @csrf
                                    
                                    <h5 class="mb-4 text-primary font-weight-bold border-bottom pb-2">Thông tin cá nhân</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label font-weight-bold">Họ và tên</label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label font-weight-bold">Địa chỉ Email</label>
                                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <h5 class="mt-4 mb-4 text-danger font-weight-bold border-bottom pb-2">Đổi mật khẩu bảo mật</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label font-weight-bold">Mật khẩu mới</label>
                                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Bỏ trống nếu không muốn đổi">
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label font-weight-bold">Xác nhận mật khẩu mới</label>
                                            <input type="password" name="password_confirmation" class="form-control" placeholder="Xác nhận lại mật khẩu mới">
                                        </div>
                                    </div>

                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold">
                                            <i class="bi bi-save me-1"></i> Lưu thay đổi
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
