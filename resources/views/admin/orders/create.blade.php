@extends('admin.layout.app')
@section('page_title', 'Create Order')

@section('content')
<h4>Create Order</h4>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
    </ul>
</div>
@endif

<form action="{{ route('orders.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label>Client</label>
        <select name="user_id" class="form-select" required>
            <option value="">Select Client</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->phone }})</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Package Name</label>
        <input type="text" name="package" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Price</label>
        <input type="number" step="0.01" name="price" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Duration (days)</label>
        <input type="number" name="duration" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-select">
            <option value="pending">Pending</option>
            <option value="active">Active</option>
            <option value="expired">Expired</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Payment Method</label>
        <input type="text" name="payment_method" class="form-control">
    </div>

    <button type="submit" class="btn btn-dark">Save</button>
    <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
