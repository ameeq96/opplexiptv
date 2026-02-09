@extends('admin.layouts.app')

@section('title', 'Add Package')
@section('page_title', 'Add Package')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.packages.index') }}">Packages</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.packages.store') }}">
            @include('admin.packages._form', ['submitLabel' => 'Create'])
        </form>
    </div>
@endsection
