@extends('admin.layouts.app')
@section('title', 'Digital Order Details')
@section('content')
<div class="admin-card mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-1">{{ $digital_order->order_number }}</h5>
            <div class="text-muted">{{ $digital_order->customer_name }} - {{ $digital_order->customer_email }}</div>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.digital-orders.mark-paid', $digital_order) }}">@csrf<button class="btn btn-success btn-sm" type="submit">Mark Paid</button></form>
            <form method="POST" action="{{ route('admin.digital-orders.mark-delivered', $digital_order) }}">@csrf<button class="btn btn-primary btn-sm" type="submit">Mark Delivered</button></form>
            <form method="POST" action="{{ route('admin.digital-orders.resend-email', $digital_order) }}">@csrf<button class="btn btn-outline-secondary btn-sm" type="submit">Resend Email</button></form>
        </div>
    </div>
</div>

<div class="admin-card mb-3">
    <div class="row g-3">
        <div class="col-md-3"><strong>Status:</strong> {{ ucfirst($digital_order->status) }}</div>
        <div class="col-md-3"><strong>Payment:</strong> {{ ucfirst($digital_order->payment_status) }}</div>
        <div class="col-md-3"><strong>Method:</strong> {{ strtoupper((string) $digital_order->payment_method) }}</div>
        <div class="col-md-3"><strong>Total:</strong> ${{ number_format((float) $digital_order->total, 2) }}</div>
    </div>
</div>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Line Total</th>
                <th>Delivery Status</th>
                <th>Delivery Preview</th>
                <th>Assign</th>
            </tr>
            </thead>
            <tbody>
            @foreach($digital_order->items as $item)
                <tr>
                    <td>{{ $item->product_title }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format((float) $item->line_total, 2) }}</td>
                    <td><span class="badge-soft gray">{{ ucfirst($item->delivery_status) }}</span></td>
                    <td>{{ $item->deliveryPayload?->maskedPreview() ?? '-' }}</td>
                    <td>
                        @php($pool = $availablePayloads[$item->digital_product_id] ?? collect())
                        @if($item->delivery_payload_id)
                            <span class="text-success small">Assigned</span>
                        @elseif($pool->isEmpty())
                            <span class="text-muted small">No available payload</span>
                        @else
                            <form method="POST" action="{{ route('admin.digital-orders.assign-delivery', [$digital_order, $item]) }}" class="d-flex gap-2">
                                @csrf
                                <select class="form-select form-select-sm" name="payload_id" required>
                                    <option value="">Select</option>
                                    @foreach($pool as $payload)
                                        <option value="{{ $payload->id }}">#{{ $payload->id }} - {{ $payload->maskedPreview() }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-primary" type="submit">Assign</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
