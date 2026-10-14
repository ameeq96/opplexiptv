<nav aria-label="breadcrumb" class="small text-muted">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        @hasSection('breadcrumbs')
            @yield('breadcrumbs')
        @else
            @unless (request()->routeIs('admin.dashboard'))
                <li class="breadcrumb-item active" aria-current="page">
                    @hasSection('page_title')
                        @yield('page_title')
                    @else
                        @yield('title', 'Current page')
                    @endif
                </li>
            @endunless
        @endif
    </ol>
</nav>
