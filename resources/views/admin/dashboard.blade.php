@extends('admin.layout.app')

@section('page_title', 'Dashboard')

@section('content')
    <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-end mb-4">
        <div class="col-md-2">
            <label class="form-label fw-semibold">Filter</label>
            <select name="filter" class="form-select" onchange="this.form.submit()">
                <option value="all" {{ request('filter') == 'all' ? 'selected' : '' }}>All</option>
                <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="7days" {{ request('filter') == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30days" {{ request('filter') == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>Custom Range</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Apply</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary w-100">Clear Filter</a>
        </div>
    </form>

    <div class="row row-cols-1 row-cols-md-4 g-4">
        <div class="col">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Clients</h6>
                    <h2 class="fw-bold">{{ $users }}</h2>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Orders {{ $filter != 'all' ? '(' . ucfirst($filter) . ')' : '' }}
                    </h6>
                    <h2 class="fw-bold">{{ $totalOrders }}</h2>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Active Orders {{ $filter != 'all' ? '(' . ucfirst($filter) . ')' : '' }}
                    </h6>
                    <h2 class="fw-bold">{{ $activeOrders }}</h2>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Expired Orders {{ $filter != 'all' ? '(' . ucfirst($filter) . ')' : '' }}
                    </h6>
                    <h2 class="fw-bold">{{ $expiredOrders }}</h2>
                </div>
            </div>
        </div>

        @foreach ($earningsByCurrency as $currency => $amount)
            @if ($amount > 0)
                <div class="col">
                    <div class="card shadow-sm border-0 h-100 bg-light">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">Total Earnings ({{ $currency }})
                                {{ $filter != 'all' ? '(' . ucfirst($filter) . ')' : '' }}</h6>
                            <h2 class="fw-bold">{{ $currency }} {{ number_format($amount, 2) }}</h2>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        @foreach ($purchasingByCurrency as $currency => $amount)
            @if ($amount > 0)
                <div class="col">
                    <div class="card shadow-sm border-0 h-100 bg-light">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">
                                Total Purchasing ({{ $currency }})
                                {{ $filter != 'all' ? '(' . ucfirst($filter) . ')' : '' }}
                            </h6>
                            <h2 class="fw-bold">{{ $currency }} {{ number_format($amount, 2) }}</h2>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="card mt-5 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-4">Earnings Chart {{ $filter != 'all' ? '(' . ucfirst($filter) . ')' : '' }}</h5>
            <div id="earningsChart"></div>
        </div>
    </div>

    <div class="card mt-4 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-4">Purchasing Chart {{ $filter != 'all' ? '(' . ucfirst($filter) . ')' : '' }}</h5>
            <div id="purchasingChart"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const options = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: true
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            series: [{
                name: 'Earnings',
                data: {!! json_encode(array_values($earningsByCurrency)) !!}
            }],
            xaxis: {
                categories: {!! json_encode(array_keys($earningsByCurrency)) !!},
                title: {
                    text: 'Currency'
                },
                labels: {
                    style: {
                        fontSize: '14px'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: val => val.toFixed(0),
                    style: {
                        fontSize: '14px'
                    }
                },
                title: {
                    text: 'Earnings'
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0,
                    stops: [0, 90, 100]
                }
            },
            colors: ['#0d6efd'],
            tooltip: {
                y: {
                    formatter: val => `${val.toFixed(2)}`
                }
            },
            grid: {
                borderColor: '#e0e0e0',
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };

        const purchasingOptions = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: true
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            series: {!! json_encode($purchasingSeriesForChart) !!}, // [ {name:'USD', data:[{x:'2025-10-01', y:123}, ...]}, ... ]
            xaxis: {
                type: 'datetime',
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: val => val.toFixed(0),
                    style: {
                        fontSize: '12px'
                    }
                },
                title: {
                    text: 'Purchasing'
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0,
                    stops: [0, 90, 100]
                }
            },
            tooltip: {
                x: {
                    format: 'yyyy-MM-dd'
                },
                y: {
                    formatter: val => `${val.toFixed(2)}`
                }
            },
            grid: {
                borderColor: '#e0e0e0',
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };

        new ApexCharts(document.querySelector("#purchasingChart"), purchasingOptions).render();
        const chart = new ApexCharts(document.querySelector("#earningsChart"), options);
        chart.render();
    </script>
@endsection
