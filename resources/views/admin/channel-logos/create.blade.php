@extends('admin.layouts.app')

@section('title', 'Add Logo')
@section('page_title', 'Add Logo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.channel-logos.index') }}">Channel Logos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.channel-logos.store') }}" enctype="multipart/form-data">
            @include('admin.channel-logos._form', ['submitLabel' => 'Create'])
        </form>
    </div>
@endsection
