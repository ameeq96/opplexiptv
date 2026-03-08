@extends('admin.layouts.app')
@section('title', 'Create Digital Product')
@section('content')
<div class="admin-card">
    <form method="POST" action="{{ route('admin.digital-products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.digital-products._form', ['submitLabel' => 'Create'])
    </form>
</div>
@endsection
