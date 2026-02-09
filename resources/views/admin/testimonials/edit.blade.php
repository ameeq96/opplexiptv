@extends('admin.layouts.app')

@section('title', 'Edit Testimonial')
@section('page_title', 'Edit Testimonial')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.testimonials.index') }}">Testimonials</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.testimonials._form', ['submitLabel' => 'Update'])
        </form>
    </div>
@endsection
