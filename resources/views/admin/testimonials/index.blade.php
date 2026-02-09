@extends('admin.layouts.app')

@section('title', 'Testimonials')
@section('page_title', 'Testimonials')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Testimonials</li>
@endsection

@section('content')
    <div class="admin-card mb-3">
        <form method="GET" class="row g-2">
            <div class="col-lg-6">
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Search name or text">
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
        <div class="text-muted">{{ $testimonials->total() }} items</div>
        <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary">+ New Testimonial</a>
    </div>

    <div class="admin-card p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Text</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($testimonials as $t)
                        <tr>
                            <td>
                                @if ($t->image)
                                    <img src="{{ asset($t->image) }}" alt="photo"
                                         style="width:48px;height:48px;object-fit:cover;border-radius:10px;">
                                @else
                                    <div class="badge-soft gray">N/A</div>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $t->author_name }}</td>
                            <td class="text-muted small">{{ \Illuminate\Support\Str::limit($t->text, 90) }}</td>
                            <td>{{ $t->sort_order }}</td>
                            <td>
                                <span class="badge-soft {{ $t->is_active ? 'success' : 'gray' }}">
                                    {{ $t->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end admin-table-actions">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.testimonials.edit', $t) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this testimonial?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted p-4">
                                No testimonials found. <a href="{{ route('admin.testimonials.create') }}">Add one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $testimonials->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
@endsection
