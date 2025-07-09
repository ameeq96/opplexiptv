@extends('admin.layout.app')

@section('page_title', 'All Orders')

@section('content')

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-center justify-content-between">
                <div class="col-md-4">
                    <form action="{{ route('orders.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Search orders...">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>
                </div>

                <div class="col-md-4 text-end">
                    <a href="{{ route('orders.create') }}" class="btn btn-dark">
                        <i class="bi bi-plus-lg me-1"></i> Add New Order
                    </a>
                </div>
            </div>
        </div>
    </div>


    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form id="bulkDeleteForm" action="{{ route('orders.bulkDelete') }}" method="POST">
        @csrf
        @method('DELETE')

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th>Client</th>
                        <th>Package</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Screenshot</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}">
                            </td>
                            <td>{{ $order->user->name }}</td>
                            <td>{{ $order->package }}</td>
                            <td>{{ $order->currency }} {{ number_format($order->price, 2) }}</td>
                            <td>
                                <span
                                    class="badge
                                {{ $order->status == 'active'
                                    ? 'bg-success'
                                    : ($order->status == 'pending'
                                        ? 'bg-warning text-dark'
                                        : 'bg-secondary') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>{{ ucfirst($order->payment_method) }}</td>
                            <td>
                                @if ($order->screenshot)
                                    <img src="{{ asset('storage/' . $order->screenshot) }}" alt="Screenshot" width="50"
                                        height="50" style="object-fit: cover; border-radius: 4px;">
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-outline-primary me-1">
                                    Edit
                                </a>

                                @php
                                    $phone = preg_replace('/[^0-9]/', '', $order->user->phone);
                                    $message = urlencode(
                                        'Hello ' .
                                            $order->user->name .
                                            ", your IPTV order for package '" .
                                            $order->package .
                                            "' is now " .
                                            strtoupper($order->status) .
                                            '.',
                                    );
                                    $waUrl = "https://wa.me/{$phone}?text={$message}";
                                @endphp

                                <a href="{{ $waUrl }}" target="_blank" class="btn btn-sm btn-outline-success me-1">
                                    WhatsApp
                                </a>

                                <form action="{{ route('orders.destroy', $order) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Delete this order?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-danger mt-2" onclick="return confirm('Delete selected orders?')">
            Delete Selected
        </button>
    </form>

    <div class="mt-3 d-flex justify-content-center flex-column">
        {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

@endsection
