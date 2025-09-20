@extends('admin.layout.app')

@section('page_title', 'All Orders')

@section('content')

    {{-- Tabs --}}
    <div class="mb-3">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'unmessaged') === 'unmessaged' ? 'active' : '' }}"
                    href="{{ route('admin.orders.index', array_merge(request()->query(), ['tab' => 'unmessaged'])) }}">
                    Unmessaged
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? '') === 'messaged' ? 'active' : '' }}"
                    href="{{ route('admin.orders.index', array_merge(request()->query(), ['tab' => 'messaged'])) }}">
                    Messaged
                </a>
            </li>
        </ul>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 align-items-center">
                {{-- keep current tab while filtering --}}
                <input type="hidden" name="tab" value="{{ $tab ?? 'unmessaged' }}">
                <input type="hidden" name="type" value="{{ $type ?? 'package' }}">

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
                    <a href="{{ route('admin.orders.create') }}" class="btn btn-dark">
                        <i class="bi bi-plus-lg me-1"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- BULK ACTIONS + TABLE --}}
    <form id="bulkActionForm" action="{{ route('admin.orders.bulkAction') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="bulkActionInput" value="">

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 120px;">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th style="min-width: 120px;">Client</th>
                        <th style="min-width: 120px;">Package</th>
                        <th style="min-width: 120px;">Days Left</th>
                        <th style="min-width: 170px;">IPTV Username</th>
                        <th style="min-width: 120px;">Price</th>
                        <th style="min-width: 120px;">Status</th>
                        <th style="min-width: 120px;">Payment</th>
                        <th style="min-width: 120px;">Buying Date</th>
                        <th style="min-width: 120px;">Expiry Date</th>
                        <th style="min-width: 160px;">Note</th>
                        <th style="min-width: 140px;">Messaged?</th>
                        <th style="min-width: 120px;">Screenshot</th>
                        <th style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                {{-- IMPORTANT: name="order_ids[]" hee rehna chahiye --}}
                                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}">
                            </td>

                            <td>{{ $order->user->name }}</td>

                            <td>
                                @php
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
                                        <strong class="text-danger">Expired</strong>
                                    @else
                                        <strong class="{{ $daysLeft <= 3 ? 'text-warning' : '' }}">{{ $daysLeft }}
                                            days</strong>
                                    @endif
                                @else
                                    <strong class="text-muted">N/A</strong>
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
                                @if (!empty($order->note))
                                    <span class="d-inline-block text-truncate" style="max-width: 220px;"
                                        title="{{ $order->note }}">
                                        {{ \Illuminate\Support\Str::limit($order->note, 80) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                @if ($order->messaged_at)
                                    <span class="badge bg-info">Yes</span>
                                    <small
                                        class="text-muted d-block">{{ $order->messaged_at->format('d M Y H:i') }}</small>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>

                            <td>
                                @php
                                    $pics = $order->pictures ?? collect();
                                    $count = $pics->count();
                                @endphp
                                @if ($count)
                                    <div class="d-flex overflow-auto" style="max-width: 250px; gap: 5px;">
                                        @foreach ($pics as $pic)
                                            <img src="{{ asset($pic->path) }}" alt="ss" width="50"
                                                height="50"
                                                style="object-fit: cover; border-radius: 4px; cursor: pointer; flex-shrink: 0;"
                                                data-bs-toggle="modal" data-bs-target="#screenshotModal"
                                                onclick="showScreenshot('{{ asset($pic->path) }}')">
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $phone = preg_replace('/[^0-9]/', '', $order->user->phone ?? '');
                                    $message = urlencode(
                                        "Hello {$order->user->name}, your IPTV order for package '{$order->package}' is now " .
                                            strtoupper($order->status) .
                                            '.',
                                    );
                                    $waBusinessUrl = $phone ? "https://wa.me/{$phone}?text={$message}" : null;
                                @endphp

                                <div class="d-flex justify-content-center gap-1">
                                    @if ($waBusinessUrl)
                                        <a href="{{ $waBusinessUrl }}" target="_blank"
                                            class="btn btn-sm btn-outline-success wa-btn" data-id="{{ $order->id }}">
                                            WhatsApp
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.orders.edit', $order) }}"
                                        class="btn btn-sm btn-outline-primary">Edit</a>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="text-muted">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Buttons MUST be inside the form --}}
        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-success js-bulk" data-action="mark_messaged">
                Mark as Messaged
            </button>

            @if (($tab ?? 'unmessaged') === 'messaged')
                <button type="button" class="btn btn-outline-secondary js-bulk" data-action="unmark_messaged">
                    Move back to Unmessaged
                </button>
            @endif

            <button type="button" class="btn btn-danger js-bulk" data-action="delete">
                Delete Selected
            </button>
        </div>
    </form>

    {{-- Pagination --}}
    <div class="mt-3 d-flex justify-content-center flex-column">
        {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

    {{-- Screenshot Lightbox Modal --}}
    <div class="modal fade" id="screenshotModal" tabindex="-1" aria-labelledby="screenshotModalLabel"
        aria-hidden="true">
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
@endsection

<script>
    // Select all
    document.getElementById('checkAll')?.addEventListener('change', function() {
        document.querySelectorAll('input[name="order_ids[]"]').forEach(cb => cb.checked = this.checked);
    });

    // Lightbox helper
    function showScreenshot(src) {
        const img = document.getElementById('modalScreenshot');
        if (img) img.src = src;
    }

    // Bulk buttons: event delegation
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.js-bulk');
        if (!btn) return;

        const form = document.getElementById('bulkActionForm');
        const input = document.getElementById('bulkActionInput');
        if (!form || !input) {
            alert('Bulk action form ya hidden input missing!');
            return;
        }

        // ensure at least one checkbox
        const anyChecked = !!document.querySelector('input[name="order_ids[]"]:checked');
        if (!anyChecked) {
            alert('Please select at least one order.');
            return;
        }

        const action = btn.dataset.action;
        if (action === 'delete' && !confirm('Delete selected orders?')) return;

        input.value = action;
        console.log('Submitting bulkAction:', action);
        form.submit();
    });

    // Optional: WA click => mark one as messaged
    document.querySelectorAll('.wa-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`{{ url('orders') }}/${id}/mark-messaged`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).catch(() => {});
        });
    });
</script>
