<div id="adminSidebarOverlay" data-admin-sidebar="close" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:1040;"></div>
<aside class="admin-sidebar">
    <div class="admin-brand">
        <i class="bi bi-grid"></i>
        <span>Opplex IPTV</span>
    </div>

    <nav class="admin-nav">
        <div class="nav-section">Main</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ admin_active_route('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house"></i> <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> <span>Clients</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> <span>Package Orders</span>
        </a>
        <a href="{{ route('admin.panel-orders.index') }}" class="{{ request()->routeIs('admin.panel-orders.*') ? 'active' : '' }}">
            <i class="bi bi-display"></i> <span>Panel Orders</span>
        </a>
        <a href="{{ route('admin.purchasing.index') }}" class="{{ request()->routeIs('admin.purchasing.*') ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> <span>Purchasing</span>
        </a>
        <a href="{{ route('admin.trial_clicks.index') }}" class="{{ request()->routeIs('admin.trial_clicks.*') ? 'active' : '' }}">
            <i class="bi bi-phone"></i> <span>WhatsApp Trials</span>
        </a>
        <a href="{{ route('admin.blogs.index') }}" class="{{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i> <span>Blogs</span>
        </a>
        <a href="{{ route('admin.shop-products.index') }}" class="{{ request()->routeIs('admin.shop-products.*') ? 'active' : '' }}">
            <i class="bi bi-bag"></i> <span>Shop Products</span>
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

        <div class="nav-section">Account</div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="logout-link w-100 text-start">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </nav>
</aside>
