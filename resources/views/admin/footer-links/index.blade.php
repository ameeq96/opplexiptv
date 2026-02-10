@extends('admin.layouts.app')

@section('title', 'Footer Links')
@section('page_title', 'Footer Links')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Footer Links</li>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted">Manage footer links</div>
        <a href="{{ route('admin.footer-links.create') }}" class="btn btn-primary">+ New Link</a>
    </div>

    <div class="admin-card p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Label</th>
                        <th>URL</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($links as $l)
                        <tr>
                            <td>{{ ucfirst($l->group) }}</td>
                            <td class="fw-semibold">{{ $l->label }}</td>
                            <td class="text-muted small">{{ $l->url }}</td>
                            <td>{{ $l->sort_order }}</td>
                            <td>
                                <span class="badge-soft {{ $l->is_active ? 'success' : 'gray' }}">
                                    {{ $l->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end admin-table-actions">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.footer-links.edit', $l) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.footer-links.destroy', $l) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this link?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted p-4">
                                No links found. <a href="{{ route('admin.footer-links.create') }}">Add one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
