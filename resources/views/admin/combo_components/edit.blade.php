@extends('layouts.admin')
@section('title', 'Sửa thành phần Combo')
@section('page-title', 'Sửa thành phần Combo')
@section('breadcrumbs')<ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.combos.index') }}">Combo</a></li><li class="breadcrumb-item"><a href="{{ route('admin.combos.components.index', $combo) }}">{{ $combo->name }}</a></li><li class="breadcrumb-item active">Chỉnh sửa</li></ol>@endsection
@section('content')<form id="admin-save-form" action="{{ route('admin.combos.components.update', [$combo, $comboItem]) }}" method="POST">@csrf @method('PUT') @include('admin.combo_components.combo_component')</form>@endsection
