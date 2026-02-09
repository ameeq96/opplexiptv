@extends('admin.layouts.app')

@section('title', 'Add Menu Item')
@section('page_title', 'Add Menu Item')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.menu-items.index') }}">Menu Items</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add</li>
@endsection

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.menu-items.store') }}">
            @include('admin.menu-items._form', ['submitLabel' => 'Create'])
        </form>
    </div>
@endsection
