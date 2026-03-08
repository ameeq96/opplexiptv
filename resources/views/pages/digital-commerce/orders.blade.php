@extends('layouts.default')
@section('title', 'My Digital Orders')

@section('content')
<section class="shop-section mt-5" style="background-image:url('{{ asset('images/background/4.webp') }}');">
    <div class="auto-container">
        <div class="bg-white rounded p-4">
            <h3 class="mb-3">My Digital Orders</h3>
            @if($orders->isEmpty())
                <div class="alert alert-info">No orders found for your session.</div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                                    <td>{{ ucfirst($order->status) }}</td>
                                    <td>{{ ucfirst($order->payment_status) }}</td>
                                    <td>${{ number_format((float) $order->total, 2) }}</td>
                                    <td><a class="btn btn-sm btn-light" href="{{ route('digital.orders.show', $order) }}">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</section>
@endsection
