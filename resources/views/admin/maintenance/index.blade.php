@extends('admin.layouts.app')

@section('title', 'System Maintenance')
@section('page_title', 'System Maintenance')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">System Maintenance</li>
@endsection

@section('content')
    <div class="alert alert-warning" role="alert">
        <div class="d-flex gap-3">
            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
            <div>
                <strong>Run maintenance actions only when needed.</strong>
                Cache rebuilds can briefly increase server work. Clearing application caches also temporarily removes
                scheduler overlap and unique-job locks, while a queue restart lets active jobs finish before workers exit.
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach ($actions as $key => $action)
            <div class="col-12 {{ $key === 'full' ? '' : 'col-lg-6 col-xxl-4' }}">
                <div class="admin-card h-100 d-flex flex-column">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="fs-3 text-primary">
                            <i class="bi {{ $action['icon'] }}"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $action['label'] }}</h5>
                            <p class="text-muted mb-0">{{ $action['description'] }}</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        @foreach ($action['commands'] as $command)
                            <code class="d-block bg-light border rounded px-3 py-2 mb-2 text-dark">php artisan {{ $command }}</code>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('admin.maintenance.run') }}" class="mt-auto"
                          onsubmit="if (!confirm('Run this maintenance action now?')) return false; this.querySelector('button[type=submit]').disabled = true;">
                        @csrf
                        <input type="hidden" name="action" value="{{ $key }}">
                        <button type="submit" class="btn btn-{{ $action['style'] }}">
                            <i class="bi {{ $action['icon'] }} me-1"></i>
                            {{ $action['button'] }}
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection
