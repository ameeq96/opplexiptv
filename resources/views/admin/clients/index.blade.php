@extends('admin.layouts.app')

@section('page_title', 'Customers · 360')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h3 class="mb-1">Customer 360</h3><p class="text-muted mb-0">Contacts, subscriptions, conversations and follow-ups in one place.</p></div>
        <div class="d-flex gap-2">
            @if (auth('admin')->user()->isOwner())
                <a href="{{ route('admin.clients.unlinked') }}" class="btn btn-outline-primary">Review unlinked activity</a>
            @endif
            @if (auth('admin')->user()->hasRole('owner', 'sales'))
                <a href="{{ route('admin.clients.export.crm', request()->only(['search', 'assigned_admin_id', 'subscription', 'payment', 'follow_up', 'source', 'consent', 'exclude_iptv'])) }}" class="btn btn-primary">Export filtered customer / order CSV</a>
            @endif
        </div>
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

    @if (array_sum($unlinkedActivity) > 0)
        <div class="alert alert-warning">
            <strong>Unlinked activity:</strong>
            {{ $unlinkedActivity['trial_clicks'] ?? 0 }} WhatsApp lead(s) and
            {{ $unlinkedActivity['checkout_drafts'] ?? 0 }} checkout draft(s),
            {{ $unlinkedActivity['customer_activities'] ?? 0 }} activity event(s) and
            {{ $unlinkedActivity['customer_conversations'] ?? 0 }} conversation(s) do not have a safe client match yet.
            Ambiguous records remain separate to avoid attaching data to the wrong customer.
        </div>
    @endif

    <div class="admin-card mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h5 class="mb-1">WhatsApp Number Update</h5>
                <div class="text-muted">
                    New support number: <strong>{{ config('services.whatsapp.display') }}</strong>
                </div>
                <div class="small text-muted mt-1">
                    {{ $contactUpdateEligible }} client(s) with an active or paid order and active email permission are eligible.
                    Each client is scheduled only once for this number.
                    The existing scheduler and queue deliver the emails.
                </div>
            </div>
            <form action="{{ route('admin.clients.contact-number-update') }}" method="POST"
                onsubmit="return confirm('Schedule the WhatsApp number update email for all eligible clients?');">
                @csrf
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="btn btn-success" @disabled($contactUpdateEligible === 0 || config('mail.default') === 'log')>
                    Schedule Update Email
                </button>
            </form>
        </div>
        @if (config('mail.default') === 'log')
            <div class="alert alert-warning mt-3 mb-0">
                Real email delivery is not configured. Set a production mail provider before using this campaign.
            </div>
        @endif
    </div>

    <div class="admin-card mb-4">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="row g-3">
            <div class="col-md-4"><label class="form-label" for="crm-search">Name, email or phone</label><input id="crm-search" name="search" value="{{ request('search') }}" maxlength="255" class="form-control"></div>
            <div class="col-md-4"><label class="form-label" for="crm-assignee">Assigned to</label><select id="crm-assignee" name="assigned_admin_id" class="form-select"><option value="">Everyone</option><option value="unassigned" @selected(request('assigned_admin_id') === 'unassigned')>Unassigned</option>@foreach ($admins as $admin)<option value="{{ $admin->id }}" @selected((string) request('assigned_admin_id') === (string) $admin->id)>{{ $admin->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="crm-subscription">Subscription</label><select id="crm-subscription" name="subscription" class="form-select"><option value="">All</option>@foreach (['active' => 'Active and paid', 'expired' => 'Past end date (paid)', 'expiring' => 'Active: expires within 7 days'] as $value => $label)<option value="{{ $value }}" @selected(request('subscription') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="crm-payment">Payment</label><select id="crm-payment" name="payment" class="form-select"><option value="">All</option>@foreach (['paid' => 'Paid', 'unpaid' => 'Unpaid', 'test_paid' => 'Test payment'] as $value => $label)<option value="{{ $value }}" @selected(request('payment') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="crm-follow-up">Follow-ups</label><select id="crm-follow-up" name="follow_up" class="form-select"><option value="">All</option><option value="pending" @selected(request('follow_up') === 'pending')>Pending</option><option value="overdue" @selected(request('follow_up') === 'overdue')>Overdue</option></select></div>
            <div class="col-md-4"><label class="form-label" for="crm-source">Captured activity source</label><select id="crm-source" name="source" class="form-select"><option value="">All</option>@foreach ($activitySources as $source)<option value="{{ $source }}" @selected(request('source') === $source)>{{ $source }}</option>@endforeach</select></div>
            @foreach (['consent', 'exclude_iptv', 'per_page'] as $filter)
                @if (request()->filled($filter))<input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">@endif
            @endforeach
            <div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit">Apply CRM filters</button><a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">Reset</a></div>
        </form>
    </div>

    <div class="admin-card mb-4">
        <form action="{{ route('admin.clients.index') }}" method="GET" class="admin-toolbar">
            @foreach (['assigned_admin_id', 'subscription', 'payment', 'follow_up', 'source'] as $filter)
                @if (request()->filled($filter))<input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">@endif
            @endforeach
            <select name="per_page" class="form-select" onchange="this.form.submit()">
                @foreach ([10, 20, 30, 40, 100] as $size)
                    <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                        Show {{ $size }}
                    </option>
                @endforeach
            </select>

            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                placeholder="Search clients...">

            <button type="submit" class="btn btn-primary">Search</button>

            <input type="file" name="csv_file" accept=".csv" class="form-control file-input"
                form="clientCsvImportForm" aria-label="Client CSV file" required>
            <button type="submit" form="clientCsvImportForm" class="btn btn-success">
                Import CSV
            </button>

            <a href="{{ route('admin.clients.export.facebook') }}" class="btn btn-warning">
                Export Ads-Consented CSV
            </a>

            <select name="consent" class="form-select" onchange="this.form.submit()">
                <option value="">All Consent States</option>
                <option value="email" @selected(request('consent') === 'email')>Email Consented</option>
                <option value="whatsapp" @selected(request('consent') === 'whatsapp')>WhatsApp Consented</option>
                <option value="ads" @selected(request('consent') === 'ads')>Ads Consented</option>
            </select>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="exclude_iptv" value="1" id="excludeIPTV"
                    onchange="this.form.submit()" {{ request('exclude_iptv') ? 'checked' : '' }}>
                <label class="form-check-label" for="excludeIPTV">
                    Exclude IPTV Clients
                </label>
            </div>

            <div class="actions">
                <a href="{{ route('admin.clients.create') }}" class="btn btn-dark">
                    <i class="bi bi-plus-lg me-1"></i> Add
                </a>
            </div>
        </form>
        <form id="clientCsvImportForm" action="{{ route('admin.clients.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
        </form>
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
                        <th>Email / assignment</th>
                        <th>Orders / follow-ups</th>
                        <th>Country</th>
                        <th>Consent</th>
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
                            <td><div>{{ $client->email ?: '—' }}</div><small class="text-muted">{{ $client->assignedAdmin?->name ?: 'Unassigned' }}</small></td>
                            <td>{{ $client->orders_count }} orders<br><span class="badge {{ $client->pending_follow_ups_count ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ $client->pending_follow_ups_count }} pending follow-ups</span></td>
                            <td>{{ $client->country ?? 'N/A' }}</td>
                            <td>
                                <span class="badge-soft {{ $client->hasMarketingConsent('email') ? 'success' : 'gray' }}">Email</span>
                                <span class="badge-soft {{ $client->hasMarketingConsent('whatsapp') ? 'success' : 'gray' }}">WhatsApp</span>
                                <span class="badge-soft {{ $client->hasMarketingConsent('ads') ? 'success' : 'gray' }}">Ads</span>
                            </td>
                            <td>
                                @php
                                    $phone = preg_replace('/\D+/', '', $client->phone ?? '');

                                    $packagesUrl = $packagesUrl ?? null;

                                    $payOrReply = $packagesUrl
                                        ? "View & buy: {$packagesUrl}"
                                        : "Reply *YES* and I'll recommend the best package for you";

                                    $lines = array_filter([
                                        "ðŸ‘‹ *{$client->name}*, looking for premium IPTV?",
                                        'âœ… Live TV + VOD | HD/4K ready | Smooth streaming',
                                        'ðŸ“± Works on popular supported devices | 24/7 support',
                                        '',
                                        "ðŸŽ‰ Today's deal: Free setup + activation after payment confirmation",
                                        "ðŸ‘‰ {$payOrReply}",
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
                                    @if ($waUniversal && $client->hasMarketingConsent('whatsapp'))
                                        <a href="{{ $waUniversal }}" target="_blank" rel="noopener"
                                            class="btn btn-sm btn-outline-success wa-btn"
                                            data-android="{{ $waBusinessAndroid }}" data-web="{{ $waWeb }}">
                                            WhatsApp
                                        </a>
                                    @endif

                                    @if ($waUniversal && !$client->hasMarketingConsent('whatsapp'))
                                        <span class="badge-soft gray">No WhatsApp consent</span>
                                    @endif

                                    <a href="{{ route('admin.clients.show', $client) }}"
                                        class="btn btn-sm btn-outline-dark">360 View</a>

                                    <a href="{{ route('admin.clients.edit', $client) }}"
                                        class="btn btn-sm btn-outline-primary">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-muted">No clients found.</td>
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

@push('scripts')
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
@endpush
