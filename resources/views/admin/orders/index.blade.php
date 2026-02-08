@extends('admin.layouts.app')

@section('title', 'All Orders')
@section('page_title', 'All Orders')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Orders</li>
@endsection

@section('content')
    <div class="admin-card mb-3">
        <ul class="nav nav-pills gap-2">
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

    <div class="admin-card mb-4">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 g-md-3 align-items-end">
            <input type="hidden" name="tab" value="{{ $tab ?? 'unmessaged' }}">
            <input type="hidden" name="type" value="{{ $type ?? 'package' }}">

            <div class="col-12 col-md-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Per Page</label>
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach ([10, 20, 30, 40, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                            Show {{ $size }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-9 col-lg-4">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                    placeholder="Search orders...">
            </div>

            <div class="col-12 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">Date</label>
                <select name="date_filter" class="form-select" onchange="this.form.submit()">
                    <option value="">Date Filter</option>
                    <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ request('date_filter') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="7days" {{ request('date_filter') == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30days" {{ request('date_filter') == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90days" {{ request('date_filter') == '90days' ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="year" {{ request('date_filter') == 'year' ? 'selected' : '' }}>This Year</option>
                </select>
            </div>

            <div class="col-12 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">Expiry</label>
                <select name="expiry_status" class="form-select" onchange="this.form.submit()">
                    <option value="">Expiry Filter</option>
                    <option value="soon" {{ request('expiry_status') == 'soon' ? 'selected' : '' }}>Expiring Soon (Next 5 Days)</option>
                    <option value="expired" {{ request('expiry_status') == 'expired' ? 'selected' : '' }}>Already Expired</option>
                </select>
            </div>

            <div class="col-12 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    @php $statusVal = request('status', 'all'); @endphp
                    <option value="all" {{ $statusVal === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending" {{ $statusVal === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ $statusVal === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ $statusVal === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">Start</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">End</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
            </div>

            <div class="col-12 col-lg d-flex justify-content-start align-items-end gap-2">
                <button class="btn btn-primary" type="submit">Filter</button>
                <a href="{{ route('admin.orders.create') }}" class="btn btn-dark">
                    Add <i class="bi bi-plus-lg me-1"></i>
                </a>
            </div>
        </form>
    </div>

    <form id="bulkActionForm" action="{{ route('admin.orders.bulkAction') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="bulkActionInput" value="">

        <div class="d-flex align-items-center mb-2 justify-content-start">
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="clearSelection" class="btn btn-sm btn-outline-secondary">Clear</button>
                <span id="selectedCounter" class="badge-soft">0 Selected</span>
            </div>
        </div>

        <div class="admin-card p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead>
                        <tr>
                            <th style="min-width: 120px;"><input type="checkbox" id="checkAll"></th>
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
                                <td><input type="checkbox" name="order_ids[]" value="{{ $order->id }}"></td>
                                <td>{{ $order->user->name ?? 'N/A' }}</td>
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
                                            <strong class="{{ $daysLeft <= 3 ? 'text-warning' : '' }}">{{ $daysLeft }} days</strong>
                                        @endif
                                    @else
                                        <strong class="text-muted">N/A</strong>
                                    @endif
                                </td>
                                <td>{{ $order->iptv_username ?? '-' }}</td>
                                <td>{{ $order->currency }} {{ number_format($order->price, 2) }}</td>
                                <td>
                                    @php
                                        $isExpired = $order->expiry_date && \Carbon\Carbon::parse($order->expiry_date)->lt(now());
                                        $statusToShow = $isExpired ? 'expired' : $order->status;
                                    @endphp
                                    <span class="badge-soft {{ $statusToShow == 'active' ? 'success' : ($statusToShow == 'pending' ? 'warn' : 'gray') }}">
                                        {{ ucfirst($statusToShow) }}
                                    </span>
                                </td>
                                <td>{{ ucfirst($order->payment_method ?? '-') }}</td>
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
                                        <span class="d-inline-block text-truncate" style="max-width: 220px;" title="{{ $order->note }}">
                                            {{ \Illuminate\Support\Str::limit($order->note, 80) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($order->messaged_at)
                                        <span class="badge-soft success">Yes</span>
                                        <small class="text-muted d-block">{{ $order->messaged_at->format('d M Y H:i') }}</small>
                                    @else
                                        <span class="badge-soft gray">No</span>
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
                                                <img src="{{ asset($pic->path) }}" alt="ss" width="50" height="50"
                                                    style="object-fit: cover; border-radius: 6px; cursor: pointer; flex-shrink: 0;"
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
                                        $phone = preg_replace('/\D+/', '', $order->user->phone ?? '');
                                        $custName = $order->user->name ?? 'Customer';
                                        $message = urlencode(
                                            "Hello {$custName}, your IPTV order for package '{$order->package}' is now " .
                                                strtoupper($order->status) .
                                                '.',
                                        );

                                        $waUniversal = $phone ? "https://wa.me/{$phone}?text={$message}" : null;
                                        $waWeb = $phone ? "https://web.whatsapp.com/send?phone={$phone}&text={$message}" : null;
                                        $waBusinessAndroid = $phone
                                            ? "intent://send/?phone={$phone}&text={$message}#Intent;scheme=whatsapp;package=com.whatsapp.w4b;end"
                                            : null;
                                    @endphp

                                    <div class="d-flex justify-content-center gap-1">
                                        @if ($waUniversal)
                                            <a href="{{ $waUniversal }}" target="_blank" rel="noopener"
                                                class="btn btn-sm btn-outline-success wa-btn" data-id="{{ $order->id }}"
                                                data-android="{{ $waBusinessAndroid }}" data-web="{{ $waWeb }}">
                                                WhatsApp
                                            </a>
                                        @endif

                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-sm btn-outline-primary">Edit</a>
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
        </div>

        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-success js-bulk" data-action="mark_messaged">Mark as Messaged</button>
            @if (($tab ?? 'unmessaged') === 'messaged')
                <button type="button" class="btn btn-outline-secondary js-bulk" data-action="unmark_messaged">Move back to Unmessaged</button>
            @endif
            <button type="button" class="btn btn-danger js-bulk" data-action="delete">Delete Selected</button>
        </div>
    </form>

    <div class="mt-3 d-flex justify-content-center flex-column">
        {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bulkForm = document.getElementById('bulkActionForm');
        const bulkInput = document.getElementById('bulkActionInput');
        const checkAllEl = document.getElementById('checkAll');
        const counterEl = document.getElementById('selectedCounter');
        const clearBtn = document.getElementById('clearSelection');

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.wa-btn');
            if (!btn) return;

            const ua = navigator.userAgent || navigator.vendor || window.opera;
            const isAndroid = /Android/i.test(ua);
            const isDesktop = !/Android|iPhone|iPad|iPod/i.test(ua);

            if (isAndroid && btn.dataset.android) {
                btn.setAttribute('href', btn.dataset.android);
            } else if (isDesktop && btn.dataset.web) {
                btn.setAttribute('href', btn.dataset.web);
            }
        }, { capture: true });

        function getRowCheckboxes() {
            return Array.from(document.querySelectorAll('input[name="order_ids[]"]'));
        }

        function updateSelectedCount() {
            const boxes = getRowCheckboxes();
            const total = boxes.length;
            const checked = boxes.filter(cb => cb.checked).length;

            if (counterEl) counterEl.textContent = `${checked} Selected`;

            document.querySelectorAll('.js-bulk').forEach(btn => {
                btn.disabled = (checked === 0);
            });

            if (checkAllEl) {
                if (checked === 0) {
                    checkAllEl.indeterminate = false;
                    checkAllEl.checked = false;
                } else if (checked === total) {
                    checkAllEl.indeterminate = false;
                    checkAllEl.checked = true;
                } else {
                    checkAllEl.indeterminate = true;
                }
            }
        }

        if (checkAllEl) {
            checkAllEl.addEventListener('change', function() {
                getRowCheckboxes().forEach(cb => cb.checked = this.checked);
                updateSelectedCount();
            });
        }

        document.addEventListener('change', function(e) {
            if (e.target && e.target.matches('input[name="order_ids[]"]')) {
                updateSelectedCount();
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                getRowCheckboxes().forEach(cb => cb.checked = false);
                if (checkAllEl) {
                    checkAllEl.indeterminate = false;
                    checkAllEl.checked = false;
                }
                updateSelectedCount();
            });
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-bulk');
            if (!btn) return;

            const anyChecked = !!document.querySelector('input[name="order_ids[]"]:checked');
            if (!anyChecked) {
                alert('Please select at least one order.');
                return;
            }

            const action = btn.dataset.action;
            if (action === 'delete' && !confirm('Delete selected orders?')) return;

            bulkInput.value = action;
            bulkForm.submit();
        });

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

        updateSelectedCount();
    });
</script>
@endpush
