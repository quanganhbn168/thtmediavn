@extends('layouts.master')

@section('title', 'Thông tin tài khoản — '.$website['name'])

@section('content')
<section class="section-space bg-soft">
    <div class="container">
        <div class="row g-4">
            <aside class="col-lg-3">@include('pages.account._nav')</aside>
            <div class="col-lg-9">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="content-card mb-3">
                    <h1 class="h3 mb-3">Thông tin cá nhân</h1>
                    <form action="{{ route('account.profile.update') }}" method="post" class="row g-3">
                        @csrf @method('PUT')
                        <div class="col-md-6"><label class="form-label">Họ và tên</label><input class="form-control" name="name" value="{{ old('name', $user->name) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Số điện thoại</label><input class="form-control" name="phone" value="{{ old('phone', $user->phone) }}"></div>
                        <div class="col-12"><button class="btn btn-primary">Lưu thông tin</button></div>
                    </form>
                </div>

                <div class="content-card mb-3">
                    <h2 class="h5 fw-bold mb-3">Địa chỉ đã lưu</h2>
                    @forelse($addresses as $address)
                        <div class="d-flex justify-content-between gap-3 py-3 border-bottom">
                            <div>
                                <strong>{{ $address->name }}</strong> · {{ $address->phone }}
                                @if($address->is_default)<span class="badge text-bg-primary">Mặc định</span>@endif
                                <div class="text-muted small">{{ $address->address }}, {{ $address->ward }}, {{ $address->district }}, {{ $address->province }}</div>
                            </div>
                            <form action="{{ route('account.addresses.destroy', $address) }}" method="post">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form>
                        </div>
                    @empty
                        <p class="text-muted">Chưa có địa chỉ nào.</p>
                    @endforelse
                </div>

                <div class="content-card">
                    <h2 class="h5 fw-bold mb-3">Thêm địa chỉ</h2>
                    <form action="{{ route('account.addresses.store') }}" method="post" class="row g-3">
                        @csrf
                        <div class="col-md-6"><input class="form-control" name="name" placeholder="Tên người nhận" required></div>
                        <div class="col-md-6"><input class="form-control" name="phone" placeholder="Số điện thoại" required></div>
                        <div class="col-md-4"><input class="form-control" name="province" placeholder="Tỉnh/Thành phố" required></div>
                        <div class="col-md-4"><input class="form-control" name="district" placeholder="Quận/Huyện"></div>
                        <div class="col-md-4"><input class="form-control" name="ward" placeholder="Phường/Xã"></div>
                        <div class="col-12"><input class="form-control" name="address" placeholder="Số nhà, tên đường" required></div>
                        <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_default" value="1" id="defaultAddress"><label class="form-check-label" for="defaultAddress">Đặt làm địa chỉ mặc định</label></div></div>
                        <div class="col-12"><button class="btn btn-primary">Thêm địa chỉ</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
