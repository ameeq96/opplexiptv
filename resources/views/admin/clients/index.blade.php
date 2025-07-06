@extends('admin.layout.app')

@section('page_title', 'All Clients')

@section('content')
    <a href="{{ route('clients.create') }}" class="btn btn-dark mb-3">Add New Client</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th><th>Email</th><th>Phone</th><th>Country</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
                <tr>
                    <td>{{ $client->name }}</td>
                    <td>{{ $client->email }}</td>
                    <td>{{ $client->phone }}</td>
                    <td>{{ $client->country }}</td>
                    <td>
                        <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
