@extends('admin.layouts.app')
@section('title', 'Edit Digital Product')
@section('content')
<div class="admin-card">
    <form method="POST" action="{{ route('admin.digital-products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.digital-products._form', ['submitLabel' => 'Update'])
    </form>
</div>
@endsection
