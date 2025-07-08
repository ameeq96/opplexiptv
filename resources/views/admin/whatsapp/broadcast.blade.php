@extends('admin.layout.app')
@section('page_title', 'WhatsApp Broadcast')

@section('content')

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-6">
                    <form action="{{ route('whatsapp.broadcast') }}" method="GET" class="input-group">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Search clients...">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <button onclick="sendAll()" class="btn btn-success">
                        <i class="bi bi-whatsapp me-1"></i> Send to All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $phone = preg_replace('/[^0-9]/', '', $user->phone);
                                $msg = urlencode("Hello {$user->name}, your IPTV service update from Opplex.");
                                $url = "https://wa.me/{$phone}?text={$msg}";
                            @endphp
                            <tr class="text-center">
                                <td>
                                    <i class="bi bi-person-circle me-1 text-muted"></i>
                                    {{ $user->name }}
                                </td>
                                <td>{{ $user->phone }}</td>
                                <td>
                                    <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-whatsapp me-1"></i> Send
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No clients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-center flex-column">
        {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

@endsection
