@extends('layouts.master')
@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('meta_keywords', $seoKeywords)
@section('canonical', $intro->url)
@section('og_title', $seoTitle)
@section('og_description', $seoDescription)
@section('og_type', 'article')
@section('seo_image', $intro->image_url)
@section('content')
<article class="container mx-auto px-4 py-12" style="max-width: 56rem">
    <nav aria-label="Đường dẫn" class="mb-6 text-sm"><a href="{{ route('home') }}">Trang chủ</a> / <a href="{{ route('about') }}">Giới thiệu</a></nav>
    <h1 class="text-3xl font-bold mb-6">{{ $intro->title }}</h1>
    @if($intro->summary)<p class="mb-6 text-lg">{{ $intro->summary }}</p>@endif
    @if($intro->image_url)<img src="{{ $intro->image_url }}" alt="{{ $intro->title }}" class="w-full mb-8 rounded-xl">@endif
    <div class="prose max-w-none fi-prose">{!! $content !!}</div>
</article>
@endsection
