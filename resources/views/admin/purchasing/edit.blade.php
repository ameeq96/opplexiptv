@extends('admin.layouts.app')

@section('page_title', 'Edit Purchase')

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

    {{-- ===== UPDATE FORM (fields only) ===== --}}
    <form id="updatePurchaseForm" action="{{ route('admin.purchasing.update', $purchasing->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Panel Name <span class="text-danger">*</span></label>
          <input type="text" name="item_name" class="form-control"
                 value="{{ old('item_name', $purchasing->item_name) }}" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Credits</label>
          <input type="number" name="quantity" class="form-control"
                 value="{{ old('quantity', $purchasing->quantity) }}" required>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Cost Price</label>
          <div class="input-group">
            <input type="number" step="0.01" name="cost_price" class="form-control"
                   value="{{ old('cost_price', $purchasing->cost_price) }}" required>
            <select name="currency" class="form-select" style="max-width: 120px;">
              @foreach (['PKR','USD','EUR','GBP','CAD','AED','SAR','INR'] as $currency)
                <option value="{{ $currency }}" {{ old('currency', $purchasing->currency) == $currency ? 'selected' : '' }}>
                  {{ $currency }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Purchase Date</label>
          <input type="date" name="purchase_date" class="form-control"
                 value="{{ old('purchase_date', optional($purchasing->purchase_date)->format('Y-m-d')) }}">
        </div>
      </div>

      {{-- NOTE (optional) --}}
      <div class="row mb-3">
        <div class="col-md-12">
          <label class="form-label">Note <span class="text-muted">(optional)</span></label>
          <textarea name="note" class="form-control" rows="3" placeholder="Any notes...">{{ old('note', $purchasing->note) }}</textarea>
        </div>
      </div>

      {{-- MULTIPLE SCREENSHOTS (no preview) --}}
      <div class="row mb-3">
        <div class="col-md-12">
          <label class="form-label">Upload New Screenshots (multiple)</label>
          <input type="file" name="screenshots[]" class="form-control" multiple accept="image/*">
          <small class="text-muted">Allowed: JPG, JPEG, PNG â€” max 5MB each.</small>
          @error('screenshots') <div class="text-danger">{{ $message }}</div> @enderror
          @error('screenshots.*') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
      </div>

    </form>
    {{-- ===== /UPDATE FORM ===== --}}

    {{-- ===== CURRENT SCREENSHOTS (shown above buttons; separate delete forms) ===== --}}
    @if (method_exists($purchasing, 'pictures') && $purchasing->pictures->count())
      <div class="mb-3">
        <label class="form-label d-block">Current Screenshots</label>
        <div class="row g-2">
          @foreach ($purchasing->pictures as $pic)
            <div class="col-6 col-sm-4 col-md-3">
              <div class="border rounded p-2 h-100 d-flex flex-column">
                <a href="{{ asset($pic->path) }}" target="_blank" class="mb-2">
                  <img src="{{ asset($pic->path) }}" class="img-fluid rounded" alt="screenshot"
                       style="object-fit: cover; width: 100%; height: 140px;">
                </a>
                <form action="{{ route('admin.purchasing.pictures.destroy', [$purchasing->id, $pic->id]) }}" method="POST" class="mt-auto">
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

    {{-- BUTTON ROW (outside, submits the form by id) --}}
    <div class="d-flex justify-content-between">
      <a href="{{ route('admin.purchasing.index') }}" class="btn btn-outline-secondary">Back</a>
      <button type="submit" class="btn btn-dark" form="updatePurchaseForm">Update Purchase</button>
    </div>

  </div>
</div>

@endsection

