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
            <form action="{{ route('admin.clients.index') }}" method="GET" enctype="multipart/form-data"
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
                <button type="submit" formaction="{{ route('admin.clients.import') }}" class="btn btn-success"
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

                <a href="{{ route('admin.clients.create') }}" class="btn btn-dark ms-auto">
                    <i class="bi bi-plus-lg me-1"></i> Add New Client
                </a>
            </form>
        </div>
    </div>


    <form id="bulkDeleteForm" action="{{ route('admin.clients.bulkDelete') }}" method="POST">
        @csrf
        @method('DELETE')

        <div class="d-flex align-items-center mb-2 justify-content-start">
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="clearSelectionClients" class="btn btn-sm btn-outline-secondary">Clear</button>
                <span id="selectedCounterClients" class="badge bg-primary">0 Selected</span>
            </div>
        </div>

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
                                @php
                                    $phone = preg_replace('/\D+/', '', $client->phone ?? '');

                                    $packagesUrl = $packagesUrl ?? null;

                                    $payOrReply = $packagesUrl
                                        ? "View & buy: {$packagesUrl}"
                                        : "Reply *YES* and I'll recommend the best package for you";

                                    $lines = array_filter([
                                        "👋 *{$client->name}*, looking for premium IPTV?",
                                        '✅ Live TV + VOD | HD/4K ready | Smooth streaming',
                                        '📱 Works on all devices | 24/7 support',
                                        '',
                                        "🎉 Today's deal: Free setup + instant activation",
                                        "👉 {$payOrReply}",
                                    ]);

                                    $message = rawurlencode(implode("\n", $lines));

                                    $waUniversal = $phone ? "https://wa.me/{$phone}?text={$message}" : null;
                                    $waWeb = $phone
                                        ? "https://web.whatsapp.com/send?phone={$phone}&text={$message}"
                                        : null;
                                    $waBusinessAndroid = $phone
                                        ? "intent://send/?phone={$phone}&text={$message}#Intent;scheme=whatsapp;package=com.whatsapp.w4b;end"
                                        : null;
                                @endphp


                                <div class="d-inline-flex align-items-center gap-1">
                                    @if ($waUniversal)
                                        <a href="{{ $waUniversal }}" target="_blank" rel="noopener"
                                            class="btn btn-sm btn-outline-success wa-btn"
                                            data-android="{{ $waBusinessAndroid }}" data-web="{{ $waWeb }}">
                                            WhatsApp
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.clients.edit', $client) }}"
                                        class="btn btn-sm btn-outline-primary">Edit</a>
                                </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bulkDeleteForm');
        const checkAll = document.getElementById('checkAll');
        const counterEl = document.getElementById('selectedCounterClients');
        const clearBtn = document.getElementById('clearSelectionClients');
        const deleteBtn = form?.querySelector('button[type="submit"].btn-danger');

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.wa-btn');
            if (!btn) return;

            const ua = navigator.userAgent || navigator.vendor || window.opera;
            const isAndroid = /Android/i.test(ua);
            const isDesktop = !/Android|iPhone|iPad|iPod/i.test(ua);

            if (isAndroid && btn.dataset.android) {
                btn.href = btn.dataset.android;
                btn.removeAttribute('target');
            } else if (isDesktop && btn.dataset.web) {
                btn.href = btn.dataset.web;
            }
        }, {
            capture: true
        });

        function rowBoxes() {
            return Array.from(document.querySelectorAll('input[name="client_ids[]"]'));
        }

        function updateSelectedState() {
            const boxes = rowBoxes();
            const total = boxes.length;
            const checked = boxes.filter(cb => cb.checked).length;

            if (counterEl) counterEl.textContent = `${checked} Selected`;
            if (deleteBtn) deleteBtn.disabled = (checked === 0);

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

        // Header Select All
        checkAll?.addEventListener('change', function() {
            rowBoxes().forEach(cb => cb.checked = this.checked);
            updateSelectedState();
        });

        // Row checkbox changes
        document.addEventListener('change', function(e) {
            if (e.target && e.target.matches('input[name="client_ids[]"]')) {
                updateSelectedState();
            }
        });

        // Clear selection
        clearBtn?.addEventListener('click', function() {
            rowBoxes().forEach(cb => cb.checked = false);
            if (checkAll) {
                checkAll.indeterminate = false;
                checkAll.checked = false;
            }
            updateSelectedState();
        });

        // On first render
        updateSelectedState();
    });
</script>
