@extends('admin.layout.app')

@section('page_title', 'Dashboard')

@section('content')
    <div class="row row-cols-1 row-cols-md-4 g-3">
        {{-- Total Clients --}}
        <div class="col">
            <a href="{{ route('clients.index') }}" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5>Total Clients</h5>
                        <h3>{{ $users }}</h3>
                    </div>
                </div>
            </a>
        </div>

        <div class="col mt-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5>Total Orders</h5>
                    <h3>{{ $totalOrders }}</h3>
                </div>
            </div>
        </div>

        {{-- Active Orders --}}
        <div class="col">
            <a href="{{ route('orders.index', ['status' => 'active']) }}" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5>Active Orders</h5>
                        <h3>{{ $activeOrders }}</h3>
                    </div>
                </div>
            </a>
        </div>

        {{-- Expired Orders --}}
        <div class="col">
            <a href="{{ route('orders.index', ['status' => 'expired']) }}" class="text-decoration-none text-dark">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5>Expired Orders</h5>
                        <h3>{{ $expiredOrders }}</h3>
                    </div>
                </div>
            </a>
        </div>

        @foreach ($earningsByCurrency as $currency => $amount)
            @if ($amount > 0)
                <div class="col">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h5>Total Earnings ({{ $currency }})</h5>
                            <h3>{{ $currency }} {{ number_format($amount, 2) }}</h3>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        @foreach ($dailyEarningsByCurrency as $currency => $amount)
            @if ($amount > 0)
                <div class="col-md-4 mt-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5>Today's Earnings ({{ $currency }})</h5>
                            <h3>{{ $currency }} {{ number_format($amount, 2) }}</h3>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

    </div>
@endsection
