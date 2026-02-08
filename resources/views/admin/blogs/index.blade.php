@extends('admin.layouts.app')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Blogs')
@section('page_title', 'Blogs')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Blogs</li>
@endsection

@section('content')
    <div class="admin-card mb-3">
        <form method="GET" class="row g-2">
            <div class="col-lg-4">
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Search title or slug">
            </div>
            <div class="col-lg-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" @selected($status === 'draft')>Draft</option>
                    <option value="published" @selected($status === 'published')>Published</option>
                    <option value="archived" @selected($status === 'archived')>Archived</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select name="featured" class="form-select">
                    <option value="">Featured: All</option>
                    <option value="1" @selected($featured === '1')>Featured only</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select name="locale" class="form-select">
                    @foreach ($locales as $loc)
                        <option value="{{ $loc }}" @selected($locale === $loc)>{{ strtoupper($loc) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 d-grid">
                <button class="btn btn-primary" type="submit">Filter</button>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted">{{ $blogs->total() }} blogs</div>
        <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">+ New Blog</a>
    </div>

    <div class="admin-card p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title ({{ strtoupper($locale) }})</th>
                        <th>Locales</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th>Views</th>
                        <th>Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($blogs as $blog)
                        @php
                            $t = $blog->translation($locale);
                            $statusClass = $blog->status === 'published' ? 'success' : ($blog->status === 'draft' ? 'warn' : 'gray');
                        @endphp
                        <tr>
                            <td>
                                @if ($blog->cover_image)
                                    <img src="{{ asset(Storage::url($blog->cover_image)) }}" alt="cover" style="width:48px;height:48px;object-fit:cover;border-radius:10px;">
                                @else
                                    <div class="badge-soft gray">N/A</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $t?->title ?? '—' }}</div>
                                <div class="text-muted small">{{ $t?->slug }}</div>
                            </td>
                            <td>
                                @foreach ($blog->translations as $tr)
                                    <span class="badge-soft gray">{{ strtoupper($tr->locale) }}</span>
                                @endforeach
                            </td>
                            <td><span class="badge-soft {{ $statusClass }}">{{ ucfirst($blog->status) }}</span></td>
                            <td>{{ optional($blog->published_at)->format('Y-m-d') }}</td>
                            <td>{{ number_format($blog->views) }}</td>
                            <td>{{ optional($blog->updated_at)->format('Y-m-d') }}</td>
                            <td class="text-end admin-table-actions">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">Actions</button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="{{ route('admin.blogs.edit', $blog) }}">Edit</a>
                                        @if ($blog->status === 'draft')
                                            <form method="POST" action="{{ route('admin.blogs.publish', $blog) }}">
                                                @csrf
                                                <button class="dropdown-item" type="submit">Publish</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.blogs.archive', $blog) }}">
                                            @csrf
                                            <button class="dropdown-item" type="submit">Archive</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.blogs.duplicate', $blog) }}">
                                            @csrf
                                            <button class="dropdown-item" type="submit">Duplicate</button>
                                        </form>
                                        <div class="dropdown-divider"></div>
                                        <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}" onsubmit="return confirm('Delete this blog?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item text-danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted p-4">
                                No blogs found. <a href="{{ route('admin.blogs.create') }}">Create a blog</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $blogs->links() }}
    </div>
@endsection
