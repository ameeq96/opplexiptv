<header class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn admin-icon-button d-lg-none" data-admin-sidebar="toggle" aria-label="Toggle sidebar" aria-controls="adminSidebar" aria-expanded="false">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>
        <div>
            <h2 class="page-title mb-1">
                @hasSection('page_title')
                    @yield('page_title')
                @else
                    @yield('title', 'Dashboard')
                @endif
            </h2>
            @include('admin.partials.breadcrumbs')
        </div>
    </div>
    <div class="admin-topbar-actions">
        @if (app()->environment('local'))
            <span class="admin-environment"><span aria-hidden="true"></span> Local preview</span>
        @endif
        <a class="admin-website-link" href="{{ url('/') }}" target="_blank" rel="noopener" aria-label="View website (opens in a new tab)"><i class="bi bi-arrow-up-right-square" aria-hidden="true"></i><span>View website</span></a>

        <form class="m-0" method="POST" action="{{ route('admin.locale') }}">
            @csrf
            <select name="locale" class="form-select no-select2 admin-locale" aria-label="Admin language" onchange="this.form.submit()">
                @foreach (admin_locales() as $loc)
                    <option value="{{ $loc }}" @selected(admin_locale() === $loc)>{{ strtoupper($loc) }}</option>
                @endforeach
            </select>
        </form>

        <div class="dropdown">
            <button type="button" class="btn admin-icon-button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                <i class="bi bi-bell" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end admin-notification-menu">
                <strong class="d-block mb-1">Notifications</strong>
                <p class="text-muted mb-0">This workspace does not have a notification feed connected yet.</p>
            </div>
        </div>

        <div class="dropdown">
            <button type="button" class="btn admin-profile-button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Admin account menu">
                <span class="admin-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth('admin')->user()?->name ?? 'A', 0, 1)) }}</span>
                <span class="admin-profile-copy"><strong>{{ auth('admin')->user()?->name ?? 'Admin' }}</strong><small>{{ ucfirst(auth('admin')->user()?->role ?? 'Admin') }}</small></span>
                <i class="bi bi-chevron-down" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="{{ route('admin.password.change') }}"><i class="bi bi-key me-2" aria-hidden="true"></i>Change password</a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>
