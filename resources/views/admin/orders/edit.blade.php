@extends('admin.layout.app')
@section('page_title', 'Edit Order')

@php
    $predefinedMethods = [
        'easypaisa',
        'jazzcash',
        'nayapay',
        'meezan bank',
        'alfalah bank',
        'sadapay',
        'skrill',
        'binance',
        'bitget',
        'remitly',
        'mexc',
    ];

    $isOther = !in_array(strtolower($order->payment_method), $predefinedMethods);
@endphp

@section('content')

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('orders.update', $order) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Client</label>
                        <select name="user_id" class="form-select" required>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ $order->user_id == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }} ({{ $client->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="package" class="form-label">Package Name</label>
                        <input type="text" name="package" class="form-control" value="{{ $order->package }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="price" class="form-control"
                                value="{{ $order->price }}" required>

                            <select name="currency" class="form-select" style="max-width: 120px;">
                                <option value="PKR" {{ $order->currency == 'PKR' ? 'selected' : '' }}>PKR</option>
                                <option value="USD" {{ $order->currency == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="AED" {{ $order->currency == 'AED' ? 'selected' : '' }}>AED</option>
                                <option value="EUR" {{ $order->currency == 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="GBP" {{ $order->currency == 'GBP' ? 'selected' : '' }}>GBP</option>
                                <option value="SAR" {{ $order->currency == 'SAR' ? 'selected' : '' }}>SAR</option>
                                <option value="INR" {{ $order->currency == 'INR' ? 'selected' : '' }}>INR</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="duration" class="form-label">Duration (days)</label>
                        <input type="number" name="duration" class="form-control" value="{{ $order->duration }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ $order->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ $order->status == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select" onchange="toggleOtherField()">
                            <option value="">-- Select Payment Method --</option>
                            @foreach ($predefinedMethods as $method)
                                <option value="{{ $method }}"
                                    {{ strtolower($order->payment_method) == strtolower($method) ? 'selected' : '' }}>
                                    {{ ucfirst($method) }}
                                </option>
                            @endforeach
                            <option value="other" {{ $isOther ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3" id="other-payment-method" style="{{ $isOther ? '' : 'display:none;' }}">
                    <label for="custom_payment_method" class="form-label">Enter Custom Payment Method</label>
                    <input type="text" name="custom_payment_method" id="custom_payment_method" class="form-control"
                        value="{{ $isOther ? $order->payment_method : '' }}" placeholder="Enter other payment method">
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-dark">Update Order</button>
                </div>
            </form>
        </div>
    </div>

@endsection
