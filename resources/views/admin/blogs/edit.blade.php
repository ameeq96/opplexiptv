@extends('admin.layouts.app')

@section('title', 'Edit Blog')
@section('page_title', 'Edit Blog')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">Blogs</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.blogs.update', $blog) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.blogs._form')
    </form>
@endsection
