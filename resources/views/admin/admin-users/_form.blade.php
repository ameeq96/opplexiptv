@csrf

@php $editingSelf = $adminUser->exists && $adminUser->is(auth('admin')->user()); @endphp

<div class="row g-3">
    <div class="col-lg-4">
        <label class="form-label" for="adminName">Name</label>
        <input id="adminName" type="text" name="name" class="form-control" value="{{ old('name', $adminUser->name) }}" required>
    </div>
    <div class="col-lg-4">
        <label class="form-label" for="adminEmail">Email</label>
        <input id="adminEmail" type="email" name="email" class="form-control" value="{{ old('email', $adminUser->email) }}" required>
    </div>
    <div class="col-lg-4">
        <label class="form-label" for="adminRole">Role</label>
        <select id="adminRole" name="role" class="form-select" @disabled($editingSelf) required>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected(old('role', $adminUser->role ?: \App\Models\Admin::ROLE_OWNER) === $role)>
                    {{ ucfirst($role) }}
                </option>
            @endforeach
        </select>
        @if ($editingSelf)
            <input type="hidden" name="role" value="{{ \App\Models\Admin::ROLE_OWNER }}">
            <div class="small text-muted mt-1">You cannot remove your own Owner role.</div>
        @endif
    </div>
    <div class="col-lg-6" @if ($editingSelf) hidden @endif>
        <label class="form-label" for="adminPassword">Password</label>
        <input id="adminPassword" type="password" name="password" class="form-control" @required(!$adminUser->exists) autocomplete="new-password">
        <div class="small text-muted mt-1">
            Minimum 12 characters with uppercase, lowercase, number and symbol.
            @if ($adminUser->exists) Leave blank to keep the current password. @endif
        </div>
    </div>
    <div class="col-lg-6" @if ($editingSelf) hidden @endif>
        <label class="form-label" for="adminPasswordConfirmation">Confirm Password</label>
        <input id="adminPasswordConfirmation" type="password" name="password_confirmation" class="form-control" @required(!$adminUser->exists) autocomplete="new-password">
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
