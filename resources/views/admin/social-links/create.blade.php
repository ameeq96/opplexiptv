@extends('admin.layouts.app')

@section('title', 'Add Social Link')
@section('page_title', 'Add Social Link')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.social-links.index') }}">Social Links</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.social-links.store') }}">
            @include('admin.social-links._form', ['submitLabel' => 'Create'])
        </form>
    </div>
@endsection
