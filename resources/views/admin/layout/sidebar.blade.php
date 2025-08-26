<div class="sidebar d-flex flex-column p-3" id="sidebar">
    <h4 class="text-center mb-4">Opplex IPTV</h4>
    <a href="{{ route('admin.dashboard') }}">🏠 Dashboard</a>
    <a href="{{ route('clients.index') }}">👤 Clients</a>
    <a href="{{ route('orders.index') }}">📦 Package Orders</a>
    <a href="{{ route('panel-orders.index') }}">🖥️ Panel Orders</a>
    <a href="{{ route('purchasing.index') }}">💳 Purchasing</a> 

    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="logout-link">🚪 Logout</button>
    </form>
</div>
