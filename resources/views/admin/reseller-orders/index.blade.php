@extends('admin.layout.app')

@section('page_title', 'All Reseller Orders')

@section('content')

    {{-- Tabs --}}
    <div class="mb-3">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'unmessaged') === 'unmessaged' ? 'active' : '' }}"
                    href="{{ route('admin.panel-orders.index', array_merge(request()->except('tab', 'page'), ['tab' => 'unmessaged'])) }}">
                    Unmessaged
                </a </li>
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? '') === 'messaged' ? 'active' : '' }}"
                    href="{{ route('admin.panel-orders.index', array_merge(request()->except('tab', 'page'), ['tab' => 'messaged'])) }}">
                    Messaged
                </a>
            </li>
        </ul>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.panel-orders.index') }}" method="GET"
                  class="row g-2 g-md-3 align-items-end">
                {{-- keep current tab while filtering --}}
                <input type="hidden" name="tab" value="{{ $tab ?? 'unmessaged' }}">
                <input type="hidden" name="type" value="{{ $type ?? 'reseller' }}">

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

                {{-- Optional same filters as package list (if you want to keep UI parity) --}}
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label small text-muted mb-1">Date</label>
                    <select name="date_filter" class="form-select" onchange="this.form.submit()">
                        <option value="">Date Filter</option>
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

                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label small text-muted mb-1">Expiry</label>
                    <select name="expiry_status" class="form-select" onchange="this.form.submit()">
                        <option value="">Expiry Filter</option>
                        <option value="soon" {{ request('expiry_status') == 'soon' ? 'selected' : '' }}>Expiring Soon
                            (Next 5 Days)</option>
                        <option value="expired" {{ request('expiry_status') == 'expired' ? 'selected' : '' }}>Already
                            Expired</option>
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
                    <a href="{{ route('admin.panel-orders.create') }}" class="btn btn-dark">
                        Add <i class="bi bi-plus-lg me-1"></i> 
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- BULK ACTIONS + TABLE --}}
    <form id="bulkActionFormReseller" action="{{ route('admin.reseller-orders.bulkAction') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="bulkActionInputReseller" value="">

        <div class="d-flex align-items-center mb-2 justify-content-start">
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="clearSelectionReseller" class="btn btn-sm btn-outline-secondary">Clear</button>
                <span id="selectedCounterReseller" class="badge bg-primary">0 Selected</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 120px;">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th style="width: 50px;">#</th>
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
                        <th style="min-width: 160px;">Note</th>
                        <th style="min-width: 160px;">Screenshots</th>
                        <th style="min-width: 120px;">Messaged?</th>
                        <th style="min-width: 100px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><input type="checkbox" name="order_ids[]" value="{{ $order->id }}"></td>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $order->user->name ?? 'N/A' }}</td>
                            <td>{{ $order->custom_package ? $order->custom_package : $order->package }}</td>
                            <td>{{ $order->credits }}</td>
                            <td>
                                @if ($order->expiry_date)
                                    {{ \Carbon\Carbon::parse($order->expiry_date)->isPast()
                                        ? 'Expired'
                                        : \Carbon\Carbon::now()->diffInDays($order->expiry_date) . ' days' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $order->iptv_username ?? '-' }}</td>
                            <td>{{ $order->duration ?? 'N/A' }}</td>
                            <td>{{ $order->price }} {{ $order->currency }}</td>
                            <td>{{ $order->sell_price }} {{ $order->currency }}</td>
                            <td class="fw-bold text-success">{{ $order->profit }} {{ $order->currency }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                            <td>{{ $order->payment_method ?? ($order->custom_payment_method ?? '-') }}</td>
                            <td>{{ $order->buying_date }}</td>
                            <td>{{ $order->expiry_date ?? '-' }}</td>
                            <td>
                                @if (!empty($order->note))
                                    <span class="d-inline-block text-truncate" style="max-width: 220px;"
                                        title="{{ $order->note }}">
                                        {{ $order->note }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $pics = $order->pictures ?? collect();
                                    $count = $pics->count();
                                @endphp
                                @if ($count)
                                    <div class="d-flex overflow-auto" style="max-width: 260px; gap: 6px;">
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
                                    // --- CHANGES: WhatsApp Business aware links ---
                                    $phone = preg_replace('/\D+/', '', $order->user->phone ?? '');

                                    // NOTE: Phone ko international format me rakhna best (e.g., 447...).
                                    // $message me rawurlencode use kiya hai
                                    $custName = $order->user->name ?? 'Customer';
                                    $message = rawurlencode(
                                        "Hello {$custName}, your IPTV reseller order for package '{$order->package}' is now " .
                                            strtoupper($order->status) .
                                            '.',
                                    );

                                    $waUniversal = $phone ? "https://wa.me/{$phone}?text={$message}" : null; // iOS/others
                                    $waWeb = $phone
                                        ? "https://web.whatsapp.com/send?phone={$phone}&text={$message}"
                                        : null; // Desktop
                                    $waBusinessAndroid = $phone
                                        ? "intent://send/?phone={$phone}&text={$message}#Intent;scheme=whatsapp;package=com.whatsapp.w4b;end"
                                        : null; // Android Business
                                @endphp

                                <div class="d-flex justify-content-center gap-1">
                                    @if ($waUniversal)
                                        <a href="{{ $waUniversal }}" target="_blank" rel="noopener"
                                            class="btn btn-sm btn-outline-success wa-btn"
                                            data-android="{{ $waBusinessAndroid }}" data-web="{{ $waWeb }}">
                                            WhatsApp
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.panel-orders.show', $order->id) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        View
                                    </a>

                                    <a href="{{ route('admin.panel-orders.edit', $order->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="text-muted">No reseller orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bulk action buttons --}}
        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-success js-bulk-reseller" data-action="mark_messaged">
                Mark as Messaged
            </button>

            @if (($tab ?? 'unmessaged') === 'messaged')
                <button type="button" class="btn btn-outline-secondary js-bulk-reseller" data-action="unmark_messaged">
                    Move back to Unmessaged
                </button>
            @endif

            <button type="button" class="btn btn-danger js-bulk-reseller" data-action="delete">
                Delete Selected
            </button>
        </div>
    </form>

    <div class="mt-3 d-flex justify-content-center flex-column">
        {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

    <!-- Screenshot Lightbox Modal -->
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
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bulkActionFormReseller');
        const actionIn = document.getElementById('bulkActionInputReseller');
        const checkAll = document.getElementById('checkAll');
        const counter = document.getElementById('selectedCounterReseller');
        const clearBtn = document.getElementById('clearSelectionReseller');

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.wa-btn');
            if (!btn) return;

            const ua = navigator.userAgent || navigator.vendor || window.opera;
            const isAndroid = /Android/i.test(ua);
            const isDesktop = !/Android|iPhone|iPad|iPod/i.test(ua);

            if (isAndroid && btn.dataset.android) {
                // Force WhatsApp Business app
                btn.href = btn.dataset.android;
                btn.removeAttribute('target'); // intent links ko _self best
            } else if (isDesktop && btn.dataset.web) {
                // Desktop par WhatsApp Web
                btn.href = btn.dataset.web;
                // target _blank theek hai (already set)
            }
            // iOS/others -> wa.me default (no change)
        }, {
            capture: true
        });

        function rowBoxes() {
            return Array.from(document.querySelectorAll('input[name="order_ids[]"]'));
        }

        function updateSelectedState() {
            const boxes = rowBoxes();
            const total = boxes.length;
            const checked = boxes.filter(cb => cb.checked).length;

            if (counter) counter.textContent = `${checked} Selected`;

            // bulk buttons enable/disable
            document.querySelectorAll('.js-bulk-reseller').forEach(btn => {
                btn.disabled = (checked === 0);
            });

            // tri-state for Select All
            if (checkAll) {
                if (checked === 0) {
                    checkAll.indeterminate = false;
                    checkAll.checked = false;
                } else if (checked === total) {
                    checkAll.indeterminate = false;
                    checkAll.checked = true;
                } else {
                    checkAll.indeterminate = true;
                }
            }
        }

        // Header "Select All"
        checkAll?.addEventListener('change', function() {
            rowBoxes().forEach(cb => cb.checked = this.checked);
            updateSelectedState();
        });

        // Row checkbox
        document.addEventListener('change', function(e) {
            if (e.target && e.target.matches('input[name="order_ids[]"]')) {
                updateSelectedState();
            }
        });

        // Clear button
        clearBtn?.addEventListener('click', function() {
            rowBoxes().forEach(cb => cb.checked = false);
            if (checkAll) {
                checkAll.indeterminate = false;
                checkAll.checked = false;
            }
            updateSelectedState();
        });

        // Bulk buttons (existing flow)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-bulk-reseller');
            if (!btn) return;

            const anyChecked = !!document.querySelector('input[name="order_ids[]"]:checked');
            if (!anyChecked) {
                alert('Please select at least one order.');
                return;
            }

            const action = btn.dataset.action;
            if (action === 'delete' && !confirm('Delete selected orders?')) return;

            actionIn.value = action;
            form.submit();
        });

        // Lightbox helper (as-is)
        window.showScreenshot = function(url) {
            const img = document.getElementById('modalScreenshot');
            if (img) img.src = url;
        };

        // Initial render
        updateSelectedState();
    });
</script>
