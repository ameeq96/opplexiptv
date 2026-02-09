@extends('admin.layouts.app')

@section('title', 'Packages')
@section('page_title', 'Packages')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Packages</li>
@endsection

@section('content')
    <div class="admin-card mb-3">
        <form method="GET" class="row g-2">
            <div class="col-lg-4">
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Search title">
            </div>
            <div class="col-lg-3">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="iptv" @selected($type === 'iptv')>IPTV</option>
                    <option value="reseller" @selected($type === 'reseller')>Reseller</option>
                </select>
            </div>
            <div class="col-lg-3">
                <select name="vendor" class="form-select">
                    <option value="">All Vendors</option>
                    <option value="opplex" @selected($vendor === 'opplex')>Opplex</option>
                    <option value="starshare" @selected($vendor === 'starshare')>Starshare</option>
                </select>
            </div>
            <div class="col-lg-2 d-grid">
                <button class="btn btn-primary" type="submit">Filter</button>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted">{{ $packages->total() }} items</div>
        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">+ New Package</a>
    </div>

    <div class="admin-card p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Vendor</th>
                        <th>Price</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($packages as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->title }}</td>
                            <td>{{ $p->type }}</td>
                            <td>{{ $p->vendor }}</td>
                            <td>{{ $p->display_price }}</td>
                            <td>{{ $p->sort_order }}</td>
                            <td>
                                <span class="badge-soft {{ $p->active ? 'success' : 'gray' }}">
                                    {{ $p->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end admin-table-actions">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.packages.edit', $p) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.packages.destroy', $p) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this package?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted p-4">
                                No packages found. <a href="{{ route('admin.packages.create') }}">Add one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $packages->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
@endsection
