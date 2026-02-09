@extends('admin.layouts.app')

@section('title', 'Shop Products')
@section('page_title', 'Shop Products')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Shop Products</li>
@endsection

@section('content')
    <div class="admin-card mb-3">
        <form method="GET" class="row g-2">
            <div class="col-lg-6">
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Search name or ASIN">
            </div>
            <div class="col-lg-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="col-lg-3 d-grid">
                <button class="btn btn-primary" type="submit">Filter</button>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted">{{ $products->total() }} products</div>
        <a href="{{ route('admin.shop-products.create') }}" class="btn btn-primary">+ New Product</a>
    </div>

    <div class="admin-card p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>ASIN</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $p)
                        <tr>
                            <td>
                                <img src="{{ asset('images/shop/' . $p->image) }}" alt="product"
                                     style="width:48px;height:48px;object-fit:cover;border-radius:10px;">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $p->name }}</div>
                                <div class="text-muted small">{{ $p->link }}</div>
                            </td>
                            <td>{{ $p->asin ?? '—' }}</td>
                            <td>{{ $p->sort_order }}</td>
                            <td>
                                <span class="badge-soft {{ $p->is_active ? 'success' : 'gray' }}">
                                    {{ $p->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end admin-table-actions">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.shop-products.edit', $p) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.shop-products.destroy', $p) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted p-4">
                                No products found. <a href="{{ route('admin.shop-products.create') }}">Add one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
@endsection
