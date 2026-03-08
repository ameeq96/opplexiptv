@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')
<div class="admin-card mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-5">
            <input type="text" class="form-control" name="q" value="{{ $search }}" placeholder="Search products">
        </div>
        <div class="col-md-4">
            <select class="form-select" name="type">
                <option value="all" @selected($type === 'all')>All Types</option>
                <option value="affiliate" @selected($type === 'affiliate')>Affiliate</option>
                <option value="digital" @selected($type === 'digital')>Digital</option>
            </select>
        </div>
        <div class="col-md-3 d-grid">
            <button class="btn btn-primary" type="submit">Filter</button>
        </div>
    </form>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group" role="group">
        <a href="{{ route('admin.products.index', ['type' => 'all']) }}" class="btn btn-sm {{ $type === 'all' ? 'btn-primary' : 'btn-light' }}">All</a>
        <a href="{{ route('admin.products.index', ['type' => 'affiliate']) }}" class="btn btn-sm {{ $type === 'affiliate' ? 'btn-primary' : 'btn-light' }}">Affiliate</a>
        <a href="{{ route('admin.products.index', ['type' => 'digital']) }}" class="btn btn-sm {{ $type === 'digital' ? 'btn-primary' : 'btn-light' }}">Digital</a>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.shop-products.create') }}" class="btn btn-outline-primary">+ New Affiliate</a>
        <a href="{{ route('admin.digital-products.create') }}" class="btn btn-primary">+ New Digital</a>
    </div>
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
