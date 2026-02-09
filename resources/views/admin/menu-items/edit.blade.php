@extends('admin.layouts.app')

@section('title', 'Edit Menu Item')
@section('page_title', 'Edit Menu Item')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.menu-items.index') }}">Menu Items</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.menu-items.update', $item) }}">
            @method('PUT')
            @include('admin.menu-items._form', ['submitLabel' => 'Update'])
        </form>
    </div>
@endsection
