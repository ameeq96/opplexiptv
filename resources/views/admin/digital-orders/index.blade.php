@extends('admin.layouts.app')
@section('title', 'Digital Orders')
@section('content')
<div class="admin-card mb-3">
    <form method="GET" class="row g-2">
        <div class="col-lg-5"><input class="form-control" name="q" value="{{ $q }}" placeholder="Order/customer/email"></div>
        <div class="col-lg-3">
            <select class="form-select" name="status">
                <option value="">All Status</option>
                @foreach(['pending','paid','processing','delivered','cancelled','refunded'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2">
            <select class="form-select" name="payment_status">
                <option value="">All Payments</option>
                @foreach(['unpaid','paid','failed','refunded'] as $s)
                    <option value="{{ $s }}" @selected($paymentStatus === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 d-grid"><button class="btn btn-primary">Filter</button></div>
    </form>
</div>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Payment</th>
                <th class="text-end">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>
                        <div class="fw-semibold">{{ $order->customer_name }}</div>
                        <small class="text-muted">{{ $order->customer_email }}</small>
                    </td>
                    <td>{{ $order->items_count }}</td>
                    <td>${{ number_format((float) $order->total, 2) }}</td>
                    <td><span class="badge-soft gray">{{ ucfirst($order->status) }}</span></td>
                    <td><span class="badge-soft {{ $order->payment_status === 'paid' ? 'success' : 'gray' }}">{{ ucfirst($order->payment_status) }}</span></td>
                    <td class="text-end"><a href="{{ route('admin.digital-orders.show', $order) }}" class="btn btn-sm btn-light">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center p-4 text-muted">No orders found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
@endsection
