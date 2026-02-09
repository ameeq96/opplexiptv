@extends('admin.layouts.app')

@section('title', 'Edit Package')
@section('page_title', 'Edit Package')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.packages.index') }}">Packages</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.packages.update', $package) }}">
            @method('PUT')
            @include('admin.packages._form', ['submitLabel' => 'Update'])
        </form>
    </div>
@endsection
