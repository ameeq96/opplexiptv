@php
    $fmt = fn ($v) => '$' . number_format((float) $v, 2);
@endphp

<h2>{{ $isAdmin ? 'New digital order received' : 'Thanks for your digital order!' }}</h2>
<p>Order: <strong>{{ $order->order_number }}</strong></p>
<p>Customer: {{ $order->customer_name }} ({{ $order->customer_email }})</p>
<p>Total: {{ $fmt($order->total) }}</p>

<table style="border-collapse:collapse;width:100%;max-width:560px;" border="1" cellpadding="8">
    <thead>
    <tr>
        <th align="left">Product</th>
        <th align="left">Qty</th>
        <th align="left">Price</th>
        <th align="left">Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($order->items as $item)
        <tr>
            <td>{{ $item->product_title }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $fmt($item->unit_price) }}</td>
            <td>{{ $fmt($item->line_total) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

@if(!$isAdmin)
<p style="margin-top:12px;">
    Track your orders:
    <a href="{{ route('digital.orders.access', $order->customer_access_token) }}">Open dashboard</a>
</p>
@endif
