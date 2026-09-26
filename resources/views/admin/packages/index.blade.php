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
                    <option value="starshare" @selected($vendor === 'starshare')>Filex</option>
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
                        <th>Merchandising</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($packageGroups as $group)
                        @if ($group['is_provider'])
                            <tr class="table-light">
                                <td colspan="8" class="py-3">
                                    <form method="POST" action="{{ route('admin.packages.update', $group['representative']) }}"
                                          class="d-flex flex-wrap align-items-center gap-3">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="provider_merchandising" value="1">
                                        <div class="me-auto">
                                            <div class="fw-bold">{{ $group['name'] }}</div>
                                            <div class="small text-muted">{{ $group['packages']->count() }} duration plans</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="is_featured" value="0">
                                            <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                                                id="featured-provider-{{ $group['representative']->id }}"
                                                @checked($group['is_featured'])>
                                            <label class="form-check-label" for="featured-provider-{{ $group['representative']->id }}">Featured</label>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="is_available" value="0">
                                            <input class="form-check-input" type="checkbox" name="is_available" value="1"
                                                id="available-provider-{{ $group['representative']->id }}"
                                                @checked($group['is_available'])>
                                            <label class="form-check-label" for="available-provider-{{ $group['representative']->id }}">Available</label>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="small text-muted mb-0" for="order-provider-{{ $group['representative']->id }}">Provider order</label>
                                            <input type="number" min="0" name="provider_order" class="form-control form-control-sm"
                                                id="order-provider-{{ $group['representative']->id }}"
                                                value="{{ $group['provider_order'] }}" style="width: 82px">
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary">Save provider</button>
                                    </form>
                                </td>
                            </tr>
                        @endif

                        @foreach ($group['packages'] as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->title }}</td>
                            <td>{{ $p->type }}</td>
                            <td>{{ $p->vendor === 'starshare' ? 'Filex' : $p->vendor }}</td>
                            <td>{{ $p->display_price }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @if ($p->badge_key)
                                        <span class="badge-soft gray">{{ str($p->badge_key)->replace('_', ' ')->title() }}</span>
                                    @endif
                                    @if ($p->is_featured)
                                        <span class="badge-soft success">Featured</span>
                                    @endif
                                    @unless ($p->is_available)
                                        <span class="badge-soft gray">Unavailable</span>
                                    @endunless
                                    @if ($p->free_trial_hours)
                                        <span class="badge-soft gray">{{ $p->free_trial_hours }}h trial</span>
                                    @endif
                                    @if ($p->instant_activation)
                                        <span class="badge-soft success">Instant</span>
                                    @endif
                                    @if (! $p->badge_key && ! $p->is_featured && $p->is_available && ! $p->free_trial_hours && ! $p->instant_activation)
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </td>
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
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted p-4">
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
