@extends('admin.layout.app')
@section('page_title', 'Create Order')

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('orders.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Client</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Select Client</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">
                                    {{ $client->name }} ({{ $client->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="package" class="form-label">Package Name</label>
                        <input type="text" name="package" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="price" class="form-control" required>
                            <select name="currency" class="form-select" style="max-width: 120px;">
                                <option value="PKR">PKR</option>
                                <option value="USD">USD</option>
                                <option value="AED">AED</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                                <option value="SAR">SAR</option>
                                <option value="INR">INR</option>
                            </select>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <label for="duration" class="form-label">Duration (days)</label>
                        <input type="number" name="duration" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select" onchange="toggleOtherField()">
                            <option value="">-- Select Payment Method --</option>
                            <option value="easypaisa">Easypaisa</option>
                            <option value="jazzcash">Jazzcash</option>
                            <option value="nayapay">Nayapay</option>
                            <option value="meezan bank">Meezan Bank</option>
                            <option value="alfalah bank">Alfalah Bank</option>
                            <option value="sadapay">Sadapay</option>
                            <option value="skrill">Skrill</option>
                            <option value="binance">Binance</option>
                            <option value="bitget">Bitget</option>
                            <option value="remitly">Remitly</option>
                            <option value="mexc">MEXC</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3" id="other-payment-method" style="display: none;">
                    <label for="custom_payment_method" class="form-label">Enter Custom Payment Method</label>
                    <input type="text" name="custom_payment_method" id="custom_payment_method" class="form-control"
                        placeholder="Enter other payment method">
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="expiry_date" class="form-label">Subscription Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control"
                            value="{{ old('expiry_date', $order->expiry_date ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="screenshot" class="form-label">Upload Screenshot</label>
                        <input type="file" name="screenshot" class="form-control">
                    </div>
                </div>

                @if (isset($order) && $order->screenshot)
                    <div class="mb-3">
                        <p class="mb-1">Current Screenshot:</p>
                        <img src="{{ asset('storage/' . $order->screenshot) }}" width="150">
                    </div>
                @endif

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-dark">Save Order</button>
                </div>
            </form>
        </div>
    </div>

@endsection
