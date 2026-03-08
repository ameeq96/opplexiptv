@extends('admin.layouts.app')

@section('title', 'Digital Products')

@section('content')
<div class="admin-card mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-6">
            <input type="text" name="q" class="form-control" placeholder="Search title or slug" value="{{ $search }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-md-3 d-grid">
            <button class="btn btn-primary" type="submit">Filter</button>
        </div>
    </form>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted">{{ $products->total() }} products</div>
    <a href="{{ route('admin.digital-products.create') }}" class="btn btn-primary">+ New Product</a>
</div>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Delivery</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            @if($product->image)
                                <img src="{{ asset('images/digital-products/' . $product->image) }}" alt="{{ $product->title }}" style="width:52px;height:52px;border-radius:8px;object-fit:cover;">
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $product->title }}</div>
                            <div class="text-muted small">{{ $product->slug }}</div>
                        </td>
                        <td>{{ $product->category?->name ?? '-' }}</td>
                        <td>${{ number_format((float) $product->price, 2) }}</td>
                        <td><span class="badge-soft gray">{{ ucfirst($product->delivery_type) }}</span></td>
                        <td>
                            <span class="badge-soft {{ $product->is_active ? 'success' : 'gray' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.digital-products.edit', $product) }}" class="btn btn-sm btn-light">Edit</a>
                            <form method="POST" action="{{ route('admin.digital-products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center p-4 text-muted">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $products->links('pagination::bootstrap-5') }}</div>
@endsection
