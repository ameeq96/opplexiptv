@extends('admin.layout.app')

@section('page_title', 'Edit Panel Order')

@section('content')

    @php
        $predefinedMethods = [
            'easypaisa', 'jazzcash', 'nayapay', 'meezan bank', 'alfalah bank',
            'sadapay', 'skrill', 'binance', 'bitget', 'remitly', 'mexc',
        ];

        $isOther = !in_array(strtolower($order->payment_method), $predefinedMethods);
    @endphp

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
            <form action="{{ route('panel-orders.update', $order->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Client <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select select2" required>
                            <option value="">-- Select Client --</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ $client->id == $order->user_id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="iptv_username" class="form-label">IPTV Username</label>
                        <input type="text" name="iptv_username" class="form-control"
                            value="{{ old('iptv_username', $order->iptv_username) }}" placeholder="e.g. opplex_1234">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="duration" class="form-label">Duration (days)</label>
                        <input type="number" name="duration" class="form-control" value="{{ $order->duration }}" required>
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

                <div class="row mb-3" id="other-payment-method" style="{{ $isOther ? '' : 'display:none;' }}">
                    <div class="col-md-12">
                        <label for="custom_payment_method" class="form-label">Enter Custom Payment Method</label>
                        <input type="text" name="custom_payment_method" id="custom_payment_method" class="form-control"
                            value="{{ $isOther ? $order->payment_method : '' }}" placeholder="Enter other payment method">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Cost Price</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="price" value="{{ old('price', $order->price) }}"
                                class="form-control" required>
                            <select name="currency" class="form-select" style="max-width: 120px;">
                                @foreach (['PKR','CAD','USD','AED','EUR','GBP','SAR','INR'] as $cur)
                                    <option value="{{ $cur }}" {{ old('currency', $order->currency) == $cur ? 'selected' : '' }}>{{ $cur }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Sell Price</label>
                        <input type="number" step="0.01" name="sell_price"
                            value="{{ old('sell_price', $order->sell_price) }}" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Credits</label>
                        <input type="number" name="credits" value="{{ old('credits', $order->credits) }}" class="form-control"
                            placeholder="Enter credits if any">
                    </div>

                    <div class="col-md-6">
                        <label for="package" class="form-label">Package Name</label>
                        <select name="package" id="package" class="form-select" required>
                            <option value="" disabled>-- Select Package --</option>
                            <option value="starshare" {{ $order->package == 'starshare' ? 'selected' : '' }}>Starshare</option>
                            <option value="opplex" {{ $order->package == 'opplex' ? 'selected' : '' }}>Opplex</option>
                            <option value="other" {{ $order->package == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3" id="custom_package_field" style="{{ $order->package == 'other' ? '' : 'display:none;' }}">
                    <div class="col-md-12">
                        <label for="custom_package" class="form-label">Custom Package Name</label>
                        <input type="text" name="custom_package" id="custom_package"
                            value="{{ old('custom_package', $order->custom_package) }}" class="form-control"
                            placeholder="Enter your package name">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ $order->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ $order->status == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Buying Date</label>
                        <input type="date" name="buying_date" value="{{ old('buying_date', $order->buying_date) }}"
                            class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date" value="{{ old('expiry_date', $order->expiry_date) }}"
                            class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Upload New Screenshot</label>
                        <input type="file" name="screenshot" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Current Screenshot</label><br>
                        @if ($order->screenshot)
                            <img src="{{ asset($order->screenshot) }}" width="150" class="rounded shadow-sm mb-2">
                        @else
                            <p>No screenshot uploaded.</p>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('panel-orders.index') }}" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-dark">Update Order</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('package').addEventListener('change', function() {
            const customField = document.getElementById('custom_package_field');
            if (this.value === 'other') {
                customField.style.display = 'block';
            } else {
                customField.style.display = 'none';
            }
        });
    </script>

@endsection
