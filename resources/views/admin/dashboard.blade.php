@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Home</li>
@endsection

@section('content')
    @php
        $currentFilter = $filter ?? 'all';
        $rangeLabels = ['all' => 'All time', 'today' => 'Today', 'yesterday' => 'Yesterday', '7days' => 'Last 7 days', '30days' => 'Last 30 days', '90days' => 'Last 90 days', 'year' => 'This year', 'custom' => 'Custom range'];
        $rangeLabel = $rangeLabels[$currentFilter] ?? 'All time';
        $dashboardAdmin = auth('admin')->user();
        $canManageCustomers = $dashboardAdmin?->isOwner() || $dashboardAdmin?->hasRole(\App\Models\Admin::ROLE_SALES) || $dashboardAdmin?->hasRole(\App\Models\Admin::ROLE_SUPPORT);
    @endphp

    <div class="dashboard-intro">
        <div>
            <span class="dashboard-eyebrow">Business overview</span>
            <h1>Your business, at a glance.</h1>
            <p>Keep track of your customers, orders and performance in one place.</p>
        </div>
        <div class="dashboard-period"><i class="bi bi-calendar3" aria-hidden="true"></i><span>{{ $rangeLabel }}</span></div>
    </div>

    <div class="admin-card dashboard-filter-bar mb-4">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="dashboard-filter" class="form-label">Reporting period</label>
                <select id="dashboard-filter" name="filter" class="form-select">
                    <option value="all" @selected($currentFilter === 'all')>All Time</option>
                    <option value="today" @selected($currentFilter === 'today')>Today</option>
                    <option value="yesterday" @selected($currentFilter === 'yesterday')>Yesterday</option>
                    <option value="7days" @selected($currentFilter === '7days')>Last 7 Days</option>
                    <option value="30days" @selected($currentFilter === '30days')>Last 30 Days</option>
                    <option value="90days" @selected($currentFilter === '90days')>Last 90 Days</option>
                    <option value="year" @selected($currentFilter === 'year')>This Year</option>
                    <option value="custom" @selected($currentFilter === 'custom')>Custom</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="dashboard-start" class="form-label">From</label>
                <input id="dashboard-start" type="date" name="start_date" value="{{ old('start_date', $startDate ? \Illuminate\Support\Carbon::parse($startDate)->toDateString() : '') }}" class="form-control">
            </div>
            <div class="col-12 col-md-3">
                <label for="dashboard-end" class="form-label">To</label>
                <input id="dashboard-end" type="date" name="end_date" value="{{ old('end_date', $endDate ? \Illuminate\Support\Carbon::parse($endDate)->toDateString() : '') }}" class="form-control">
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit"><i class="bi bi-funnel me-1" aria-hidden="true"></i> Apply filters</button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="admin-grid dashboard-stats mb-4">
        <div class="stat-card dashboard-stat">
            <div class="dashboard-stat-top"><span>Total orders</span><span class="dashboard-stat-icon indigo"><i class="bi bi-box-seam" aria-hidden="true"></i></span></div>
            <h4 data-admin-api-field="totalOrders" data-admin-api-format="integer">{{ number_format($totalOrders ?? 0) }}</h4>
            <p class="dashboard-stat-meta">Orders in the selected period</p>
        </div>
        <div class="stat-card dashboard-stat">
            <div class="dashboard-stat-top"><span>Active orders</span><span class="dashboard-stat-icon green"><i class="bi bi-check2-circle" aria-hidden="true"></i></span></div>
            <h4 data-admin-api-field="activeOrders" data-admin-api-format="integer">{{ number_format($activeOrders ?? 0) }}</h4>
            <p class="dashboard-stat-meta">Not yet past their expiry date</p>
        </div>
        <div class="stat-card dashboard-stat">
            <div class="dashboard-stat-top"><span>Expired orders</span><span class="dashboard-stat-icon amber"><i class="bi bi-clock-history" aria-hidden="true"></i></span></div>
            <h4 data-admin-api-field="expiredOrders" data-admin-api-format="integer">{{ number_format($expiredOrders ?? 0) }}</h4>
            <p class="dashboard-stat-meta">Past their expiry date</p>
        </div>
        <div class="stat-card dashboard-stat">
            <div class="dashboard-stat-top"><span>Total customers</span><span class="dashboard-stat-icon blue"><i class="bi bi-people" aria-hidden="true"></i></span></div>
            <h4 data-admin-api-field="users" data-admin-api-format="integer">{{ number_format($users ?? 0) }}</h4>
            <p class="dashboard-stat-meta">All customer records, across all time</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <section class="admin-card dashboard-finance-panel h-100" aria-labelledby="earnings-heading">
                <div class="dashboard-panel-heading">
                    <div><h2 id="earnings-heading">Earnings by currency</h2><p>Financial totals, with currencies kept separate.</p></div>
                    <span class="dashboard-panel-icon"><i class="bi bi-bar-chart" aria-hidden="true"></i></span>
                </div>
                @if (count($earningsByCurrency ?? []) > 0)
                <div class="table-responsive mb-4">
                    <table class="table align-middle mb-0 dashboard-earnings-table">
                        <thead>
                            <tr>
                                <th>Currency</th>
                                <th class="text-end">Earnings</th>
                                <th class="text-end">Purchasing</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($earningsByCurrency as $ccy => $amount)
                                <tr>
                                    <td><span class="dashboard-currency">{{ $ccy }}</span></td>
                                    <td class="text-end fw-semibold">{{ number_format($amount, 2) }}</td>
                                    <td class="text-end">{{ number_format($purchasingByCurrency[$ccy] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="dashboard-chart-wrap dashboard-earnings-chart"><canvas id="earningsChart" height="220" role="img" aria-label="Earnings by currency for {{ $rangeLabel }}">Earnings are listed in the table above.</canvas></div>
                @else
                    <div class="dashboard-empty-state">
                        <span class="dashboard-empty-icon"><i class="bi bi-bar-chart-line" aria-hidden="true"></i></span>
                        <h3>No earnings to show yet</h3>
                        <p>Your earnings and purchasing totals will appear here when records are available for this period.</p>
                    </div>
                @endif
                <p class="dashboard-panel-note"><i class="bi bi-info-circle" aria-hidden="true"></i> {{ $currentFilter === 'all' ? 'All-time earnings include purchasing deductions.' : 'Earnings are shown before purchasing deductions for this period.' }}</p>
            </section>
        </div>
        <div class="col-12 col-xl-6">
            <section class="admin-card dashboard-finance-panel h-100" aria-labelledby="purchasing-heading">
                <div class="dashboard-panel-heading">
                    <div><h2 id="purchasing-heading">Purchasing activity</h2><p>See how purchasing changes over time.</p></div>
                    <span class="badge-soft gray">{{ $currentFilter === 'all' ? 'Last 30 days' : $rangeLabel }}</span>
                </div>
                @if (count($purchasingSeriesForChart ?? []) > 0)
                    <div class="dashboard-chart-wrap dashboard-purchasing-chart"><canvas id="purchasingChart" height="260" role="img" aria-label="Purchasing totals by date and currency"></canvas></div>
                @else
                    <div class="dashboard-empty-state">
                        <span class="dashboard-empty-icon"><i class="bi bi-graph-up" aria-hidden="true"></i></span>
                        <h3>No purchases in this period</h3>
                        <p>No purchasing records in this period. Your activity will appear here as purchases are recorded.</p>
                        @if ($dashboardAdmin?->isOwner())
                            <a href="{{ route('admin.purchasing.index') }}" class="dashboard-text-link">View purchasing <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        @endif
                    </div>
                @endif
                <p class="dashboard-panel-note"><i class="bi bi-calendar3" aria-hidden="true"></i> {{ $startDate && $endDate ? \Illuminate\Support\Carbon::parse($startDate)->format('d M Y').' – '.\Illuminate\Support\Carbon::parse($endDate)->format('d M Y') : 'Showing the last 30 days of purchasing activity.' }}</p>
            </section>
        </div>
    </div>

    @if ($canManageCustomers)
        <div class="dashboard-shortcuts">
            <a href="{{ route('admin.clients.index') }}" class="dashboard-shortcut"><span class="dashboard-shortcut-icon"><i class="bi bi-people" aria-hidden="true"></i></span><span><strong>Customer workspace</strong><small>Profiles, conversations and follow-ups</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            <a href="{{ route('admin.orders.index') }}" class="dashboard-shortcut"><span class="dashboard-shortcut-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><span><strong>Manage orders</strong><small>Review package orders and subscriptions</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            <a href="{{ route('admin.trial_clicks.index') }}" class="dashboard-shortcut"><span class="dashboard-shortcut-icon"><i class="bi bi-chat-left-text" aria-hidden="true"></i></span><span><strong>WhatsApp leads</strong><small>Keep up with customer enquiries</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
        </div>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const earningsData = @json($earningsByCurrency ?? []);
    const purchasingSeries = @json($purchasingSeriesForChart ?? []);

    const earningLabels = Object.keys(earningsData || {});
    const earningValues = earningLabels.map((k) => Number(earningsData[k] || 0));

    if (document.getElementById('earningsChart')) {
        new Chart(document.getElementById('earningsChart'), {
            type: 'bar',
            data: {
                labels: earningLabels,
                datasets: [{
                    label: 'Earnings',
                    data: earningValues,
                    backgroundColor: '#7072df',
                    borderColor: '#5658d9',
                    borderWidth: 0,
                    borderRadius: 6,
                    maxBarThickness: 56
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { color: '#7b8496', font: { family: 'Manrope' } } },
                    y: { beginAtZero: true, grid: { color: '#edf0f4' }, border: { display: false }, ticks: { color: '#7b8496', font: { family: 'Manrope' } } }
                }
            }
        });
    }

    if (document.getElementById('purchasingChart')) {
        const dateSet = new Set();
        purchasingSeries.forEach(series => {
            (series.data || []).forEach(point => dateSet.add(point.x));
        });
        const labels = Array.from(dateSet).sort();

        const chartColors = ['#5658d9', '#159c89', '#ce9237', '#4c8bc7', '#ad67ba', '#d27177'];
        const datasets = purchasingSeries.map((series, index) => {
            const dataMap = new Map((series.data || []).map(point => [point.x, Number(point.y || 0)]));
            return {
                label: series.name,
                data: labels.map(l => dataMap.get(l) ?? 0),
                borderColor: chartColors[index % chartColors.length],
                backgroundColor: chartColors[index % chartColors.length],
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                fill: false,
                tension: 0.25
            };
        });

        new Chart(document.getElementById('purchasingChart'), {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 6, boxHeight: 6, padding: 24, color: '#626e82', font: { family: 'Manrope' } } }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { maxTicksLimit: 6, maxRotation: 0, color: '#7b8496', font: { family: 'Manrope', size: 11 } } },
                    y: { beginAtZero: true, grid: { color: '#edf0f4' }, border: { display: false }, ticks: { color: '#7b8496', font: { family: 'Manrope', size: 11 } } }
                }
            }
        });
    }
</script>
@endpush
