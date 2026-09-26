@extends('admin.layouts.app')

@section('title', 'Edit Admin User')
@section('page_title', 'Edit Admin User')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.admin-users.index') }}">Admin Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.admin-users.update', $adminUser) }}">
            @method('PUT')
            @include('admin.admin-users._form', ['submitLabel' => 'Update'])
        </form>
    </div>
@endsection
