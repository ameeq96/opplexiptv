<nav aria-label="breadcrumb" class="small text-muted">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        @hasSection('breadcrumbs')
            @yield('breadcrumbs')
        @endif
    </ol>
</nav>
