@extends('admin.layouts.app')

@section('page_title', 'Customer 360')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="mb-1">{{ $client->name ?: 'Unnamed client' }}</h3>
            <div class="text-muted">Customer 360 · Client #{{ $client->id }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">Back</a>
            <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-primary">Edit Client</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="admin-card h-100"><div class="text-muted small">Package orders</div><div class="fs-3 fw-bold">{{ $orders->count() }}</div></div></div>
        <div class="col-md-3"><div class="admin-card h-100"><div class="text-muted small">WhatsApp leads</div><div class="fs-3 fw-bold">{{ $trialClicks->count() }}</div></div></div>
        <div class="col-md-3"><div class="admin-card h-100"><div class="text-muted small">Checkout drafts</div><div class="fs-3 fw-bold">{{ $checkoutDrafts->count() }}</div></div></div>
        <div class="col-md-3"><div class="admin-card h-100"><div class="text-muted small">Digital orders</div><div class="fs-3 fw-bold">{{ $digitalOrders->count() }}</div></div></div>
    </div>

    <div class="admin-card mb-4">
        <h5 class="mb-3">Client profile</h5>
        <div class="row g-3">
            <div class="col-md-4"><div class="text-muted small">Email</div><div>{{ $client->email ?: '—' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Phone</div><div>{{ $client->phone ?: '—' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Country</div><div>{{ $client->country ?: '—' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Email consent</div><span class="badge {{ $client->hasMarketingConsent('email') ? 'bg-success' : 'bg-secondary' }}">{{ $client->hasMarketingConsent('email') ? 'Active' : 'Not active' }}</span></div>
            <div class="col-md-4"><div class="text-muted small">WhatsApp consent</div><span class="badge {{ $client->hasMarketingConsent('whatsapp') ? 'bg-success' : 'bg-secondary' }}">{{ $client->hasMarketingConsent('whatsapp') ? 'Active' : 'Not active' }}</span></div>
            <div class="col-md-4"><div class="text-muted small">Ads consent</div><span class="badge {{ $client->hasMarketingConsent('ads') ? 'bg-success' : 'bg-secondary' }}">{{ $client->hasMarketingConsent('ads') ? 'Active' : 'Not active' }}</span></div>
            @if ($client->notes)
                <div class="col-12"><div class="text-muted small">Notes</div><div>{{ $client->notes }}</div></div>
            @endif
        </div>
    </div>

    <div class="admin-card mb-4">
        <h5 class="mb-3">Package and reseller orders</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Date</th><th>Package</th><th>Device</th><th>Type</th><th>Status</th><th>Payment</th><th>Amount</th></tr></thead>
                <tbody>
                @forelse ($orders as $order)
                    @php($orderRoute = $order->type === 'reseller' ? 'admin.panel-orders.show' : 'admin.orders.show')
                    <tr>
                        <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route($orderRoute, $order) }}">{{ $order->package ?: 'Order #'.$order->id }}</a></td>
                        <td>{{ $order->device?->name ?: '—' }}</td>
                        <td>{{ ucfirst($order->type) }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>{{ ucfirst($order->payment_status) }}</td>
                        <td>{{ $order->currency ?: 'USD' }} {{ number_format((float) ($order->sell_price ?? $order->price), 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted text-center">No package or reseller orders.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card mb-4">
        <h5 class="mb-3">WhatsApp leads</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Latest activity</th><th>Lead</th><th>Intent</th><th>Package</th><th>Source</th><th>Status</th><th>Clicks</th></tr></thead>
                <tbody>
                @forelse ($trialClicks as $lead)
                    <tr>
                        <td>{{ ($lead->whatsapp_contact_consented_at ?: $lead->updated_at)?->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route('admin.trial_clicks.index', ['search' => $lead->lead_code]) }}">{{ $lead->lead_code }}</a></td>
                        <td>{{ ucfirst(str_replace('_', ' ', $lead->intent ?: 'contact')) }}</td>
                        <td>{{ $lead->package_name ?: '—' }}</td>
                        <td>{{ $lead->utm_source ?: $lead->placement ?: '—' }}</td>
                        <td>{{ ucfirst($lead->status) }}</td>
                        <td>{{ $lead->click_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted text-center">No linked WhatsApp leads.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card mb-4">
        <h5 class="mb-3">Checkout drafts</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Last activity</th><th>Vendor</th><th>Connection</th><th>Email consent</th><th>WhatsApp consent</th><th>Result</th></tr></thead>
                <tbody>
                @forelse ($checkoutDrafts as $draft)
                    <tr>
                        <td>{{ $draft->last_activity_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $draft->vendor ?: '—' }}</td>
                        <td>{{ $draft->connection_name ?: '—' }}</td>
                        <td>{{ $draft->email_consented_at ? 'Yes' : 'No' }}</td>
                        <td>{{ $draft->whatsapp_consented_at ? 'Yes' : 'No' }}</td>
                        <td>{{ $draft->completedOrder ? 'Order #'.$draft->completedOrder->id : 'Incomplete' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center">No linked checkout drafts.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card mb-4">
        <h5 class="mb-3">Digital orders</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Date</th><th>Order</th><th>Items</th><th>Status</th><th>Payment</th><th>Total</th></tr></thead>
                <tbody>
                @forelse ($digitalOrders as $order)
                    <tr>
                        <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->items_count }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>{{ ucfirst($order->payment_status) }}</td>
                        <td>{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center">No digital orders.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="admin-card h-100">
                <h5 class="mb-3">Referrals</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead><tr><th>Code</th><th>Role</th><th>Status</th><th>Reward</th></tr></thead>
                        <tbody>
                        @forelse ($referrals as $referral)
                            <tr>
                                <td>{{ $referral->code }}</td>
                                <td>{{ $referral->referrer_user_id === $client->id ? 'Referrer' : 'Referred customer' }}</td>
                                <td>{{ ucfirst($referral->status) }}</td>
                                <td>{{ $referral->reward_value !== null ? ($referral->reward_currency.' '.number_format((float) $referral->reward_value, 2)) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center">No referrals.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="admin-card h-100">
                <h5 class="mb-3">Marketing delivery history</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead><tr><th>Scheduled</th><th>Workflow</th><th>Channel</th><th>Result</th></tr></thead>
                        <tbody>
                        @forelse ($marketingDeliveries as $delivery)
                            <tr>
                                <td>{{ $delivery->scheduled_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $delivery->workflow)) }}</td>
                                <td>{{ ucfirst($delivery->channel) }}</td>
                                <td>{{ $delivery->sent_at ? 'Sent' : ($delivery->failed_at ? 'Failed' : 'Pending') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center">No marketing deliveries.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
