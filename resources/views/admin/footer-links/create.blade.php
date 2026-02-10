@extends('admin.layouts.app')

@section('title', 'Add Footer Link')
@section('page_title', 'Add Footer Link')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.footer-links.index') }}">Footer Links</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.footer-links.store') }}">
            @include('admin.footer-links._form', ['submitLabel' => 'Create'])
        </form>
    </div>
@endsection
