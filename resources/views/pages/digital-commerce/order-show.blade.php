@extends('layouts.default')
@section('title', 'Order Details')

@section('content')
<section class="shop-section mt-5" style="background-image:url('{{ asset('images/background/4.webp') }}');">
    <div class="auto-container">
        <div class="bg-white rounded p-4">
            <h3 class="mb-2">Order {{ $digital_order->order_number }}</h3>
            <p class="text-muted">Status: {{ ucfirst($digital_order->status) }} | Payment: {{ ucfirst($digital_order->payment_status) }}</p>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Delivery</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($digital_order->items as $item)
                        <tr>
                            <td>{{ $item->product_title }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format((float) $item->line_total, 2) }}</td>
                            <td>
                                @if($item->deliveryPayload)
                                    <div><strong>Status:</strong> {{ ucfirst($item->delivery_status) }}</div>
                                    <div><strong>Preview:</strong> {{ $item->deliveryPayload->maskedPreview() }}</div>
                                    @if($item->delivery_status === 'delivered')
                                        <details>
                                            <summary>Reveal Delivery Data</summary>
                                            <pre class="mt-2 mb-0">{{ json_encode($item->deliveryPayload->payload, JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    @endif
                                @else
                                    <span class="text-muted">Pending assignment</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
