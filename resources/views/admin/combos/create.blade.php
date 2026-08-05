@extends('layouts.admin')
@section('title', 'Thêm Combo')
@section('page-title', 'Thêm Combo')
@section('breadcrumbs')<ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.combos.index') }}">Combo</a></li><li class="breadcrumb-item active">Thêm mới</li></ol>@endsection
@section('content')<form id="admin-save-form" action="{{ route('admin.combos.store') }}" method="POST" enctype="multipart/form-data">@csrf
@include('admin.combos.combo')
</form>@endsection
