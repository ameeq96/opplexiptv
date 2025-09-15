@extends('admin.layout.app')
@section('page_title', 'Edit Order')

@php
    $predefinedMethods = [
        'easypaisa','jazzcash','nayapay','meezan bank','alfalah bank',
        'sadapay','skrill','binance','bitget','remitly','mexc',
    ];
    $isOther = !in_array(strtolower($order->payment_method ?? ''), $predefinedMethods);
    $predefs = [
        '1 Month Opplex IPTV Account','3 Months Opplex IPTV Account','6 Months Opplex IPTV Account','12 Months Opplex IPTV Account',
        '1 Month Starshare Account','3 Months Starshare Account','6 Months Starshare Account','12 Months Starshare Account',
    ];
    $customPkgVal = ($order->package != 'other' && !in_array($order->package, $predefs)) ? $order->package : '';
@endphp

@section('content')
<div class="card shadow-sm">
  <div class="card-body">

    {{-- ===================== UPDATE FORM (fields only) ===================== --}}
    <form id="updateForm" action="{{ route('admin.orders.update', $order) }}" method="POST" enctype="multipart/form-data">
      @csrf @method('PUT')

      {{-- Client + Package --}}
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="user_id" class="form-label">Client</label>
          <select name="user_id" id="user_id" class="form-select select2" required>
            @foreach ($clients as $client)
              <option value="{{ $client->id }}" {{ $order->user_id == $client->id ? 'selected' : '' }}>
                {{ $client->name }} ({{ $client->phone }})
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label for="package" class="form-label">Package Name</label>
          <select name="package" id="package" class="form-select" required>
            <option value="">-- Select Package --</option>
            <optgroup label="Opplex IPTV">
              <option value="1 Month Opplex IPTV Account" @selected($order->package=='1 Month Opplex IPTV Account')>1 Month</option>
              <option value="3 Months Opplex IPTV Account" @selected($order->package=='3 Months Opplex IPTV Account')>3 Months</option>
              <option value="6 Months Opplex IPTV Account" @selected($order->package=='6 Months Opplex IPTV Account')>6 Months</option>
              <option value="12 Months Opplex IPTV Account" @selected($order->package=='12 Months Opplex IPTV Account')>12 Months</option>
            </optgroup>
            <optgroup label="Starshare">
              <option value="1 Month Starshare Account" @selected($order->package=='1 Month Starshare Account')>1 Month</option>
              <option value="3 Months Starshare Account" @selected($order->package=='3 Months Starshare Account')>3 Months</option>
              <option value="6 Months Starshare Account" @selected($order->package=='6 Months Starshare Account')>6 Months</option>
              <option value="12 Months Starshare Account" @selected($order->package=='12 Months Starshare Account')>12 Months</option>
            </optgroup>
            <option value="other" @selected($order->package=='other')>Other</option>
          </select>
        </div>

        {{-- custom package --}}
        <div class="col-md-12 mt-2" id="custom_package_field" style="display:none;">
          <label for="custom_package" class="form-label">Custom Package Name</label>
          <input type="text" name="custom_package" id="custom_package" class="form-control"
                 value="{{ $customPkgVal }}" placeholder="Enter your package name">
        </div>
      </div>

      {{-- IPTV Username --}}
      <div class="row mb-3">
        <div class="col-md-12">
          <label for="iptv_username" class="form-label">IPTV Username</label>
          <input type="text" name="iptv_username" id="iptv_username" class="form-control"
                 value="{{ old('iptv_username', $order->iptv_username) }}" placeholder="e.g. opplex_1234">
        </div>
      </div>

      {{-- Price / Currency / Duration --}}
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Price</label>
          <div class="input-group">
            <input type="number" step="0.01" name="price" class="form-control"
                   value="{{ $order->price }}" required>
            <select name="currency" class="form-select" style="max-width:120px;">
              @foreach (['PKR','CAD','USD','AED','EUR','GBP','SAR','INR'] as $ccy)
                <option value="{{ $ccy }}" @selected($order->currency==$ccy)>{{ $ccy }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <label for="duration" class="form-label">Duration (days)</label>
          <input type="number" name="duration" id="duration" class="form-control" value="{{ $order->duration }}" required>
        </div>
      </div>

      {{-- Status / Payment --}}
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="status" class="form-label">Status</label>
          <select name="status" id="status" class="form-select">
            <option value="pending" @selected($order->status=='pending')>Pending</option>
            <option value="active" @selected($order->status=='active')>Active</option>
            <option value="expired" @selected($order->status=='expired')>Expired</option>
          </select>
        </div>

        <div class="col-md-6">
          <label for="payment_method" class="form-label">Payment Method</label>
          <select name="payment_method" id="payment_method" class="form-select">
            <option value="">-- Select Payment Method --</option>
            @foreach ($predefinedMethods as $method)
              <option value="{{ $method }}" @selected(strtolower($order->payment_method)==strtolower($method))>
                {{ ucfirst($method) }}
              </option>
            @endforeach
            <option value="other" @selected($isOther)>Other</option>
          </select>
        </div>
      </div>

      <div class="mb-3" id="other-payment-method" style="{{ $isOther ? '' : 'display:none;' }}">
        <label for="custom_payment_method" class="form-label">Enter Custom Payment Method</label>
        <input type="text" name="custom_payment_method" id="custom_payment_method" class="form-control"
               value="{{ $isOther ? $order->payment_method : '' }}" placeholder="Enter other payment method">
      </div>

      {{-- Dates --}}
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="buying_date" class="form-label">Buying Date</label>
          <input type="date" name="buying_date" id="buying_date" class="form-control"
                 value="{{ old('buying_date', optional($order->buying_date)->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-6">
          <label for="expiry_date" class="form-label">Subscription Expiry Date</label>
          <input type="date" name="expiry_date" id="expiry_date" class="form-control"
                 value="{{ old('expiry_date', optional($order->expiry_date)->format('Y-m-d')) }}">
        </div>
      </div>

      {{-- NOTE (optional) --}}
      <div class="mb-3">
        <label for="note" class="form-label">Note (optional)</label>
        <textarea name="note" id="note" class="form-control" rows="3" placeholder="Any notes about this order...">{{ old('note', $order->note) }}</textarea>
      </div>

      {{-- Multiple screenshots upload (new ones) --}}
      <div class="mb-3">
        <label for="screenshots" class="form-label">Upload New Screenshots (multiple)</label>
        <input type="file" id="screenshots" name="screenshots[]" class="form-control" multiple accept="image/*">
        <small class="text-muted">Allowed: images up to 5MB each.</small>
        @error('screenshots') <div class="text-danger">{{ $message }}</div> @enderror
        @error('screenshots.*') <div class="text-danger">{{ $message }}</div> @enderror
      </div>
    </form>
    {{-- ===================== /UPDATE FORM CLOSED HERE ===================== --}}

    {{-- ===================== PICTURES (OUTSIDE UPDATE FORM) ===================== --}}
    @if (method_exists($order,'pictures') && $order->pictures->count())
      <div class="mb-3">
        <p class="mb-1">Current Screenshots:</p>
        <div class="row">
          @foreach ($order->pictures as $pic)
            <div class="col-md-3 mb-3">
              <div class="border rounded p-2 h-100 d-flex flex-column justify-content-between">
                <a href="{{ asset($pic->path) }}" target="_blank" class="mb-2">
                  <img src="{{ asset($pic->path) }}" class="img-fluid rounded" alt="screenshot">
                </a>
                {{-- Each delete has its own small form (NOT nested inside update form) --}}
                <form action="{{ route('admin.orders.pictures.destroy', [$order->id, $pic->id]) }}" method="POST">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger w-100">Delete</button>
                </form>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
    {{-- ===================== /PICTURES ===================== --}}

    {{-- ===================== BUTTONS (OUTSIDE FORM BUT SUBMIT updateForm) ===================== --}}
    <div class="d-flex justify-content-between mt-4">
      <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
      <button type="submit" class="btn btn-dark" form="updateForm">Update Order</button>
    </div>
    {{-- ===================== /BUTTONS ===================== --}}

  </div>
</div>
@endsection

@push('scripts')
<script>
  // Toggle custom package
  const packageSelect = document.getElementById('package');
  const customPackageField = document.getElementById('custom_package_field');
  const customPackageInput = document.getElementById('custom_package');
  function toggleCustomPackage() {
    const show = packageSelect && packageSelect.value === 'other';
    customPackageField.style.display = show ? 'block' : 'none';
    if (!show && customPackageInput) customPackageInput.value = '';
  }
  if (packageSelect) { toggleCustomPackage(); packageSelect.addEventListener('change', toggleCustomPackage); }

  // Toggle custom payment method
  const paymentSelect = document.getElementById('payment_method');
  const otherPaymentField = document.getElementById('other-payment-method');
  const customPaymentInput = document.getElementById('custom_payment_method');
  function toggleOtherPayment() {
    const show = paymentSelect && paymentSelect.value === 'other';
    otherPaymentField.style.display = show ? 'block' : 'none';
    if (!show && customPaymentInput) customPaymentInput.value = '';
  }
  if (paymentSelect) { toggleOtherPayment(); paymentSelect.addEventListener('change', toggleOtherPayment); }

  // (Optional) Preview code is omitted intentionally since preview UI isn't present.
</script>
@endpush
