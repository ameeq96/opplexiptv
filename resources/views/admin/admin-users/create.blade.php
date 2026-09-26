@extends('admin.layouts.app')

@section('title', 'Add Admin User')
@section('page_title', 'Add Admin User')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.admin-users.index') }}">Admin Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.admin-users.store') }}">
            @include('admin.admin-users._form', ['submitLabel' => 'Create'])
        </form>
    </div>
@endsection
