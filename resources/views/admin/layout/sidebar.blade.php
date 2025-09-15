<div class="sidebar d-flex flex-column p-3" id="sidebar">
    <h4 class="text-center mb-4">Opplex IPTV</h4>
    <a href="{{ route('admin.dashboard') }}">🏠 Dashboard</a>
    <a href="{{ route('admin.clients.index') }}">👤 Clients</a>
    <a href="{{ route('admin.orders.index') }}">📦 Package Orders</a>
    <a href="{{ route('admin.panel-orders.index') }}">🖥️ Panel Orders</a>
    <a href="{{ route('admin.purchasing.index') }}">💳 Purchasing</a> 

    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="logout-link">🚪 Logout</button>
    </form>
</div>
