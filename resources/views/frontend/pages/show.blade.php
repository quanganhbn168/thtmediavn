@extends('layouts.master')

@section('title', $page->getTranslation('seo_title', 'vi') ?: $page->getTranslation('name', 'vi'))
@section('meta_description', $page->getTranslation('seo_description', 'vi') ?: $page->getTranslation('sub_title', 'vi'))

@section('content')
    <div class="breadcrumb-wrap">
        <div class="container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $page->getTranslation('name', 'vi') }}</li>
            </ol>
        </div>
    </div>

    <section class="section-space">
        <div class="container {{ $page->template === 'full-width' ? '' : 'container-content' }}">
            <article class="content-card">
                <header class="mb-4">
                    <h1 class="mb-2">{{ $page->getTranslation('name', 'vi') }}</h1>
                    @if($page->getTranslation('sub_title', 'vi'))
                        <p class="lead text-muted mb-0">{{ $page->getTranslation('sub_title', 'vi') }}</p>
                    @endif
                </header>
                <div class="page-content">{!! $page->getTranslation('content', 'vi') !!}</div>
            </article>
        </div>
    </section>
@endsection
