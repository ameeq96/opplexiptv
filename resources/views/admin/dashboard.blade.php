@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Home</li>
@endsection

@section('content')
    <div class="admin-card mb-4">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">Range</label>
                <select name="filter" class="form-select">
                    @php $currentFilter = $filter ?? 'all'; @endphp
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
                <label class="form-label">Start</label>
                <input type="date" name="start_date" value="{{ old('start_date', $startDate ? \Illuminate\Support\Carbon::parse($startDate)->toDateString() : '') }}" class="form-control">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">End</label>
                <input type="date" name="end_date" value="{{ old('end_date', $endDate ? \Illuminate\Support\Carbon::parse($endDate)->toDateString() : '') }}" class="form-control">
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button class="btn btn-primary w-100" type="submit">Apply</button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>

    <div class="admin-grid mb-4">
        <div class="stat-card">
            <span>Total Orders</span>
            <h4>{{ number_format($totalOrders ?? 0) }}</h4>
        </div>
        <div class="stat-card">
            <span>Active Orders</span>
            <h4>{{ number_format($activeOrders ?? 0) }}</h4>
        </div>
        <div class="stat-card">
            <span>Expired Orders</span>
            <h4>{{ number_format($expiredOrders ?? 0) }}</h4>
        </div>
        <div class="stat-card">
            <span>Total Users</span>
            <h4>{{ number_format($users ?? 0) }}</h4>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="admin-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">Earnings by Currency</h5>
                    <span class="badge-soft gray">{{ strtoupper($currentFilter ?? 'all') }}</span>
                </div>
                <div class="table-responsive mb-3">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Currency</th>
                                <th class="text-end">Earnings</th>
                                <th class="text-end">Purchasing</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($earningsByCurrency as $ccy => $amount)
                                <tr>
                                    <td class="fw-semibold">{{ $ccy }}</td>
                                    <td class="text-end">{{ number_format($amount, 2) }}</td>
                                    <td class="text-end">{{ number_format($purchasingByCurrency[$ccy] ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <canvas id="earningsChart" height="220" aria-label="Earnings chart"></canvas>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="admin-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">Purchasing Trend</h5>
                    <span class="badge-soft gray">{{ $currentFilter === 'custom' ? 'Custom Range' : 'Last 30 Days' }}</span>
                </div>
                <canvas id="purchasingChart" height="260" aria-label="Purchasing trend chart"></canvas>
            </div>
        </div>
    </div>
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
                    backgroundColor: 'rgba(22, 101, 216, 0.35)',
                    borderColor: 'rgba(22, 101, 216, 1)',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
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

        const datasets = purchasingSeries.map(series => {
            const dataMap = new Map((series.data || []).map(point => [point.x, Number(point.y || 0)]));
            return {
                label: series.name,
                data: labels.map(l => dataMap.get(l) ?? 0),
                borderWidth: 2,
                fill: false,
                tension: 0.25
            };
        });

        new Chart(document.getElementById('purchasingChart'), {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
</script>
@endpush