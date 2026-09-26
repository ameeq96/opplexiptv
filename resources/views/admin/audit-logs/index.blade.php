@extends('admin.layouts.app')

@section('title', 'Admin Audit Logs')
@section('page_title', 'Admin Audit Logs')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Audit Logs</li>
@endsection

@section('content')
    <div class="admin-card mb-4">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-lg-5">
                <label for="audit-search" class="form-label">Search</label>
                <input id="audit-search" type="search" name="search" class="form-control"
                       value="{{ $filters['search'] ?? '' }}" placeholder="Admin, action, route or target">
            </div>
            <div class="col-12 col-sm-4 col-lg-2">
                <label for="audit-method" class="form-label">Method</label>
                <select id="audit-method" name="method" class="form-select no-select2">
                    <option value="">All methods</option>
                    @foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method)
                        <option value="{{ $method }}" @selected(($filters['method'] ?? '') === $method)>{{ $method }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <label for="audit-date-from" class="form-label">From</label>
                <input id="audit-date-from" type="date" name="date_from" class="form-control"
                       value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <label for="audit-date-to" class="form-label">To</label>
                <input id="audit-date-to" type="date" name="date_to" class="form-control"
                       value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="col-12 col-lg-1 d-grid">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>

    <div class="admin-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Method</th>
                        <th>Route</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th class="text-end">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $log->admin_name ?: 'Deleted admin' }}</div>
                                <small class="text-muted">{{ $log->admin_email }}</small>
                            </td>
                            <td>{{ $log->action }}</td>
                            <td><span class="badge text-bg-secondary">{{ $log->method }}</span></td>
                            <td><code>{{ $log->route_name ?: $log->path }}</code></td>
                            <td>
                                @if ($log->target_type || $log->target_id)
                                    <span>{{ $log->target_type ? class_basename($log->target_type) : 'Record' }}</span>
                                    <small class="text-muted">#{{ $log->target_id }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $log->response_status ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.audit-logs.show', $log) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">No audit activity found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
@endsection
