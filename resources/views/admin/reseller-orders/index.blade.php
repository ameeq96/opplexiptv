@extends('admin.layout.app')

@section('page_title', 'All Reseller Orders')

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('panel-orders.index') }}" method="GET" class="row g-2 align-items-center">

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
                        <option value="yesterday" {{ request('date_filter') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="7days" {{ request('date_filter') == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30days" {{ request('date_filter') == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90days" {{ request('date_filter') == '90days' ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="year" {{ request('date_filter') == 'year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>

                <div class="col-auto">
                    <select name="expiry_status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Expiry Filter --</option>
                        <option value="soon" {{ request('expiry_status') == 'soon' ? 'selected' : '' }}>Expiring Soon (Next 5 Days)</option>
                        <option value="expired" {{ request('expiry_status') == 'expired' ? 'selected' : '' }}>Already Expired</option>
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
                    <a href="{{ route('panel-orders.create') }}" class="btn btn-dark">
                        <i class="bi bi-plus-lg me-1"></i>
                    </a>
                </div>

            </form>
        </div>
    </div>

    <form id="bulkDeleteForm" action="{{ route('reseller-orders.bulkDelete') }}" method="POST">
        @csrf
        @method('DELETE')

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 120px;">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th style="width: 50px;">#ID</th>
                        <th style="min-width: 120px;">Client</th>
                        <th style="min-width: 100px;">Package</th>
                        <th style="min-width: 100px;">Credits</th>
                        <th style="min-width: 90px;">Days Left</th>
                        <th style="min-width: 170px;">IPTV Username</th>
                        <th style="min-width: 90px;">Duration</th>
                        <th style="min-width: 100px;">Cost Price</th>
                        <th style="min-width: 100px;">Sell Price</th>
                        <th style="min-width: 90px;">Profit</th>
                        <th style="min-width: 100px;">Status</th>
                        <th style="min-width: 120px;">Payment</th>
                        <th style="min-width: 120px;">Buying Date</th>
                        <th style="min-width: 120px;">Expiry Date</th>
                        <th style="min-width: 160px;">Note</th> {{-- NEW --}}
                        <th style="min-width: 160px;">Screenshots</th>
                        <th style="min-width: 100px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}">
                            </td>

                            {{-- ID --}}
                            <td>{{ $loop->iteration }}</td>

                            {{-- Client --}}
                            <td>{{ $order->user->name ?? 'N/A' }}</td>

                            {{-- Package --}}
                            <td>{{ $order->custom_package ? $order->custom_package : $order->package }}</td>

                            {{-- Credits --}}
                            <td>{{ $order->credits }}</td>

                            {{-- Days Left --}}
                            <td>
                                @if ($order->expiry_date)
                                    {{ \Carbon\Carbon::parse($order->expiry_date)->isPast()
                                        ? 'Expired'
                                        : \Carbon\Carbon::now()->diffInDays($order->expiry_date) . ' days' }}
                                @else
                                    -
                                @endif
                            </td>

                            {{-- IPTV Username --}}
                            <td>{{ $order->iptv_username ?? '-' }}</td>

                            {{-- Duration --}}
                            <td>{{ $order->duration ?? 'N/A' }}</td>

                            {{-- Prices --}}
                            <td>{{ $order->price }} {{ $order->currency }}</td>
                            <td>{{ $order->sell_price }} {{ $order->currency }}</td>
                            <td class="fw-bold text-success">{{ $order->profit }} {{ $order->currency }}</td>

                            {{-- Status --}}
                            <td>{{ ucfirst($order->status) }}</td>

                            {{-- Payment --}}
                            <td>{{ $order->payment_method ?? ($order->custom_payment_method ?? '-') }}</td>

                            {{-- Dates --}}
                            <td>{{ $order->buying_date }}</td>
                            <td>{{ $order->expiry_date ?? '-' }}</td>

                            {{-- Note (truncated inline) --}}
                            <td>
                                @if (!empty($order->note))
                                    <span class="d-inline-block text-truncate" style="max-width: 220px;" title="{{ $order->note }}">
                                        {{ $order->note }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Screenshots: horizontal strip --}}
                            <td>
                                @php
                                    $pics = $order->pictures ?? collect();
                                    $count = $pics->count();
                                @endphp

                                @if ($count)
                                    <div class="d-flex overflow-auto" style="max-width: 260px; gap: 6px;">
                                        @foreach ($pics as $pic)
                                            <img src="{{ asset($pic->path) }}" alt="ss" width="50" height="50"
                                                style="object-fit: cover; border-radius: 4px; cursor: pointer; flex-shrink: 0;"
                                                data-bs-toggle="modal" data-bs-target="#screenshotModal"
                                                onclick="showScreenshot('{{ asset($pic->path) }}')">
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td>
                                <a href="{{ route('panel-orders.edit', $order->id) }}"
                                    class="btn btn-sm btn-outline-primary me-1">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17" class="text-muted">No reseller orders found.</td>
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
    <div class="modal fade" id="screenshotModal" tabindex="-1" aria-labelledby="screenshotModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark position-relative border-0">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2"
                    data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body p-0 text-center">
                    <img id="modalScreenshot" src="" class="img-fluid"
                        style="width: 100%; max-height: 90vh; object-fit: contain;" alt="Screenshot">
                </div>
            </div>
        </div>
    </div>

    <script>
        // simple helper for the modal
        function showScreenshot(url) {
            const img = document.getElementById('modalScreenshot');
            if (img) img.src = url;
        }
    </script>

@endsection
