@extends('admin.layouts.app')

@section('title', 'Two-Factor Authentication')
@section('page_title', 'Account Security')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Two-Factor Authentication</li>
@endsection

@section('content')
    <div class="admin-card" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
            <div>
                <h4 class="mb-1">Authenticator app</h4>
                <p class="text-muted mb-0">Protect this admin account with a rotating one-time code.</p>
            </div>
            @if ($admin->two_factor_confirmed_at)
                <span class="badge bg-success">Enabled</span>
            @else
                <span class="badge bg-warning text-dark">Setup required</span>
            @endif
        </div>

        @if (!$admin->two_factor_confirmed_at)
            <div class="alert alert-warning">
                Admin access stays locked until two-factor authentication is confirmed.
            </div>
            <ol class="mb-4">
                <li class="mb-2">Open Google Authenticator, Microsoft Authenticator, 1Password or another TOTP app.</li>
                <li class="mb-2">Choose <strong>Enter setup key</strong> and use the account and secret below.</li>
                <li>Enter the generated 6-digit code to finish setup.</li>
            </ol>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Account</label>
                    <input class="form-control" value="{{ $admin->email }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Secret key</label>
                    <input class="form-control font-monospace" value="{{ $admin->two_factor_secret }}" readonly dir="ltr">
                </div>
            </div>
            <details class="mb-4">
                <summary class="fw-semibold">Advanced: provisioning URI</summary>
                <code class="d-block mt-2 p-3 bg-light text-break" dir="ltr">{{ $provisioningUri }}</code>
            </details>
            <form method="POST" action="{{ route('admin.two-factor.confirm') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-7">
                    <label for="confirm-two-factor-code" class="form-label">6-digit code</label>
                    <input id="confirm-two-factor-code" class="form-control @error('code') is-invalid @enderror"
                        name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <button class="btn btn-primary w-100" type="submit">Enable two-factor authentication</button>
                </div>
            </form>
        @else
            <div class="alert alert-success">Two-factor authentication is active on this account.</div>

            <h5>Recovery codes</h5>
            <p class="text-muted">Each code works once. New codes are displayed only once, so store them somewhere safe and separate from your password.</p>
            <div class="row g-2 mb-4" dir="ltr">
                @forelse ((array) session('admin_recovery_codes', []) as $recoveryCode)
                    <div class="col-sm-6 col-lg-4"><code class="d-block p-2 bg-light rounded text-center">{{ $recoveryCode }}</code></div>
                @empty
                    <div class="col-12"><span class="text-muted">Recovery codes are hidden. Generate new codes below if you did not save them.</span></div>
                @endforelse
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <form method="POST" action="{{ route('admin.two-factor.recovery-codes') }}" class="border rounded p-3 h-100">
                        @csrf
                        <h6>Generate new recovery codes</h6>
                        <input class="form-control mb-2" name="password" type="password" autocomplete="current-password" placeholder="Current password" required>
                        <input class="form-control mb-3" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="6-digit code" required>
                        <button class="btn btn-outline-primary" type="submit">Regenerate codes</button>
                    </form>
                </div>
                <div class="col-lg-6">
                    <form method="POST" action="{{ route('admin.two-factor.disable') }}" class="border border-danger rounded p-3 h-100">
                        @csrf
                        @method('DELETE')
                        <h6>Reset authenticator setup</h6>
                        <input class="form-control mb-2" name="password" type="password" autocomplete="current-password" placeholder="Current password" required>
                        <input class="form-control mb-3" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="6-digit code" required>
                        <button class="btn btn-outline-danger" type="submit">Disable and set up again</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
