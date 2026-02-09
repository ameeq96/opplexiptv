@extends('admin.layouts.app')

@section('title', 'Add Product')
@section('page_title', 'Add Product')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.shop-products.index') }}">Shop Products</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.shop-products.store') }}" enctype="multipart/form-data">
            @include('admin.shop-products._form', ['submitLabel' => 'Create'])
        </form>
    </div>
@endsection
