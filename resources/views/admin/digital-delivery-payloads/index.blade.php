@extends('admin.layouts.app')
@section('title', 'Delivery Inventory')
@section('content')
<div class="admin-card mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-9">
            <select class="form-select" name="product_id">
                <option value="">All Products</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" @selected($productId === (int) $p->id)>{{ $p->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-grid"><button class="btn btn-primary">Filter</button></div>
    </form>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted">{{ $payloads->total() }} payloads</div>
    <a class="btn btn-primary" href="{{ route('admin.digital-delivery-payloads.create') }}">+ New Payload</a>
</div>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Preview</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payloads as $row)
                    <tr>
                        <td>#{{ $row->id }}</td>
                        <td>{{ $row->product?->title ?? '-' }}</td>
                        <td>{{ ucfirst($row->payload_type) }}</td>
                        <td>{{ $row->maskedPreview() }}</td>
                        <td><span class="badge-soft {{ $row->is_assigned ? 'gray' : 'success' }}">{{ $row->is_assigned ? 'Assigned' : 'Available' }}</span></td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.digital-delivery-payloads.destroy', $row) }}" class="d-inline" onsubmit="return confirm('Delete this payload?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit" @disabled($row->is_assigned)>Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center p-4 text-muted">No payload found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $payloads->links('pagination::bootstrap-5') }}</div>
@endsection
