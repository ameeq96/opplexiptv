@extends('admin.layout.app')

@section('page_title', 'Add Panel Order')

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
            <form action="{{ route('panel-orders.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Client <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select select2" required>
                            <option value="">-- Select Client --</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ old('user_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="iptv_username" class="form-label">IPTV Username</label>
                        <input type="text" name="iptv_username" class="form-control"
                               value="{{ old('iptv_username') }}" placeholder="e.g. opplex_1234">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="duration" class="form-label">Duration (days)</label>
                        <input type="number" name="duration" class="form-control"
                               value="{{ old('duration') }}" required>
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
                    <input type="text" name="custom_payment_method" id="custom_payment_method"
                           value="{{ old('custom_payment_method') }}" class="form-control"
                           placeholder="Enter other payment method">
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="price" class="form-control"
                                   value="{{ old('price') }}" required>
                            <select name="currency" class="form-select" style="max-width: 120px;">
                                <option value="PKR" {{ old('currency') == 'PKR' ? 'selected' : '' }}>PKR</option>
                                <option value="CAD" {{ old('currency') == 'CAD' ? 'selected' : '' }}>CAD</option>
                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="AED" {{ old('currency') == 'AED' ? 'selected' : '' }}>AED</option>
                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP</option>
                                <option value="SAR" {{ old('currency') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                <option value="INR" {{ old('currency') == 'INR' ? 'selected' : '' }}>INR</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Sell Price</label>
                        <input type="number" step="0.01" name="sell_price" class="form-control"
                               value="{{ old('sell_price') }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Credits</label>
                        <input type="number" name="credits" class="form-control"
                               value="{{ old('credits') }}" placeholder="Enter credits if any">
                    </div>

                    <div class="col-md-6">
                        <label for="package" class="form-label">Package Name</label>
                        <select name="package" id="package" class="form-select" required>
                            <option value="" disabled selected>-- Select Package --</option>
                            <option value="starshare" {{ old('package') == 'starshare' ? 'selected' : '' }}>Starshare</option>
                            <option value="opplex" {{ old('package') == 'opplex' ? 'selected' : '' }}>Opplex</option>
                            <option value="other" {{ old('package') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3" id="custom_package_field" style="{{ old('package') == 'other' ? '' : 'display:none;' }}">
                    <div class="col-md-12">
                        <label for="custom_package" class="form-label">Custom Package Name</label>
                        <input type="text" name="custom_package" id="custom_package"
                               value="{{ old('custom_package') }}" class="form-control"
                               placeholder="Enter your package name">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Buying Date</label>
                        <input type="date" name="buying_date" value="{{ old('buying_date') }}" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Upload Screenshot</label>
                        <input type="file" name="screenshot" class="form-control">
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('panel-orders.index') }}" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-dark">Create Order</button>
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
