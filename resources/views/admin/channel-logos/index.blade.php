@extends('admin.layouts.app')

@section('title', 'Channel Logos')
@section('page_title', 'Channel Logos')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Channel Logos</li>
@endsection

@section('content')
    <div class="admin-card mb-3">
        <form method="GET" class="row g-2">
            <div class="col-lg-6"></div>
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
        <div class="text-muted">{{ $logos->total() }} logos</div>
        <a href="{{ route('admin.channel-logos.create') }}" class="btn btn-primary">+ New Logo</a>
    </div>

    <div class="admin-card p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logos as $l)
                        <tr>
                            <td>
                                <img src="{{ asset($l->image) }}" alt="logo"
                                     style="width:80px;height:48px;object-fit:contain;border-radius:6px;">
                            </td>
                            <td>{{ $l->sort_order }}</td>
                            <td>
                                <span class="badge-soft {{ $l->is_active ? 'success' : 'gray' }}">
                                    {{ $l->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end admin-table-actions">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.channel-logos.edit', $l) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.channel-logos.destroy', $l) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this logo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted p-4">
                                No logos found. <a href="{{ route('admin.channel-logos.create') }}">Add one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $logos->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
@endsection
