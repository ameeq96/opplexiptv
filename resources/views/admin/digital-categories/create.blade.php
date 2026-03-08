@extends('admin.layouts.app')
@section('title', 'Create Digital Category')
@section('content')
<div class="admin-card">
    <form method="POST" action="{{ route('admin.digital-categories.store') }}">
        @csrf
        @include('admin.digital-categories._form', ['submitLabel' => 'Create'])
    </form>
</div>
@endsection
