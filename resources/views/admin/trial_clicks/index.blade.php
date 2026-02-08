@extends('admin.layouts.app')

@section('page_title', 'WhatsApp Trial Clicks')

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.trial_clicks.index') }}" method="GET"
                  class="d-flex flex-wrap align-items-center gap-2">

                <select name="per_page" class="form-select w-auto" onchange="this.form.submit()">
                    @foreach ([10, 20, 30, 40, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                            Show {{ $size }}
                        </option>
                    @endforeach
                </select>

                <input type="date" name="from" value="{{ request('from') }}" class="form-control w-auto" />
                <input type="date" name="to" value="{{ request('to') }}" class="form-control w-auto" />

                <input type="text" name="search" value="{{ request('search') }}" class="form-control w-auto"
                       placeholder="Search (page, fbp, fbc, ip, campaign...)">

                <button type="submit" class="btn btn-primary">Search</button>

                <a href="{{ route('admin.trial_clicks.export') }}" class="btn btn-outline-secondary ms-auto">
                    Export CSV
                </a>
            </form>
        </div>
    </div>

    {{-- Stats badges --}}
    <div class="row g-3 mb-3">
        <div class="col-auto"><span class="badge bg-primary">Today: {{ $today }}</span></div>
        <div class="col-auto"><span class="badge bg-success">Last 7d: {{ $last7 }}</span></div>
        <div class="col-auto"><span class="badge bg-secondary">Last 30d: {{ $last30 }}</span></div>
    </div>

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
                        <th style="min-width: 160px;">Time</th>
                        <th style="min-width: 180px;">Event ID</th>
                        <th style="min-width: 300px;">Page</th>
                        <th style="min-width: 300px;">Destination</th>
                        <th style="min-width: 160px;">UTM Campaign</th>
                        <th style="min-width: 160px;">fbp</th>
                        <th style="min-width: 160px;">fbc</th>
                        <th style="min-width: 140px;">IP</th>
                        <th style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clicks as $c)
                        <tr>
                            <td>
                                <input type="checkbox" name="trial_ids[]" value="{{ $c->id }}">
                            </td>

                            <td>{{ $c->created_at->format('Y-m-d H:i') }}</td>
                            <td title="{{ $c->event_id }}">
                                {{ \Illuminate\Support\Str::limit($c->event_id, 16, 'â€¦') }}
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

                            <td>{{ $c->utm_campaign ?: 'â€”' }}</td>
                            <td title="{{ $c->fbp }}">{{ \Illuminate\Support\Str::limit($c->fbp, 18, 'â€¦') ?: 'â€”' }}</td>
                            <td title="{{ $c->fbc }}">{{ \Illuminate\Support\Str::limit($c->fbc, 18, 'â€¦') ?: 'â€”' }}</td>
                            <td>{{ $c->ip ?: 'â€”' }}</td>

                            <td>
                                <form action="{{ route('admin.trial_clicks.destroy', $c) }}" method="POST"
                                      onsubmit="return confirm('Delete this record?')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-muted">No trial clicks found.</td>
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

