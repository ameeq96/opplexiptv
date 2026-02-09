@extends('admin.layouts.app')

@section('title', 'Edit Service')
@section('page_title', 'Edit Service')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.home-services.index') }}">Home Services</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.home-services.update', $service) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.home-services._form', ['submitLabel' => 'Update'])
        </form>
    </div>
@endsection
