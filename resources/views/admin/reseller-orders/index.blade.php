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
                            (Next 5 Days)</option>
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
                        <th style="width: 40px;">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th style="width: 50px;">#ID</th>
                        <th style="min-width: 120px;">Client</th>
                        <th style="min-width: 100px;">Package</th>
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
                        <th style="min-width: 120px;">Screenshot</th>
                        <th style="min-width: 100px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><input type="checkbox" name="order_ids[]" value="{{ $order->id }}"></td>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->user->name ?? 'N/A' }}</td>
                            <td>{{ $order->duration ?? 'N/A' }}</td>
                            <td>{{ $order->custom_package ? $order->custom_package : $order->package }}</td>

                            {{-- Days Left --}}
                            <td>
                                @if ($order->expiry_date)
                                    {{ \Carbon\Carbon::parse($order->expiry_date)->diffInDays(now(), false) > 0
                                        ? 'Expired'
                                        : \Carbon\Carbon::now()->diffInDays($order->expiry_date) . ' days' }}
                                @else
                                    -
                                @endif
                            </td>

                            {{-- IPTV Username --}}
                            <td>{{ $order->iptv_username ?? '-' }}</td>

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

                            {{-- Screenshot --}}
                            <td>
                                @if ($order->screenshot)
                                    <img src="{{ asset($order->screenshot) }}" width="60">
                                @else
                                    -
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
                            <td colspan="15" class="text-muted">No reseller orders found.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <button type="submit" class="btn btn-danger mt-2" onclick="return confirm('Delete selected orders?')">Delete
            Selected</button>
    </form>

    <div class="mt-3 d-flex justify-content-center flex-column">
        {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

@endsection

@push('scripts')
    <script>
        document.getElementById('checkAll').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('input[name="order_ids[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
@endpush
