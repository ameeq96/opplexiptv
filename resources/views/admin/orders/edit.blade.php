@extends('admin.layout.app')
@section('page_title', 'Edit Order')

@section('content')
<h4>Edit Order</h4>

<form action="{{ route('orders.update', $order) }}" method="POST">
    @csrf @method('PUT')

    <div class="mb-3">
        <label>Client</label>
        <select name="user_id" class="form-select" required>
            @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ $order->user_id == $client->id ? 'selected' : '' }}>
                    {{ $client->name }} ({{ $client->phone }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Package Name</label>
        <input type="text" name="package" class="form-control" value="{{ $order->package }}" required>
    </div>

    <div class="mb-3">
        <label>Price</label>
        <input type="number" name="price" class="form-control" value="{{ $order->price }}" required>
    </div>

    <div class="mb-3">
        <label>Duration (days)</label>
        <input type="number" name="duration" class="form-control" value="{{ $order->duration }}" required>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-select">
            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="active" {{ $order->status == 'active' ? 'selected' : '' }}>Active</option>
            <option value="expired" {{ $order->status == 'expired' ? 'selected' : '' }}>Expired</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Payment Method</label>
        <input type="text" name="payment_method" class="form-control" value="{{ $order->payment_method }}">
    </div>

    <button type="submit" class="btn btn-dark">Update</button>
    <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
