@extends('layouts.plain')

@section('title', 'Đăng nhập quản trị — ' . (config('app.name', 'Quản trị')))

@section('body')
<div class="min-vh-100 d-flex align-items-center justify-content-center bg-body-tertiary">
    <div class="container-xxl">
        <div class="row g-0 shadow-lg rounded-4 overflow-hidden bg-body">
            <div class="col-lg-6 d-none d-lg-flex flex-column align-items-center justify-content-center text-white position-relative admin-auth-media">
                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(120deg, rgba(20,33,61,.84), rgba(39,76,119,.6)), url('{{ asset('assets/img/login_bg.jpg') }}') center/cover no-repeat; z-index: 0;"></div>
            </div>

            <div class="col-lg-6 p-4 p-md-5">
                <div class="mx-auto" style="max-width: 440px;">
                    <div class="text-center mb-4">
                        <div class="h3 fw-black text-primary mb-3">{{ $website['name'] }}</div>
                        <h2 class="h3 fw-black">Đăng nhập quản trị</h2>
                        <p class="text-muted mb-0">Vui lòng đăng nhập để tiếp tục.</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form class="row g-3" method="post" action="{{ route('admin.login.store') }}">
                        @csrf
                        <div class="col-12">
                            <label class="form-label" for="adminAuthEmail">Email quản trị</label>
                            <input class="form-control" id="adminAuthEmail" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="adminAuthPassword">Mật khẩu</label>
                            <input class="form-control" id="adminAuthPassword" name="password" type="password" required autocomplete="current-password">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" id="adminRemember" name="remember" value="1" type="checkbox">
                                <label class="form-check-label" for="adminRemember">Ghi nhớ đăng nhập</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100" type="submit">Đăng nhập</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
