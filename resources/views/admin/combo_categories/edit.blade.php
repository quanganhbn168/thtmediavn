@extends('layouts.admin')
@section('title', 'Sửa danh mục Combo')
@section('page-title', 'Sửa danh mục Combo')
@section('breadcrumbs')<ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.combo-categories.index') }}">Danh mục Combo</a></li><li class="breadcrumb-item active">Chỉnh sửa</li></ol>@endsection
@section('content')@include('admin.combo_categories._form')@endsection
