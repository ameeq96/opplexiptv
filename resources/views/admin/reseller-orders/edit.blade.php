@extends('admin.layout.app')

@section('page_title', 'Edit Panel Order')

@section('content')

@php
    $predefinedMethods = [
        'easypaisa','jazzcash','nayapay','meezan bank','alfalah bank',
        'sadapay','skrill','binance','bitget','remitly','mexc',
    ];
    $isOther = !in_array(strtolower($order->payment_method ?? ''), $predefinedMethods);
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

    {{-- ===== UPDATE FORM (FIELDS ONLY) ===== --}}
    <form id="updateForm" action="{{ route('admin.panel-orders.update', $order->id) }}" method="POST" enctype="multipart/form-data">
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
          <select name="payment_method" id="payment_method" class="form-select">
            <option value="">-- Select Payment Method --</option>
            @foreach ($predefinedMethods as $method)
              <option value="{{ $method }}" {{ strtolower($order->payment_method)==strtolower($method) ? 'selected' : '' }}>
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
            <select name="currency" class="form-select" style="max-width:120px;">
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
            <option value="opplex"    {{ $order->package == 'opplex'    ? 'selected' : '' }}>Opplex</option>
            <option value="other"     {{ $order->package == 'other'     ? 'selected' : '' }}>Other</option>
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
            <option value="active"  {{ $order->status == 'active'  ? 'selected' : '' }}>Active</option>
            <option value="expired" {{ $order->status == 'expired' ? 'selected' : '' }}>Expired</option>
          </select>
        </div>

        <div class="col-md-6">
          <label>Buying Date</label>
          <input type="date" name="buying_date" value="{{ old('buying_date', optional($order->buying_date)->format('Y-m-d')) }}"
                 class="form-control" required>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label>Expiry Date</label>
          <input type="date" name="expiry_date" value="{{ old('expiry_date', optional($order->expiry_date)->format('Y-m-d')) }}"
                 class="form-control">
        </div>

        <div class="col-md-6">
          {{-- MULTIPLE uploads, no preview --}}
          <label>Upload New Screenshots (multiple)</label>
          <input type="file" name="screenshots[]" class="form-control" multiple accept="image/*">
          <small class="text-muted d-block">Allowed: images up to 5MB each.</small>
          @error('screenshots') <div class="text-danger">{{ $message }}</div> @enderror
          @error('screenshots.*') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- NOTE (optional) --}}
      <div class="row mb-3">
        <div class="col-md-12">
          <label for="note" class="form-label">Note (optional)</label>
          <textarea name="note" id="note" class="form-control" rows="3" placeholder="Write any notes...">{{ old('note', $order->note) }}</textarea>
          @error('note') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
      </div>

    </form>
    {{-- ===== /UPDATE FORM ===== --}}

    {{-- ===== CURRENT SCREENSHOTS (MOVED JUST ABOVE BUTTONS) ===== --}}
    @if (method_exists($order,'pictures') && $order->pictures->count())
      <div class="mb-3">
        <label class="form-label d-block">Current Screenshots</label>
        <div class="row g-2">
          @foreach ($order->pictures as $pic)
            <div class="col-6 col-sm-4 col-md-3">
              <div class="border rounded p-2 h-100 d-flex flex-column">
                <a href="{{ asset($pic->path) }}" target="_blank" class="mb-2">
                  <img src="{{ asset($pic->path) }}" class="img-fluid rounded" alt="screenshot"
                       style="object-fit: cover; width: 100%; height: 140px;">
                </a>
                <form action="{{ route('admin.orders.pictures.destroy', [$order->id, $pic->id]) }}" method="POST" class="mt-auto">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger w-100">Delete</button>
                </form>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
    {{-- ===== /CURRENT SCREENSHOTS ===== --}}

    {{-- BUTTON ROW --}}
    <div class="d-flex justify-content-between">
      <a href="{{ route('admin.panel-orders.index') }}" class="btn btn-outline-secondary">Back</a>
      <button type="submit" class="btn btn-dark" form="updateForm">Update Order</button>
    </div>

  </div>
</div>

{{-- Small togglers --}}
<script>
  // Payment "other"
  (function () {
    const sel = document.getElementById('payment_method');
    const wrap = document.getElementById('other-payment-method');
    const input = document.getElementById('custom_payment_method');
    function toggle() {
      const show = sel && sel.value === 'other';
      wrap.style.display = show ? 'block' : 'none';
      if (!show && input) input.value = '';
    }
    if (sel) { toggle(); sel.addEventListener('change', toggle); }
  })();

  // Package "other"
  (function () {
    const sel = document.getElementById('package');
    const wrap = document.getElementById('custom_package_field');
    function toggle() {
      wrap.style.display = (sel && sel.value === 'other') ? 'block' : 'none';
    }
    if (sel) { toggle(); sel.addEventListener('change', toggle); }
  })();
</script>

@endsection
