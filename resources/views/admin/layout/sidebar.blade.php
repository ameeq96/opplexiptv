<div class="sidebar d-flex flex-column p-3">
    <h4 class="text-center mb-4">Opplex IPTV</h4>

    <a href="{{ route('admin.dashboard') }}">🏠 Dashboard</a>
    <a href="{{ route('clients.index') }}">👤 Clients</a>
    <a href="{{ route('orders.index') }}">📦 Orders</a>
    <a href="{{ route('admin.whatsapp.broadcast') }}">📲 WhatsApp Broadcast</a>

    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="btn btn-danger m-3">Logout</button>
    </form>
</div>
