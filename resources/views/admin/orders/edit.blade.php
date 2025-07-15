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
                        <select name="user_id" class="form-select select2" required>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ $order->user_id == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }} ({{ $client->phone }})
                                </option>
                            @endforeach
                        </select>

                    </div>

                    <div class="col-md-6">
                        <label for="package" class="form-label">Package Name</label>
                        <select name="package" class="form-select" required>
                            <option value="">-- Select Package --</option>
                            <optgroup label="Opplex IPTV">
                                <option value="1 Month Opplex IPTV Account"
                                    {{ $order->package == '1 Month Opplex IPTV Account' ? 'selected' : '' }}>1 Month
                                </option>
                                <option value="3 Months Opplex IPTV Account"
                                    {{ $order->package == '3 Months Opplex IPTV Account' ? 'selected' : '' }}>3 Months
                                </option>
                                <option value="6 Months Opplex IPTV Account"
                                    {{ $order->package == '6 Months Opplex IPTV Account' ? 'selected' : '' }}>6 Months
                                </option>
                                <option value="12 Months Opplex IPTV Account"
                                    {{ $order->package == '12 Months Opplex IPTV Account' ? 'selected' : '' }}>12 Months
                                </option>
                            </optgroup>
                            <optgroup label="Starshare">
                                <option value="1 Month Starshare Account"
                                    {{ $order->package == '1 Month Starshare Account' ? 'selected' : '' }}>1 Month</option>
                                <option value="3 Months Starshare Account"
                                    {{ $order->package == '3 Months Starshare Account' ? 'selected' : '' }}>3 Months
                                </option>
                                <option value="6 Months Starshare Account"
                                    {{ $order->package == '6 Months Starshare Account' ? 'selected' : '' }}>6 Months
                                </option>
                                <option value="12 Months Starshare Account"
                                    {{ $order->package == '12 Months Starshare Account' ? 'selected' : '' }}>12 Months
                                </option>
                            </optgroup>
                        </select>
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="iptv_username" class="form-label">IPTV Username</label>
                        <input type="text" name="iptv_username" class="form-control"
                            value="{{ old('iptv_username', $order->iptv_username) }}" placeholder="e.g. opplex_1234">
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
                                <option value="CAD" {{ $order->currency == 'CAD' ? 'selected' : '' }}>CAD</option>
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

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="buying_date" class="form-label">Buying Date</label>
                        <input type="date" name="buying_date" class="form-control"
                            value="{{ old('buying_date', $order->buying_date) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="expiry_date" class="form-label">Subscription Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control"
                            value="{{ old('expiry_date', $order->expiry_date) }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="screenshot" class="form-label">Upload New Screenshot</label>
                        <input type="file" name="screenshot" class="form-control" accept="image/*">
                        <small class="text-muted">Accepted formats: jpg, jpeg, png | Max size: 2MB</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Current Screenshot</label><br>
                        @if ($order->screenshot)
                            <img src="{{ asset($order->screenshot) }}" alt="Current Screenshot" width="100"
                                height="100"
                                style="object-fit: cover; border: 1px solid #ccc; border-radius: 6px; cursor: pointer;"
                                data-bs-toggle="modal" data-bs-target="#screenshotModal"
                                onclick="showScreenshot('{{ asset($order->screenshot) }}')">
                        @else
                            <span class="text-muted">No screenshot uploaded.</span>
                        @endif
                    </div>
                </div>


                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-dark">Update Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Screenshot Lightbox Modal -->
    <div class="modal fade" id="screenshotModal" tabindex="-1" aria-labelledby="screenshotModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark position-relative border-0">

                <!-- Close button -->
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2"
                    data-bs-dismiss="modal" aria-label="Close"></button>

                <!-- Image -->
                <div class="modal-body p-0 text-center">
                    <img id="modalScreenshot" src="" class="img-fluid" style="width: 100%; max-height: 90vh;"
                        alt="Screenshot">
                </div>
            </div>
        </div>
    </div>



@endsection
