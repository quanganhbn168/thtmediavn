@extends('layouts.admin')
@section('title', 'Sửa Combo')
@section('page-title', 'Sửa Combo')
@section('breadcrumbs')<ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.combos.index') }}">Combo</a></li><li class="breadcrumb-item active">Chỉnh sửa</li></ol>@endsection
@section('content')<form id="admin-save-form" action="{{ route('admin.combos.update', $combo) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
@include('admin.combos._form')
</form>@endsection
