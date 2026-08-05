@extends('layouts.admin')
@section('title', 'Thêm thành phần Combo')
@section('page-title', 'Thêm thành phần Combo')
@section('breadcrumbs')<ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.combos.index') }}">Combo</a></li><li class="breadcrumb-item"><a href="{{ route('admin.combos.components.index', $combo) }}">{{ $combo->name }}</a></li><li class="breadcrumb-item active">Thêm thành phần</li></ol>@endsection
@section('content')<form id="admin-save-form" action="{{ route('admin.combos.components.store', $combo) }}" method="POST">@csrf @include('admin.combo_components.combo_component')</form>@endsection
