@extends('admin.layouts.app')

@section('title', 'Menu Items')
@section('page_title', 'Menu Items')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Menu Items</li>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted">Manage header menu</div>
        <a href="{{ route('admin.menu-items.create') }}" class="btn btn-primary">+ New Item</a>
    </div>

    <div class="admin-card p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>URL</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $i)
                        <tr>
                            <td class="fw-semibold" data-admin-api-field="label" data-admin-api-object="i" data-admin-api-id="{{ $i->id }}">{{ $i->label }}</td>
                            <td class="text-muted small" data-admin-api-field="url" data-admin-api-object="i" data-admin-api-id="{{ $i->id }}">{{ $i->url }}</td>
                            <td data-admin-api-field="sort_order" data-admin-api-object="i" data-admin-api-id="{{ $i->id }}">{{ $i->sort_order }}</td>
                            <td>
                                <span class="badge-soft {{ $i->is_active ? 'success' : 'gray' }}">
                                    {{ $i->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end admin-table-actions">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.menu-items.edit', $i) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.menu-items.destroy', $i) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this item?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @foreach ($i->children as $c)
                            <tr>
                                <td class="ps-4">— {{ $c->label }}</td>
                                <td class="text-muted small" data-admin-api-field="url" data-admin-api-object="c" data-admin-api-id="{{ $c->id }}">{{ $c->url }}</td>
                                <td data-admin-api-field="sort_order" data-admin-api-object="c" data-admin-api-id="{{ $c->id }}">{{ $c->sort_order }}</td>
                                <td>
                                    <span class="badge-soft {{ $c->is_active ? 'success' : 'gray' }}">
                                        {{ $c->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end admin-table-actions">
                                    <a class="btn btn-sm btn-light" href="{{ route('admin.menu-items.edit', $c) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.menu-items.destroy', $c) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this item?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted p-4">
                                No menu items found. <a href="{{ route('admin.menu-items.create') }}">Add one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
