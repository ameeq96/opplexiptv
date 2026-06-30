@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')
<div class="admin-card mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-9">
            <input type="text" class="form-control" name="q" value="{{ $search }}" placeholder="Search products">
        </div>
        <div class="col-md-3 d-grid">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>
</div>

<div class="d-flex justify-content-end align-items-center mb-3">
    <a href="{{ route('admin.shop-products.create') }}" class="btn btn-primary">+ New Product</a>
</div>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td>{{ $p['name'] }}</td>
                        <td>
                            <span class="badge-soft {{ $p['type'] === 'digital' ? 'success' : 'gray' }}">{{ ucfirst($p['type']) }}</span>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($p['description'], 70) }}</td>
                        <td>
                            @if($p['price'] !== null)
                                {{ $p['currency'] }} {{ number_format((float) $p['price'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge-soft {{ $p['is_active'] ? 'success' : 'gray' }}">{{ $p['is_active'] ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="text-end d-flex justify-content-end gap-2">
                            <a class="btn btn-sm btn-light" href="{{ $p['edit_url'] }}">Edit</a>
                            <form method="POST" action="{{ route('admin.products.toggle-status') }}">
                                @csrf
                                <input type="hidden" name="type" value="{{ $p['type'] }}">
                                <input type="hidden" name="id" value="{{ $p['id'] }}">
                                <button class="btn btn-sm btn-outline-secondary" type="submit">Toggle</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $products->links('pagination::bootstrap-5') }}</div>
@endsection
