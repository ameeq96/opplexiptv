@extends('admin.layouts.app')
@section('title', 'Edit Digital Category')
@section('content')
<div class="admin-card">
    <form method="POST" action="{{ route('admin.digital-categories.update', $category) }}">
        @csrf
        @method('PUT')
        @include('admin.digital-categories._form', ['submitLabel' => 'Update'])
    </form>
</div>
@endsection
