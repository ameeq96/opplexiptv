@extends('admin.layouts.app')

@section('title', 'Confirm Sensitive Access')
@section('page_title', 'Confirm Sensitive Access')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Security confirmation</li>
@endsection

@section('content')
    <div class="admin-card" style="max-width: 620px;">
        <h4 class="mb-2">Verify it is you</h4>
        <p class="text-muted mb-4">
            Enter your current password and a fresh authenticator code before managing admin accounts.
            This confirmation remains valid for 10 minutes.
        </p>

        <form method="POST" action="{{ route('admin.security.confirm.store') }}">
            @csrf
            <div class="mb-3">
                <label for="sensitive-password" class="form-label">Current password</label>
                <input id="sensitive-password" class="form-control @error('password') is-invalid @enderror"
                    name="password" type="password" autocomplete="current-password" required autofocus>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label for="sensitive-code" class="form-label">Fresh 6-digit authenticator code</label>
                <input id="sensitive-code" class="form-control @error('code') is-invalid @enderror"
                    name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                    maxlength="6" required>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary" type="submit">Confirm and continue</button>
        </form>
    </div>
@endsection
