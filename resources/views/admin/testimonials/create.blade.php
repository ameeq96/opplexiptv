@extends('admin.layouts.app')

@section('title', 'Add Testimonial')
@section('page_title', 'Add Testimonial')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.testimonials.index') }}">Testimonials</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data">
            @include('admin.testimonials._form', ['submitLabel' => 'Create'])
        </form>
    </div>
@endsection
