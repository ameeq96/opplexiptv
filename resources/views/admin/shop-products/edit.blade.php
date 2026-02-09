@extends('admin.layouts.app')

@section('title', 'Edit Product')
@section('page_title', 'Edit Product')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.shop-products.index') }}">Shop Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.shop-products.update', $product) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.shop-products._form', ['submitLabel' => 'Update'])
        </form>
    </div>
@endsection
