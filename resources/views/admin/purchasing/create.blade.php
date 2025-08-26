@extends('admin.layout.app')

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
            <form action="{{ route('purchasing.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Panel Name <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Credits</label>
                        <input type="number" name="quantity" class="form-control" value="1" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Cost Price</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="cost_price" class="form-control" required>
                            <select name="currency" class="form-select" style="max-width: 120px;">
                                <option value="PKR">PKR</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                                <option value="CAD">CAD</option>
                                <option value="AED">AED</option>
                                <option value="SAR">SAR</option>
                                <option value="INR">INR</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Screenshot</label>
                        <input type="file" name="screenshot" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('purchasing.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-dark">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>

@endsection
