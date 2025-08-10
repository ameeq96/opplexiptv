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
            <form action="{{ route('clients.index') }}" method="GET" enctype="multipart/form-data"
                class="d-flex flex-wrap align-items-center gap-2">

                <select name="per_page" class="form-select w-auto" onchange="this.form.submit()">
                    @foreach ([10, 20, 30, 40, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                            Show {{ $size }}
                        </option>
                    @endforeach
                </select>

                <input type="text" name="search" value="{{ request('search') }}" class="form-control w-auto"
                    placeholder="Search clients...">

                <button type="submit" class="btn btn-primary">Search</button>

                <input type="file" name="csv_file" accept=".csv" class="form-control w-auto">
                @csrf
                <button type="submit" formaction="{{ route('clients.import') }}" class="btn btn-success"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    Import CSV
                </button>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="exclude_iptv" value="1" id="excludeIPTV"
                        onchange="this.form.submit()" {{ request('exclude_iptv') ? 'checked' : '' }}>
                    <label class="form-check-label" for="excludeIPTV">
                        Exclude IPTV Clients
                    </label>
                </div>

                <a href="{{ route('clients.create') }}" class="btn btn-dark ms-auto">
                    <i class="bi bi-plus-lg me-1"></i> Add New Client
                </a>
            </form>
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
