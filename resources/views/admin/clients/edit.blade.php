@extends('admin.layout.app')

@section('page_title', 'Edit Client')

@section('content')
    <h4>Edit Client</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('clients.update', $client) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Name *</label>
            <input type="text" name="name" class="form-control" value="{{ $client->name }}" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ $client->email }}">
        </div>

        <div class="mb-3">
            <label>Phone *</label>
            <input type="text" name="phone" class="form-control" value="{{ $client->phone }}" required>
        </div>

        <div class="mb-3">
            <label>Country</label>
            <input type="text" name="country" class="form-control" value="{{ $client->country }}">
        </div>

        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control">{{ $client->notes }}</textarea>
        </div>

        <button type="submit" class="btn btn-dark">Update</button>
        <a href="{{ route('clients.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection
