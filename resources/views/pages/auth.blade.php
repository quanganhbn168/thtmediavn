@extends('layouts.master')

@php
    $isRegister = request()->routeIs('register');
    $authLogo = $siteAssets?->getFirstMediaUrl('logo');
@endphp
@section('title', ($isRegister ? 'Đăng ký' : 'Đăng nhập') . ' — ' . $website['name'])

@section('content')
<section class="section-space bg-soft">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="content-card">
                    <div class="text-center mb-4">
                        @if($authLogo)
                            <a class="auth-brand d-inline-block mb-3" href="{{ route('home') }}" aria-label="{{ $website['name'] }}">
                                <img class="auth-brand-logo" src="{{ $authLogo }}" alt="{{ $website['name'] }}" width="310" height="92">
                            </a>
                        @else
                            <div class="h3 fw-black text-primary mb-3">{{ $website['name'] }}</div>
                        @endif
                        <h1 class="h3 fw-black">{{ $isRegister ? 'Tạo tài khoản' : 'Đăng nhập' }}</h1>
                    </div>
                    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                    <form class="row g-3" action="{{ $isRegister ? route('register.store') : route('login.store') }}" method="post">
                        @csrf
                        @if($isRegister)<div class="col-12"><label class="form-label" for="authName">Họ và tên</label><input class="form-control" id="authName" name="name" type="text" value="{{ old('name') }}" required></div>@endif
                        <div class="col-12"><label class="form-label" for="authEmail">Email</label><input class="form-control" id="authEmail" name="email" type="email" value="{{ old('email') }}" required></div>
                        @if($isRegister)<div class="col-12"><label class="form-label" for="authPhone">Số điện thoại</label><input class="form-control" id="authPhone" name="phone" type="tel" value="{{ old('phone') }}"></div>@endif
                        <div class="col-12"><label class="form-label" for="authPassword">Mật khẩu</label><input class="form-control" id="authPassword" name="password" type="password" required></div>
                        @if($isRegister)<div class="col-12"><label class="form-label" for="authPasswordConfirmation">Nhập lại mật khẩu</label><input class="form-control" id="authPasswordConfirmation" name="password_confirmation" type="password" required></div>@else<div class="col-12"><div class="form-check"><input class="form-check-input" id="remember" name="remember" value="1" type="checkbox"><label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label></div></div>@endif
                        <div class="col-12"><button class="btn btn-primary w-100" type="submit">{{ $isRegister ? 'Đăng ký' : 'Đăng nhập' }}</button></div>
                    </form>
                    <div class="text-center mt-3 small">
                        @if($isRegister)Đã có tài khoản? <a class="text-primary fw-bold" href="{{ route('login') }}">Đăng nhập</a>@else Chưa có tài khoản? <a class="text-primary fw-bold" href="{{ route('register') }}">Đăng ký</a>@endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
