@extends('admin.layouts.app')

@section('title', 'Admin Users')
@section('page_title', 'Admin Users')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Admin Users</li>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted">Manage admin access and roles</div>
        <a href="{{ route('admin.admin-users.create') }}" class="btn btn-primary">+ New Admin</a>
    </div>

    <div class="admin-card p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>2FA</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($admins as $adminUser)
                        <tr>
                            <td class="fw-semibold">
                                {{ $adminUser->name }}
                                @if ($adminUser->is(auth('admin')->user()))
                                    <span class="text-muted small">(You)</span>
                                @endif
                            </td>
                            <td>{{ $adminUser->email }}</td>
                            <td><span class="badge-soft gray">{{ ucfirst($adminUser->role ?: 'owner') }}</span></td>
                            <td>
                                @if ($adminUser->two_factor_confirmed_at)
                                    <span class="badge bg-success">Enabled</span>
                                @else
                                    <span class="badge bg-warning text-dark">Required</span>
                                @endif
                            </td>
                            <td class="text-end admin-table-actions">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.admin-users.edit', $adminUser) }}">Edit</a>
                                @if ($adminUser->two_factor_confirmed_at && !$adminUser->is(auth('admin')->user()))
                                    <details class="d-inline-block text-start align-top">
                                        <summary class="btn btn-sm btn-outline-warning">Reset 2FA</summary>
                                        <form method="POST" action="{{ route('admin.admin-users.two-factor.reset', $adminUser) }}"
                                              class="border rounded bg-white p-2 mt-2" style="min-width: 260px;"
                                              onsubmit="return confirm('Reset 2FA, revoke sessions and replace this admin password?')">
                                            @csrf
                                            @method('DELETE')
                                            <input class="form-control form-control-sm mb-2" name="temporary_password" type="password"
                                                minlength="12" autocomplete="new-password" placeholder="New temporary password" required>
                                            <input class="form-control form-control-sm mb-2" name="temporary_password_confirmation" type="password"
                                                minlength="12" autocomplete="new-password" placeholder="Confirm temporary password" required>
                                            <button class="btn btn-sm btn-warning w-100" type="submit">Reset 2FA &amp; password</button>
                                        </form>
                                    </details>
                                @endif
                                @unless ($adminUser->is(auth('admin')->user()))
                                    <form method="POST" action="{{ route('admin.admin-users.destroy', $adminUser) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this admin user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
