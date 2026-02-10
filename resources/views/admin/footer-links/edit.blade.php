@extends('admin.layouts.app')

@section('title', 'Edit Footer Link')
@section('page_title', 'Edit Footer Link')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.footer-links.index') }}">Footer Links</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.footer-links.update', $link) }}">
            @method('PUT')
            @include('admin.footer-links._form', ['submitLabel' => 'Update'])
        </form>
    </div>
@endsection
