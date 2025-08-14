@extends('admin.layout.app')

@section('page_title', 'All Orders')

@section('content')
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('orders.index') }}" method="GET" class="row g-2 align-items-center">

                <div class="col-auto">
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        @foreach ([10, 20, 30, 40, 100] as $size)
                            <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                                Show {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search orders...">
                </div>

                <div class="col-auto">
                    <select name="date_filter" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Date Filter --</option>
                        <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('date_filter') == 'yesterday' ? 'selected' : '' }}>Yesterday
                        </option>
                        <option value="7days" {{ request('date_filter') == '7days' ? 'selected' : '' }}>Last 7 Days
                        </option>
                        <option value="30days" {{ request('date_filter') == '30days' ? 'selected' : '' }}>Last 30 Days
                        </option>
                        <option value="90days" {{ request('date_filter') == '90days' ? 'selected' : '' }}>Last 90 Days
                        </option>
                        <option value="year" {{ request('date_filter') == 'year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>

                <div class="col-auto">
                    <select name="expiry_status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Expiry Filter --</option>
                        <option value="soon" {{ request('expiry_status') == 'soon' ? 'selected' : '' }}>Expiring Soon
                            (Next 3 Days)</option>
                        <option value="expired" {{ request('expiry_status') == 'expired' ? 'selected' : '' }}>Already
                            Expired</option>
                    </select>
                </div>

                <div class="col-auto">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                </div>

                <div class="col-auto">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                </div>

                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>

                <div class="col ms-auto text-end">
                    <a href="{{ route('orders.create') }}" class="btn btn-dark">
                        <i class="bi bi-plus-lg me-1"></i>
                    </a>
                </div>

            </form>
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
                        <th>Days Left</th>
                        <th>IPTV Username</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Buying Date</th>
                        <th>Expiry Date</th>
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
                            <td>
                                @php
                                    // All predefined packages list
                                    $defaultPackages = [
                                        '1 Month Opplex IPTV Account',
                                        '3 Months Opplex IPTV Account',
                                        '6 Months Opplex IPTV Account',
                                        '12 Months Opplex IPTV Account',
                                        '1 Month Starshare Account',
                                        '3 Months Starshare Account',
                                        '6 Months Starshare Account',
                                        '12 Months Starshare Account',
                                    ];
                                @endphp

                                @if (!in_array($order->package, $defaultPackages))
                                    <span>{{ $order->custom_package }}</span>
                                @else
                                    {{ $order->package }}
                                @endif
                            </td>   
                            <td>
                                @if ($order->expiry_date)
                                    @php
                                        $daysLeft = \Carbon\Carbon::now()->diffInDays(
                                            \Carbon\Carbon::parse($order->expiry_date),
                                            false,
                                        );
                                    @endphp

                                    @if ($daysLeft < 0)
                                        <span class="text-danger">Expired</span>
                                    @else
                                        <span class="{{ $daysLeft <= 3 ? 'text-warning fw-bold' : '' }}">
                                            {{ $daysLeft }} days
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>


                            <td>{{ $order->iptv_username ?? '-' }}</td>
                            <td>{{ $order->currency }} {{ number_format($order->price, 2) }}</td>
                            <td>
                                @php
                                    $isExpired =
                                        $order->expiry_date && \Carbon\Carbon::parse($order->expiry_date)->lt(now());
                                    $statusToShow = $isExpired ? 'expired' : $order->status;
                                @endphp

                                <span
                                    class="badge
                                {{ $statusToShow == 'active'
                                    ? 'bg-success'
                                    : ($statusToShow == 'pending'
                                        ? 'bg-warning text-dark'
                                        : 'bg-secondary') }}">
                                    {{ ucfirst($statusToShow) }}
                                </span>
                            </td>

                            <td>{{ ucfirst($order->payment_method) }}</td>
                            <td>{{ \Carbon\Carbon::parse($order->buying_date)->format('d M Y') }}</td>
                            <td>
                                @if ($order->expiry_date)
                                    {{ \Carbon\Carbon::parse($order->expiry_date)->format('d M Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>

                            <td>
                                @if ($order->screenshot)
                                    <img src="{{ asset($order->screenshot) }}" alt="Screenshot" width="50"
                                        height="50" style="object-fit: cover; border-radius: 4px; cursor: pointer;"
                                        data-bs-toggle="modal" data-bs-target="#screenshotModal"
                                        onclick="showScreenshot('{{ asset($order->screenshot) }}')">
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="d-flex pt-3 align-items-center justify-content-center">
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

    <!-- Screenshot Lightbox Modal -->
    <div class="modal fade" id="screenshotModal" tabindex="-1" aria-labelledby="screenshotModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark position-relative border-0">

                <!-- Close button -->
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2"
                    data-bs-dismiss="modal" aria-label="Close"></button>

                <!-- Image -->
                <div class="modal-body p-0 text-center">
                    <img id="modalScreenshot" src="" class="img-fluid"
                        style="width: 100%; max-height: 90vh; object-fit: contain;" alt="Screenshot">
                </div>
            </div>
        </div>
    </div>



@endsection
