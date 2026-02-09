@extends('admin.layouts.app')

@section('title', 'Edit Logo')
@section('page_title', 'Edit Logo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.channel-logos.index') }}">Channel Logos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.channel-logos.update', $logo) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.channel-logos._form', ['submitLabel' => 'Update'])
        </form>
    </div>
@endsection
