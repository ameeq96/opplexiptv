<header class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light d-lg-none" data-admin-sidebar="toggle" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        <div>
            <h2 class="mb-0">@yield('page_title', 'Dashboard')</h2>
            @include('admin.partials.breadcrumbs')
        </div>
    </div>
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <input class="search-input" type="text" placeholder="Search..." aria-label="Search">

        <form class="m-0" method="POST" action="{{ route('admin.locale') }}">
            @csrf
            <select name="locale" class="form-select" onchange="this.form.submit()">
                @foreach (admin_locales() as $loc)
                    <option value="{{ $loc }}" @selected(admin_locale() === $loc)>{{ strtoupper($loc) }}</option>
                @endforeach
            </select>
        </form>

        <div class="dropdown">
            <button class="btn btn-light" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-2">
                <div class="small text-muted">No new notifications.</div>
            </div>
        </div>

        <div class="dropdown">
            <button class="btn btn-light" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i> <span class="ms-1">Admin</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="#">Profile</a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>
