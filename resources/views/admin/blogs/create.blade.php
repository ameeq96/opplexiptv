@extends('admin.layouts.app')

@section('title', 'Create Blog')
@section('page_title', 'Create Blog')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">Blogs</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.blogs.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.blogs._form')
    </form>
@endsection
