@extends('admin.layouts.app')

@section('title', 'Audit Log Details')
@section('page_title', 'Audit Log Details')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.audit-logs.index') }}">Audit Logs</a></li>
    <li class="breadcrumb-item active" aria-current="page">#{{ $auditLog->id }}</li>
@endsection

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">Back to Audit Logs</a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <div class="admin-card h-100">
                <h5 class="mb-4">Action</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Date</dt>
                    <dd class="col-sm-8">{{ $auditLog->created_at?->format('Y-m-d H:i:s') }}</dd>

                    <dt class="col-sm-4">Admin</dt>
                    <dd class="col-sm-8">
                        {{ $auditLog->admin_name ?: 'Deleted admin' }}
                        @if ($auditLog->admin_email)
                            <span class="text-muted">({{ $auditLog->admin_email }})</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Action</dt>
                    <dd class="col-sm-8">{{ $auditLog->action }}</dd>

                    <dt class="col-sm-4">Request</dt>
                    <dd class="col-sm-8"><span class="badge text-bg-secondary">{{ $auditLog->method }}</span> {{ $auditLog->path }}</dd>

                    <dt class="col-sm-4">Route</dt>
                    <dd class="col-sm-8"><code>{{ $auditLog->route_name ?: '—' }}</code></dd>

                    <dt class="col-sm-4">Target</dt>
                    <dd class="col-sm-8">
                        @if ($auditLog->target_type || $auditLog->target_id)
                            {{ $auditLog->target_type ?: 'Record' }} #{{ $auditLog->target_id }}
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-sm-4">Response status</dt>
                    <dd class="col-sm-8">{{ $auditLog->response_status ?? '—' }}</dd>

                    <dt class="col-sm-4">IP address</dt>
                    <dd class="col-sm-8">{{ $auditLog->ip_address ?: '—' }}</dd>

                    <dt class="col-sm-4">User agent</dt>
                    <dd class="col-sm-8 text-break">{{ $auditLog->user_agent ?: '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="admin-card h-100">
                <h5 class="mb-3">Sanitized request metadata</h5>
                <p class="text-muted small">Passwords, security tokens, CAPTCHA values and payment secrets are redacted.</p>
                <pre class="bg-light border rounded p-3 mb-0 overflow-auto" style="max-height: 520px;"><code>{{ json_encode($auditLog->request_data ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </div>
    </div>
@endsection
