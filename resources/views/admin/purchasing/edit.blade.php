@extends('admin.layout.app')

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

            <form action="{{ route('purchasing.update', $purchasing->id) }}" method="POST" enctype="multipart/form-data">
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
                                @foreach (['PKR', 'USD', 'EUR', 'GBP', 'CAD', 'AED', 'SAR', 'INR'] as $currency)
                                    <option value="{{ $currency }}"
                                        {{ $purchasing->currency == $currency ? 'selected' : '' }}>
                                        {{ $currency }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control"
                               value="{{ old('purchase_date', $purchasing->purchase_date) }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Screenshot</label>
                        <input type="file" name="screenshot" class="form-control" accept="image/*">

                        @if ($purchasing->screenshot)
                            <div class="mt-2">
                                <img src="{{ asset($purchasing->screenshot) }}" width="120" alt="Current Screenshot"
                                     class="img-thumbnail">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('purchasing.index') }}" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-dark">Update Purchase</button>
                </div>
            </form>
        </div>
    </div>

@endsection
