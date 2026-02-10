@extends('admin.layouts.app')

@section('title', 'Edit Social Link')
@section('page_title', 'Edit Social Link')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.social-links.index') }}">Social Links</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.social-links.update', $link) }}">
            @method('PUT')
            @include('admin.social-links._form', ['submitLabel' => 'Update'])
        </form>
    </div>
@endsection
