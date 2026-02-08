@extends('admin.layouts.app')

@section('page_title', 'Add New Purchase')

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
    <form action="{{ route('admin.purchasing.store') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Panel Name <span class="text-danger">*</span></label>
          <input type="text" name="item_name" class="form-control" value="{{ old('item_name') }}" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Credits</label>
          <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" required>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Cost Price</label>
          <div class="input-group">
            <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price') }}" required>
            <select name="currency" class="form-select" style="max-width: 120px;">
              @foreach (['PKR','USD','EUR','GBP','CAD','AED','SAR','INR'] as $ccy)
                <option value="{{ $ccy }}" @selected(old('currency','PKR')==$ccy)>{{ $ccy }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Purchase Date</label>
          <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date') }}">
        </div>
      </div>

      {{-- NOTE (optional) --}}
      <div class="row mb-3">
        <div class="col-md-12">
          <label class="form-label">Note <span class="text-muted">(optional)</span></label>
          <textarea name="note" class="form-control" rows="3" placeholder="Any notes...">{{ old('note') }}</textarea>
        </div>
      </div>

      {{-- MULTIPLE SCREENSHOTS (no preview) --}}
      <div class="row mb-3">
        <div class="col-md-12">
          <label class="form-label">Upload Screenshots (multiple)</label>
          <input type="file" name="screenshots[]" class="form-control" multiple accept="image/*">
          <small class="text-muted">Allowed: JPG, JPEG, PNG â€” max 5MB each.</small>
          @error('screenshots') <div class="text-danger">{{ $message }}</div> @enderror
          @error('screenshots.*') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="d-flex justify-content-between">
        <a href="{{ route('admin.purchasing.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-dark">Save Purchase</button>
      </div>
    </form>
  </div>
</div>

@endsection

