@extends('admin.layouts.app')

@section('page_title', 'WhatsApp Leads')

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Trial details were not saved.</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filters card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.trial_clicks.index') }}" method="GET"
                  class="d-flex flex-wrap align-items-center gap-2">

                <select name="per_page" class="form-select w-auto" onchange="this.form.requestSubmit()">
                    @foreach ([10, 20, 30, 40, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                            Show {{ $size }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="form-select w-auto" onchange="this.form.requestSubmit()">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>

                <input type="date" name="from" value="{{ request('from') }}" class="form-control w-auto" />
                <input type="date" name="to" value="{{ request('to') }}" class="form-control w-auto" />

                <select name="landing_brand" class="form-select w-auto" aria-label="Landing brand" onchange="this.form.requestSubmit()">
                    <option value="">All lead sources</option>
                    @foreach ($landingBrands as $brand => $label)
                        <option value="{{ $brand }}" @selected(request('landing_brand') === $brand)>{{ $label }}</option>
                    @endforeach
                </select>

                <input type="text" name="search" value="{{ request('search') }}" class="form-control w-auto"
                       placeholder="Search name, phone, lead, package...">

                <button type="submit" class="btn btn-primary">Search</button>

                <a href="{{ route('admin.trial_clicks.export') }}" class="btn btn-outline-secondary ms-auto">
                    Export all leads CSV
                </a>
            </form>
        </div>
    </div>

    {{-- Stats badges --}}
    <div class="row g-3 mb-3">
        <div class="col-auto"><span class="badge bg-primary">Today: {{ $today }}</span></div>
        <div class="col-auto"><span class="badge bg-success">Last 7d: {{ $last7 }}</span></div>
        <div class="col-auto"><span class="badge bg-secondary">Last 30d: {{ $last30 }}</span></div>
        <div class="col-auto"><span class="badge bg-info text-dark">New: {{ $newLeads }}</span></div>
        <div class="col-auto"><span class="badge bg-dark">Marked paid (manual): {{ $paidLeads }}</span></div>
    </div>

    <section class="card shadow-sm mb-4" aria-labelledby="landingFunnelTitle">
        <div class="card-body">
            <h2 id="landingFunnelTitle" class="h5">Landing activity</h2>
            <p class="small text-muted mb-3"><span data-admin-api-field="landingFunnel.from">{{ $landingFunnel['from'] }}</span> to <span data-admin-api-field="landingFunnel.to">{{ $landingFunnel['to'] }}</span>. Visits and checkout clicks count distinct opted-in sessions per brand, not people. Contact requests count saved submissions, including requests without analytics consent. A checkout click does not confirm completion or payment.</p>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-2">
                    <thead><tr><th scope="col">Brand</th><th scope="col">Consented visits</th><th scope="col">Contact requests</th><th scope="col">Checkout clicks</th><th scope="col">Verified paid orders</th></tr></thead>
                    <tbody>
                        @foreach ($landingFunnel['brands'] as $row)
                            <tr>
                                <th scope="row">{{ $row['name'] }}</th>
                                <td data-admin-api-field="landingFunnel.brands.{{ $loop->index }}.visits">{{ $row['visits'] }}</td>
                                <td data-admin-api-field="landingFunnel.brands.{{ $loop->index }}.contacts">{{ $row['contacts'] }}</td>
                                <td data-admin-api-field="landingFunnel.brands.{{ $loop->index }}.checkout_clicks">{{ $row['checkout_clicks'] }}</td>
                                @if ($landingFunnel['payment_attribution'])
                                    <td data-admin-api-field="landingFunnel.brands.{{ $loop->index }}.paid_orders">{{ $row['paid_orders'] }}</td>
                                @else
                                    <td>Pending connection</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="small text-muted mb-2">Source and date filters apply to this activity report. Search/status filters apply to the lead list; the badges above show all lead entries. Browser event counts are indicative, not audited sales figures.</p>
            @if ($landingFunnel['payment_attribution'])
                <p class="small text-muted mb-0">Paid orders count distinct, confirmed production SafePay orders attributed to a landing brand. Test payments and manually marked lead statuses are excluded. Paid attribution does not require visitor analytics consent; visit/click totals do. These are different populations, not a conversion-rate calculation. Historical orders without attribution are not assigned to a brand.</p>
            @else
                <p class="small text-muted mb-0">Payment attribution is not connected yet. The authenticated payment connection must be verified before paid-order reporting is enabled.</p>
            @endif
        </div>
    </section>

    {{-- Bulk delete form --}}
    <form id="bulkDeleteTrials" action="{{ route('admin.trial_clicks.bulkDelete') }}" method="POST">
        @csrf
        @method('DELETE')

        <div class="d-flex align-items-center mb-2 justify-content-start">
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="clearSelectionTrials" class="btn btn-sm btn-outline-secondary">Clear</button>
                <span id="selectedCounterTrials" class="badge bg-primary">0 Selected</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 120px;">
                            <input type="checkbox" id="checkAllTrials">
                        </th>
                        <th style="min-width: 160px;">Latest activity</th>
                        <th style="min-width: 150px;">Lead ID</th>
                        <th style="min-width: 220px;">Customer</th>
                        <th style="min-width: 260px;">Message</th>
                        <th style="min-width: 220px;">Lead details</th>
                        <th style="min-width: 300px;">Page</th>
                        <th style="min-width: 300px;">Destination</th>
                        <th style="min-width: 160px;">UTM Campaign</th>
                        <th style="min-width: 160px;">fbp</th>
                        <th style="min-width: 160px;">fbc</th>
                        <th style="min-width: 140px;">IP</th>
                        <th style="min-width: 150px;">Status</th>
                        <th style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clicks as $c)
                        @php
                            $customerName = $c->contact_name ?: $c->user?->name;
                            $customerPhone = $c->phone_normalized ?: $c->user?->phone_normalized;
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="trial_ids[]" value="{{ $c->id }}">
                            </td>

                            <td>{{ ($c->whatsapp_contact_consented_at ?: $c->created_at)->format('Y-m-d H:i') }}</td>
                            <td title="{{ $c->event_id }}">
                                <strong data-admin-api-field="lead_code" data-admin-api-object="click" data-admin-api-id="{{ $c->id }}">{{ $c->lead_code }}</strong>
                            </td>

                            <td class="text-start">
                                @if ($customerName || $customerPhone)
                                    <div><strong>{{ $customerName ?: 'Unknown customer' }}</strong></div>
                                    @if ($customerPhone)
                                        <a href="https://wa.me/{{ $customerPhone }}" target="_blank" rel="noopener">
                                            +{{ $customerPhone }}
                                        </a>
                                    @endif
                                    @if ($c->contact_email)
                                        <div class="small" data-admin-api-field="contact_email" data-admin-api-object="click" data-admin-api-id="{{ $c->id }}">{{ $c->contact_email }}</div>
                                    @endif
                                    @if ($c->whatsapp_contact_consented_at)
                                        <div class="small text-muted">
                                            Form consent: {{ $c->whatsapp_contact_consented_at->format('Y-m-d H:i') }}
                                            @if ($c->click_count > 1)
                                                · {{ $c->click_count }} clicks
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-start" style="max-width: 360px; overflow-wrap: anywhere;">
                                @if ($c->message !== null && $c->message !== '')
                                    <details>
                                        <summary>{{ \Illuminate\Support\Str::limit($c->message, 90) }}</summary>
                                        <div class="small mt-2" style="white-space: pre-wrap;" data-admin-api-field="message" data-admin-api-object="click" data-admin-api-id="{{ $c->id }}">{{ $c->message }}</div>
                                    </details>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-start">
                                <div>{{ ucfirst(str_replace('_', ' ', $c->intent ?: 'contact')) }}</div>
                                @if ($c->package_name)
                                    <div class="small text-muted" data-admin-api-field="package_name" data-admin-api-object="click" data-admin-api-id="{{ $c->id }}">{{ $c->package_name }}</div>
                                @endif
                                @if ($c->vendor || $c->placement)
                                    <div class="small text-muted">
                                        {{ $c->vendor ?: 'Unknown vendor' }}
                                        {{ $c->placement ? ' · '.$c->placement : '' }}
                                    </div>
                                @endif
                                @if ($c->value !== null)
                                    <div class="small">{{ $c->currency ?: 'USD' }} {{ number_format((float) $c->value, 2) }}</div>
                                @endif
                                @if ($c->intent === 'trial')
                                <details class="mt-2" @if ((string) old('trial_record') === (string) $c->id) open @endif>
                                    <summary class="fw-semibold">Trial: {{ ucfirst($c->trial_status) }}</summary>
                                    <p class="small text-muted mt-2 mb-2">Only save actual trial activation dates after verifying the customer's account email. Clicking a lead does not start a trial. Times: {{ config('app.timezone') }}.</p>
                                    @php $trialOld = (string) old('trial_record') === (string) $c->id; @endphp
                                    <label class="form-label small" for="trialEmail{{ $c->id }}">Confirmed customer account email</label>
                                    <input type="email" id="trialEmail{{ $c->id }}" name="confirmed_account_email"
                                        form="trialDates{{ $c->id }}" class="form-control form-control-sm mb-2" required maxlength="255"
                                        value="{{ $trialOld ? old('confirmed_account_email') : ($c->trial_owner_verified_at ? $c->user?->email : '') }}">
                                    <label class="form-label small" for="trialStart{{ $c->id }}">Actual start</label>
                                    <input type="datetime-local" id="trialStart{{ $c->id }}" name="trial_started_at"
                                        form="trialDates{{ $c->id }}" class="form-control form-control-sm mb-2" required
                                        value="{{ $trialOld ? old('trial_started_at') : $c->trial_started_at?->format('Y-m-d\TH:i') }}">
                                    <label class="form-label small" for="trialEnd{{ $c->id }}">Actual expiry</label>
                                    <input type="datetime-local" id="trialEnd{{ $c->id }}" name="trial_expires_at"
                                        form="trialDates{{ $c->id }}" class="form-control form-control-sm mb-2" required
                                        value="{{ $trialOld ? old('trial_expires_at') : $c->trial_expires_at?->format('Y-m-d\TH:i') }}">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" id="trialOwner{{ $c->id }}" name="owner_confirmed" value="1"
                                            form="trialDates{{ $c->id }}" class="form-check-input" required>
                                        <label class="form-check-label small" for="trialOwner{{ $c->id }}">I confirmed this trial belongs to the account above.</label>
                                    </div>
                                    <button type="submit" form="trialDates{{ $c->id }}" class="btn btn-sm btn-outline-primary">Save trial dates</button>
                                </details>
                                @endif
                            </td>

                            <td class="text-start">
                                @if ($c->page)
                                    <a href="{{ $c->page }}" target="_blank" rel="noopener"
                                       title="{{ $c->page }}">
                                        {{ \Illuminate\Support\Str::limit($c->page, 60) }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                                @if ($c->utm_source || $c->utm_medium || $c->utm_campaign)
                                    <div class="small text-muted">
                                        {{ $c->utm_source ? 'src='.$c->utm_source : '' }}
                                        {{ $c->utm_medium ? ' â€¢ med='.$c->utm_medium : '' }}
                                        {{ $c->utm_campaign ? ' â€¢ cmp='.$c->utm_campaign : '' }}
                                    </div>
                                @endif
                            </td>

                            <td class="text-start">
                                @if ($c->destination)
                                    <a href="{{ $c->destination }}" target="_blank" rel="noopener"
                                       title="{{ $c->destination }}">
                                        {{ \Illuminate\Support\Str::limit($c->destination, 60) }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td data-admin-api-field="utm_campaign" data-admin-api-object="click" data-admin-api-id="{{ $c->id }}" data-admin-api-empty="â€”">{{ $c->utm_campaign ?: 'â€”' }}</td>
                            <td title="{{ $c->fbp }}">{{ \Illuminate\Support\Str::limit($c->fbp, 18, 'â€¦') ?: 'â€”' }}</td>
                            <td title="{{ $c->fbc }}">{{ \Illuminate\Support\Str::limit($c->fbc, 18, 'â€¦') ?: 'â€”' }}</td>
                            <td data-admin-api-field="ip" data-admin-api-object="click" data-admin-api-id="{{ $c->id }}" data-admin-api-empty="â€”">{{ $c->ip ?: 'â€”' }}</td>

                            <td>
                                <select class="form-select form-select-sm" data-lead-status
                                    data-current-status="{{ $c->status }}"
                                    data-status-url="{{ route('api.browser.admin.trial_clicks.status', $c) }}"
                                    aria-label="Status for {{ $c->lead_code }}">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" @selected($c->status === $status)>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <button type="submit" form="deleteTrial{{ $c->id }}" class="btn btn-sm btn-outline-danger">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-muted">No WhatsApp leads found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-danger mt-2"
                onclick="return confirm('Delete selected trial clicks?')">
            Delete Selected
        </button>
    </form>

    @foreach ($clicks as $c)
        <form id="deleteTrial{{ $c->id }}" action="{{ route('admin.trial_clicks.destroy', $c) }}" method="POST"
              onsubmit="return confirm('Delete this record?')">
            @csrf @method('DELETE')
        </form>
        @if ($c->intent === 'trial')
        <form id="trialDates{{ $c->id }}" action="{{ route('admin.trial_clicks.trial', $c) }}" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="trial_record" value="{{ $c->id }}">
        </form>
        @endif
    @endforeach

    <div class="mt-3 d-flex justify-content-center flex-column">
        {{ $clicks->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('bulkDeleteTrials');
            const checkAll = document.getElementById('checkAllTrials');
            const counterEl = document.getElementById('selectedCounterTrials');
            const clearBtn = document.getElementById('clearSelectionTrials');
            const deleteBtn = form?.querySelector('button[type="submit"].btn-danger');

            function rowBoxes() {
                return Array.from(document.querySelectorAll('input[name="trial_ids[]"]'));
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

            checkAll?.addEventListener('change', function () {
                rowBoxes().forEach(cb => cb.checked = this.checked);
                updateSelectedState();
            });

            document.addEventListener('change', function (e) {
                if (e.target && e.target.matches('input[name="trial_ids[]"]')) {
                    updateSelectedState();
                }
            });

            document.addEventListener('change', async function (e) {
                if (!e.target || !e.target.matches('[data-lead-status]')) return;

                const select = e.target;
                const previousStatus = select.dataset.currentStatus;
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                select.disabled = true;

                try {
                    const response = await fetch(select.dataset.statusUrl, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({ status: select.value })
                    });
                    if (!response.ok) throw new Error('Unable to update lead status.');
                    select.dataset.currentStatus = select.value;
                } catch (error) {
                    select.value = previousStatus;
                    window.alert(error.message);
                } finally {
                    select.disabled = false;
                }
            });

            clearBtn?.addEventListener('click', function () {
                rowBoxes().forEach(cb => cb.checked = false);
                if (checkAll) {
                    checkAll.indeterminate = false;
                    checkAll.checked = false;
                }
                updateSelectedState();
            });

            // initial
            updateSelectedState();
        });
    </script>

@endsection
