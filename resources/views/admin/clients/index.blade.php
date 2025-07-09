@extends('admin.layout.app')

@section('page_title', 'All Clients')

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <form action="{{ route('clients.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Search clients...">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                </div>

                <div class="col-md-5">
                    <form action="{{ route('clients.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <input type="file" name="csv_file" accept=".csv" class="form-control" required>
                            <button type="submit" class="btn btn-success">Import CSV</button>
                        </div>
                    </form>
                </div>

                <div class="col-md-3 text-end pe-md-2">
                    <a href="{{ route('clients.create') }}" class="btn btn-dark">
                        <i class="bi bi-plus-lg me-1"></i> Add New Client
                    </a>
                </div>
            </div>
        </div>
    </div>


    <form id="bulkDeleteForm" action="{{ route('clients.bulkDelete') }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Country</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td>
                                <input type="checkbox" name="client_ids[]" value="{{ $client->id }}">
                            </td>
                            <td>{{ $client->name }}</td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->phone }}</td>
                            <td>{{ $client->country ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('clients.edit', $client) }}"
                                    class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Delete this client?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted">No clients found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-danger mt-2" onclick="return confirm('Delete selected clients?')">Delete
            Selected</button>
    </form>

    <div class="mt-3 d-flex justify-content-center flex-column">
        {{ $clients->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

@endsection
