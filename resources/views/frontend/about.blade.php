@extends('layouts.master')

@section('title', 'Giới thiệu — ' . $website['name'])

@php
    $story = data_get($aboutSettings?->about_story, 'vi');
    $history = data_get($aboutSettings?->about_history, 'vi');
    $mission = data_get($aboutSettings?->about_mission, 'vi');
    $vision = data_get($aboutSettings?->about_vision, 'vi');
    $coreValues = data_get($aboutSettings?->about_core_values, 'vi');
    $aboutImage = $siteAssets?->getFirstMediaUrl('about_image');
@endphp

@section('content')
<div class="breadcrumb-wrap"><div class="container"><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item active">Giới thiệu</li></ol></nav></div></div>
<section class="page-hero"><div class="container"><h1>{{ $website['name'] }}</h1><p>{{ $website['tagline'] }}</p></div></section>

<section class="section-space">
    <div class="container">
        <div class="row g-4 align-items-center">
            @if($aboutImage)
                <div class="col-lg-5"><img class="w-100 rounded-4 shadow-sm" src="{{ $aboutImage }}" alt="{{ $website['name'] }}" loading="lazy"></div>
            @endif
            <div class="{{ $aboutImage ? 'col-lg-7' : 'col-12' }}">
                <div class="content-copy">{!! $story !!}</div>
            </div>
        </div>

        @if($history)
            <div class="content-card mt-5"><h2 class="h3 fw-bold">Lịch sử hình thành</h2><div class="content-copy">{!! $history !!}</div></div>
        @endif

        <div class="row g-4 mt-1">
            @if($vision)<div class="col-lg-6"><div class="content-card h-100"><h2 class="h4 fw-bold">Tầm nhìn</h2><div class="content-copy">{!! $vision !!}</div></div></div>@endif
            @if($mission)<div class="col-lg-6"><div class="content-card h-100"><h2 class="h4 fw-bold">Sứ mệnh</h2><div class="content-copy">{!! $mission !!}</div></div></div>@endif
        </div>

        @if($coreValues)
            <div class="mt-5"><div class="text-center mb-4"><h2 class="h3 fw-bold">Giá trị cốt lõi</h2></div>{!! $coreValues !!}</div>
        @endif
    </div>
</section>
@endsection
