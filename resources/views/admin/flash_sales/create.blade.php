@extends('layouts.admin')

@section('title', 'Tạo Flash Sale')
@section('page-title', 'Tạo Flash Sale')

@section('breadcrumbs')
<ol class="breadcrumb float-sm-end mb-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.flash-sales.index') }}">Flash Sale</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tạo mới</li>
</ol>
@endsection

@section('content')
    <x-admin.flash-sale-editor
        :sale="$sale"
        :editor-items="$editorItems"
        :action="route('admin.flash-sales.store')"
        method="POST"
        submit-label="Tạo Flash Sale"
        :show-save-and-create="true"
    />
@endsection
