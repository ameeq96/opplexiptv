@extends('admin.layouts.app')

@section('title', 'Add Service')
@section('page_title', 'Add Service')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.home-services.index') }}">Home Services</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.home-services.store') }}" enctype="multipart/form-data">
            @include('admin.home-services._form', ['submitLabel' => 'Create'])
        </form>
    </div>
@endsection
