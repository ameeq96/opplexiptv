@extends('admin.layouts.app')

@section('title', 'Home Services')
@section('page_title', 'Home Services')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Home Services</li>
@endsection

@section('content')
    <div class="admin-card mb-3">
        <form method="GET" class="row g-2">
            <div class="col-lg-6">
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Search title">
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
        <div class="text-muted">{{ $services->total() }} items</div>
        <a href="{{ route('admin.home-services.create') }}" class="btn btn-primary">+ New Service</a>
    </div>

    <div class="admin-card p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $s)
                        <tr>
                            <td>
                                @if ($s->icon)
                                    <img src="{{ asset('images/icons/' . $s->icon) }}" alt="icon"
                                         style="width:48px;height:48px;object-fit:contain;border-radius:10px;">
                                @else
                                    <div class="badge-soft gray">N/A</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $s->title }}</div>
                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit($s->description, 120) }}</div>
                            </td>
                            <td>{{ $s->sort_order }}</td>
                            <td>
                                <span class="badge-soft {{ $s->is_active ? 'success' : 'gray' }}">
                                    {{ $s->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end admin-table-actions">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.home-services.edit', $s) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.home-services.destroy', $s) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this service?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted p-4">
                                No services found. <a href="{{ route('admin.home-services.create') }}">Add one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $services->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
@endsection
