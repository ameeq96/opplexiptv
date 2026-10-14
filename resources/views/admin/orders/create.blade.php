@extends('admin.layouts.app')
@section('title', isset($order) ? 'Edit Order' : 'Create Order')
@section('page_title', isset($order) ? 'Edit Order' : 'Create Order')

@section('breadcrumbs')
  <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Package Orders</a></li>
  <li class="breadcrumb-item active" aria-current="page">Create Order</li>
@endsection

@section('content')

<div class="admin-form-intro">
  <h3>Create a package order</h3>
  <p>Add the client's package, payment details and subscription dates. Fields marked <span class="admin-required" aria-hidden="true">*</span> are required.</p>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <form action="{{ route('admin.orders.store') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <fieldset class="admin-form-section">
        <legend>Client &amp; package</legend>
        <p class="text-muted small">Choose who this order belongs to and the package they purchased.</p>
      <div class="row mb-2">
        <div class="col-md-6">
          <label for="user_id" class="form-label">Client <span class="admin-required" aria-hidden="true">*</span></label>
          <select name="user_id" id="user_id" class="form-select select2" required>
            <option value="">Select Client</option>
            @foreach ($clients as $client)
              <option value="{{ $client->id }}" @selected((string) old('user_id') === (string) $client->id)>{{ $client->name }} ({{ $client->phone }})</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label for="package" class="form-label">Package Name <span class="admin-required" aria-hidden="true">*</span></label>
          <select name="package" id="package" class="form-select" required>
            <option value="">-- Select Package --</option>
            <optgroup label="Opplex IPTV">
              <option value="1 Month Opplex IPTV Account" @selected(old('package') === '1 Month Opplex IPTV Account')>1 Month</option>
              <option value="3 Months Opplex IPTV Account" @selected(old('package') === '3 Months Opplex IPTV Account')>3 Months</option>
              <option value="6 Months Opplex IPTV Account" @selected(old('package') === '6 Months Opplex IPTV Account')>6 Months</option>
              <option value="12 Months Opplex IPTV Account" @selected(old('package') === '12 Months Opplex IPTV Account')>12 Months</option>
            </optgroup>
            <optgroup label="Filex">
              <option value="1 Month Starshare Account" @selected(old('package') === '1 Month Starshare Account')>1 Month</option>
              <option value="3 Months Starshare Account" @selected(old('package') === '3 Months Starshare Account')>3 Months</option>
              <option value="6 Months Starshare Account" @selected(old('package') === '6 Months Starshare Account')>6 Months</option>
              <option value="12 Months Starshare Account" @selected(old('package') === '12 Months Starshare Account')>12 Months</option>
            </optgroup>
            <option value="other" @selected(old('package') === 'other')>Other</option>
          </select>
        </div>
      </div>

      <div class="col-md-12" id="custom_package_field" style="display:none;">
        <label for="custom_package" class="form-label">Custom Package Name</label>
        <input type="text" name="custom_package" id="custom_package" class="form-control" placeholder="Enter your package name" value="{{ old('custom_package') }}">
      </div>

      <div class="row mb-2 mt-1">
        <div class="col-md-12">
          <label for="iptv_username" class="form-label">IPTV Username <span class="text-muted fw-normal">(optional)</span></label>
          <input type="text" name="iptv_username" id="iptv_username" class="form-control" placeholder="e.g. opplex_1234" value="{{ old('iptv_username') }}">
        </div>
      </div>
      </fieldset>

      <fieldset class="admin-form-section">
        <legend>Payment &amp; subscription</legend>
        <p class="text-muted small">Record the amount, currency and dates for this order.</p>
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="price" class="form-label">Price <span class="admin-required" aria-hidden="true">*</span></label>
          <div class="input-group">
            <input type="number" step="0.01" name="price" id="price" class="form-control" value="{{ old('price') }}" required>
            <label for="currency" class="visually-hidden">Currency</label>
            <select name="currency" id="currency" class="form-select" style="max-width: 120px;">
              @foreach (['PKR','CAD','USD','AED','EUR','GBP','SAR','INR'] as $ccy)
                <option value="{{ $ccy }}" @selected(old('currency', 'PKR') === $ccy)>{{ $ccy }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-md-6">
          <label for="duration" class="form-label">Duration (days) <span class="text-muted fw-normal">(optional)</span></label>
          <input type="number" name="duration" id="duration" class="form-control" value="{{ old('duration') }}">
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="status" class="form-label">Status</label>
          <select name="status" id="status" class="form-select">
            <option value="pending" @selected(old('status', 'pending') === 'pending')>Pending</option>
            <option value="active" @selected(old('status', 'pending') === 'active')>Active</option>
            <option value="expired" @selected(old('status', 'pending') === 'expired')>Expired</option>
          </select>
        </div>

        <div class="col-md-6">
          <label for="payment_method" class="form-label">Payment Method <span class="text-muted fw-normal">(optional)</span></label>
          <select name="payment_method" id="payment_method" class="form-select">
            <option value="">-- Select Payment Method --</option>
            @foreach (['easypaisa','jazzcash','nayapay','meezan bank','alfalah bank','sadapay','skrill','binance','bitget','remitly','mexc','other'] as $pm)
              <option value="{{ $pm }}" @selected(old('payment_method') === $pm)>{{ ucfirst($pm) }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="mb-3" id="other-payment-method" style="display: none;">
        <label for="custom_payment_method" class="form-label">Enter Custom Payment Method</label>
        <input type="text" name="custom_payment_method" id="custom_payment_method" class="form-control" placeholder="Enter other payment method" value="{{ old('custom_payment_method') }}">
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="buying_date" class="form-label">Buying Date <span class="admin-required" aria-hidden="true">*</span></label>
          <input type="date" name="buying_date" id="buying_date" class="form-control" value="{{ old('buying_date') }}" required>
        </div>

        <div class="col-md-6">
          <label for="expiry_date" class="form-label">Subscription Expiry Date <span class="text-muted fw-normal">(optional)</span></label>
          <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
        </div>
      </div>
      </fieldset>

      <fieldset class="admin-form-section">
        <legend>Notes &amp; attachments</legend>
        <p class="text-muted small">Add optional context and supporting screenshots.</p>
      {{-- NOTE (optional) --}}
      <div class="mb-3">
        <label for="note" class="form-label">Note (optional)</label>
        <textarea name="note" id="note" class="form-control" rows="3" placeholder="Any notes about this order...">{{ old('note') }}</textarea>
      </div>

      {{-- ONLY MULTIPLE UPLOAD, NO SHOW/NO PREVIEW --}}
      <div class="mb-3">
        <label for="screenshots" class="form-label">Upload Screenshots (multiple)</label>
        <input type="file" id="screenshots" name="screenshots[]" class="form-control" multiple accept="image/*">
        <small class="text-muted">Optional. Allowed: images up to 5MB each. Files must be selected again after a validation error.</small>
        @error('screenshots') <div class="text-danger">{{ $message }}</div> @enderror
        @error('screenshots.*') <div class="text-danger">{{ $message }}</div> @enderror
      </div>
      </fieldset>

      <div class="admin-form-actions d-flex justify-content-between mt-4">
        <p class="text-muted small mb-0">Changes are not saved until you select Save Order.</p>
        <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-dark">Save Order</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Package: toggle custom field
  const packageSelect = document.getElementById('package');
  const customPackageField = document.getElementById('custom_package_field');
  const customPackageInput = document.getElementById('custom_package');
  function toggleCustomPackage() {
    const show = packageSelect && packageSelect.value === 'other';
    customPackageField.style.display = show ? 'block' : 'none';
    if (!show && customPackageInput) customPackageInput.value = '';
  }
  if (packageSelect) {
    toggleCustomPackage();
    packageSelect.addEventListener('change', toggleCustomPackage);
  }

  // Payment method: toggle custom field
  const paymentSelect = document.getElementById('payment_method');
  const otherPaymentField = document.getElementById('other-payment-method');
  const customPaymentInput = document.getElementById('custom_payment_method');
  function toggleCustomPayment() {
    const show = paymentSelect && paymentSelect.value === 'other';
    otherPaymentField.style.display = show ? 'block' : 'none';
    if (!show && customPaymentInput) customPaymentInput.value = '';
  }
  if (paymentSelect) {
    toggleCustomPayment();
    paymentSelect.addEventListener('change', toggleCustomPayment);
  }
</script>
@endpush

