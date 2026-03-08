@extends('admin.layouts.app')

@section('title', 'Digital Categories')

@section('content')
<div class="admin-card mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md-9">
            <input type="text" class="form-control" name="q" value="{{ $search }}" placeholder="Search categories">
        </div>
        <div class="col-md-3 d-grid">
            <button class="btn btn-primary" type="submit">Filter</button>
        </div>
    </form>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted">{{ $categories->total() }} categories</div>
    <a href="{{ route('admin.digital-categories.create') }}" class="btn btn-primary">+ New Category</a>
</div>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Sort</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td><code>{{ $category->slug }}</code></td>
                    <td>{{ $category->sort_order }}</td>
                    <td>
                        <span class="badge-soft {{ $category->is_active ? 'success' : 'gray' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.digital-categories.edit', $category) }}" class="btn btn-sm btn-light">Edit</a>
                        <form method="POST" action="{{ route('admin.digital-categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center p-4 text-muted">No categories found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $categories->links('pagination::bootstrap-5') }}</div>
@endsection
