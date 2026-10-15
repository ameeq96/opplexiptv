<div id="adminSidebarOverlay" class="admin-sidebar-overlay" data-admin-sidebar="close" hidden></div>
<aside class="admin-sidebar" id="adminSidebar" aria-label="Admin navigation">
    @php
        $adminUser = auth('admin')->user();
        $isOwner = $adminUser?->isOwner() ?? false;
        $isSales = $adminUser?->hasRole(\App\Models\Admin::ROLE_SALES) ?? false;
        $isSupport = $adminUser?->hasRole(\App\Models\Admin::ROLE_SUPPORT) ?? false;
        $isContent = $adminUser?->hasRole(\App\Models\Admin::ROLE_CONTENT) ?? false;
    @endphp
    <div class="admin-brand">
        <span class="admin-brand-copy"><img class="admin-brand-logo" src="{{ asset('images/opplexiptvlogo.webp') }}" alt="Opplex IPTV" width="250" height="65"><small>ADMIN WORKSPACE</small></span>
        <button type="button" class="admin-sidebar-close d-lg-none" data-admin-sidebar="close" aria-label="Close navigation"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>

    <div class="admin-nav-search" hidden>
        <label for="adminPageSearch">Find a page</label>
        <div><i class="bi bi-search" aria-hidden="true"></i><input type="search" id="adminPageSearch" placeholder="Orders, clients, settings…" autocomplete="off" aria-controls="adminNavigation"><button type="button" id="adminPageSearchClear" aria-label="Clear page search" hidden><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
        <span id="adminPageSearchStatus" class="visually-hidden" role="status"></span>
    </div>
    <nav class="admin-nav" id="adminNavigation" aria-label="Main navigation">
        <p class="admin-nav-empty" id="adminPageSearchEmpty" hidden>No matching pages. Try another page name.</p>
        <div class="nav-section">Workspace</div>
        @if ($isOwner || $isSales)
            <a href="{{ route('admin.dashboard') }}" class="{{ admin_active_route('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-house"></i> <span>Dashboard</span>
            </a>
        @endif
        @if ($isOwner || $isSales || $isSupport)
        <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> <span>Clients</span><span class="admin-nav-tag">360</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> <span>Package Orders</span>
        </a>
        <a href="{{ route('admin.panel-orders.index') }}" class="{{ request()->routeIs('admin.panel-orders.*') ? 'active' : '' }}">
            <i class="bi bi-display"></i> <span>Panel Orders</span>
        </a>
        @endif
        @if ($isOwner)
        <a href="{{ route('admin.purchasing.index') }}" class="{{ request()->routeIs('admin.purchasing.*') ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> <span>Purchasing</span>
        </a>
        @endif
        @if ($isOwner || $isSales || $isSupport)
        <a href="{{ route('admin.trial_clicks.index') }}" class="{{ request()->routeIs('admin.trial_clicks.*') ? 'active' : '' }}">
            <i class="bi bi-phone"></i> <span>WhatsApp Leads</span>
        </a>
        @endif
        @if ($isOwner || $isContent)
        <div class="nav-section">Content &amp; catalog</div>
        <a href="{{ route('admin.blogs.index') }}" class="{{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i> <span>Blogs</span>
        </a>
        <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') || request()->routeIs('admin.shop-products.*') ? 'active' : '' }}">
            <i class="bi bi-bag"></i> <span>Products</span>
        </a>
        <a href="{{ route('admin.home-services.index') }}" class="{{ request()->routeIs('admin.home-services.*') ? 'active' : '' }}">
            <i class="bi bi-layout-text-window-reverse"></i> <span>Home Services</span>
        </a>
        <a href="{{ route('admin.testimonials.index') }}" class="{{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}">
            <i class="bi bi-chat-quote"></i> <span>Testimonials</span>
        </a>
        <a href="{{ route('admin.channel-logos.index') }}" class="{{ request()->routeIs('admin.channel-logos.*') ? 'active' : '' }}">
            <i class="bi bi-images"></i> <span>Channel Logos</span>
        </a>
        <a href="{{ route('admin.menu-items.index') }}" class="{{ request()->routeIs('admin.menu-items.*') ? 'active' : '' }}">
            <i class="bi bi-list"></i> <span>Menu Items</span>
        </a>
        <a href="{{ route('admin.packages.index') }}" class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> <span>Packages</span>
        </a>
        <a href="{{ route('admin.pricing-section.edit') }}" class="{{ request()->routeIs('admin.pricing-section.*') ? 'active' : '' }}">
            <i class="bi bi-ui-checks"></i> <span>Pricing Section</span>
        </a>
        <a href="{{ route('admin.footer-settings.edit') }}" class="{{ request()->routeIs('admin.footer-settings.*') ? 'active' : '' }}">
            <i class="bi bi-layout-text-sidebar-reverse"></i> <span>Footer Settings</span>
        </a>
        <a href="{{ route('admin.footer-links.index') }}" class="{{ request()->routeIs('admin.footer-links.*') ? 'active' : '' }}">
            <i class="bi bi-link-45deg"></i> <span>Footer Links</span>
        </a>
        <a href="{{ route('admin.social-links.index') }}" class="{{ request()->routeIs('admin.social-links.*') ? 'active' : '' }}">
            <i class="bi bi-share"></i> <span>Social Links</span>
        </a>
        @endif

        @if ($isOwner)
            <div class="nav-section">System</div>
            <a href="{{ route('admin.admin-users.index') }}" class="{{ request()->routeIs('admin.admin-users.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i> <span>Admin Users</span>
            </a>
            <a href="{{ route('admin.audit-logs.index') }}" class="{{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                <i class="bi bi-journal-check"></i> <span>Audit Logs</span>
            </a>
            <a href="{{ route('admin.maintenance.index') }}" class="{{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                <i class="bi bi-tools"></i> <span>System Maintenance</span>
            </a>
        @endif

        <div class="nav-section">Account</div>
        <a href="{{ route('admin.password.change') }}" class="{{ request()->routeIs('admin.password.change') ? 'active' : '' }}">
            <i class="bi bi-key"></i> <span>Change Password</span>
        </a>
        @unless (app()->environment('local'))
        <a href="{{ route('admin.two-factor.settings') }}" class="{{ request()->routeIs('admin.two-factor.settings') ? 'active' : '' }}">
            <i class="bi bi-phone"></i> <span>Two-Factor Authentication</span>
        </a>
        @endunless
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="logout-link w-100 text-start">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </nav>
    <div class="admin-sidebar-footer">
        <span class="admin-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($adminUser?->name ?? 'A', 0, 1)) }}</span>
        <div><strong>{{ $adminUser?->name ?? 'Admin' }}</strong><small>{{ ucfirst($adminUser?->role ?? 'Admin') }} workspace</small></div>
        <i class="bi bi-shield-check" aria-hidden="true"></i>
    </div>
</aside>
