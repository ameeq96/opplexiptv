@extends('admin.layouts.app')

@section('page_title', 'Customer 360')

@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
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
        <div class="col-md-3"><div class="admin-card h-100"><div class="text-muted small">Package orders</div><div class="fs-3 fw-bold">{{ $client->orders_count }}</div></div></div>
        <div class="col-md-3"><div class="admin-card h-100"><div class="text-muted small">Captured leads</div><div class="fs-3 fw-bold">{{ $client->trial_clicks_count }}</div></div></div>
        <div class="col-md-3"><div class="admin-card h-100"><div class="text-muted small">Checkout drafts</div><div class="fs-3 fw-bold">{{ $client->checkout_drafts_count }}</div></div></div>
        <div class="col-md-3"><div class="admin-card h-100"><div class="text-muted small">Digital orders</div><div class="fs-3 fw-bold">{{ $client->digital_orders_count }}</div></div></div>
    </div>

    <div class="admin-card mb-4">
        <h5 class="mb-3">Client profile</h5>
        <div class="row g-3">
            <div class="col-md-4"><div class="text-muted small">Email</div><div>{{ $client->email ?: '—' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Phone</div><div>{{ $client->phone ?: '—' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Country</div><div>{{ $client->country ?: '—' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Assigned team member</div><div>{{ $client->assignedAdmin?->name ?: 'Unassigned' }}</div></div>
            <div class="col-md-8"><div class="text-muted small">Recorded paid totals (test payments excluded)</div>@forelse ($paidTotals as $total)<span class="badge bg-success me-2">{{ $total->total_currency ?: 'Unknown currency' }} {{ number_format((float) $total->paid_total, 2) }}</span>@empty<span>No paid orders</span>@endforelse</div>
            <div class="col-md-4"><div class="text-muted small">Email consent</div><span class="badge {{ $client->hasMarketingConsent('email') ? 'bg-success' : 'bg-secondary' }}">{{ $client->hasMarketingConsent('email') ? 'Active' : 'Not active' }}</span></div>
            <div class="col-md-4"><div class="text-muted small">WhatsApp consent</div><span class="badge {{ $client->hasMarketingConsent('whatsapp') ? 'bg-success' : 'bg-secondary' }}">{{ $client->hasMarketingConsent('whatsapp') ? 'Active' : 'Not active' }}</span></div>
            <div class="col-md-4"><div class="text-muted small">Ads consent</div><span class="badge {{ $client->hasMarketingConsent('ads') ? 'bg-success' : 'bg-secondary' }}">{{ $client->hasMarketingConsent('ads') ? 'Active' : 'Not active' }}</span></div>
            @if ($client->notes)
                <div class="col-12"><div class="text-muted small">Notes</div><div>{{ $client->notes }}</div></div>
            @endif
        </div>
        @if (auth('admin')->user()->hasRole('owner', 'sales'))
            <form class="d-flex flex-wrap gap-2 mt-3" action="{{ route('admin.clients.assignment', $client) }}" method="POST">
                @csrf @method('PATCH')
                <label class="visually-hidden" for="customer-assignment">Assigned team member</label>
                <select id="customer-assignment" name="assigned_admin_id" class="form-select w-auto"><option value="">Unassigned</option>@foreach ($admins as $admin)<option value="{{ $admin->id }}" @selected((int) $client->assigned_admin_id === (int) $admin->id)>{{ $admin->name }} ({{ $admin->role }})</option>@endforeach</select>
                <button class="btn btn-outline-primary" type="submit">Save assignment</button>
            </form>
        @endif
    </div>

    <div class="admin-card mb-4">
        <h5>Current synced payment / invoice state</h5>
        <p class="text-muted small">Latest accepted sequence for each checkout, payment and invoice. A late historical event in the timeline does not replace a newer state. Payment verification and invoice email delivery are separate.</p>
        @forelse ($externalLinks as $link)
            <article class="border rounded p-3 mb-3">
                <strong>{{ $link->source }} · {{ $link->entity_type }} #{{ $link->external_id }}</strong>
                @if ($link->entity_type === 'shared_checkout')<p class="small text-muted">Associated payment-link activity; actor anonymous / not payer identity.</p>@endif
                @if ($link->order_id)<div><a href="{{ route($link->order?->type === 'reseller' ? 'admin.panel-orders.show' : 'admin.orders.show', $link->order_id) }}">View order #{{ $link->order_id }}</a></div>@endif
                <div class="row g-3 mt-1">
                    @foreach (['checkout', 'payment', 'invoice', 'contact', 'share'] as $stateGroup)
                        @if (isset($link->state[$stateGroup]['data']) && is_array($link->state[$stateGroup]['data']))
                            @php($currentState = $link->state[$stateGroup]['data'])
                            <div class="col-md-4"><div class="border rounded p-3 h-100"><h6>{{ ucfirst($stateGroup) }}</h6><div>{{ str_replace(['.', '_'], ' ', $link->state[$stateGroup]['event_type'] ?? $stateGroup) }}</div><div class="small text-muted">Source sequence {{ $link->state[$stateGroup]['sequence'] ?? '—' }}</div>
                                @if (isset($currentState['amount_minor']) && is_numeric($currentState['amount_minor']))<div>Amount: {{ $currentState['currency'] ?? '' }} {{ number_format((float) $currentState['amount_minor'] / 100, 2) }}</div>@endif
                                @foreach (['status', 'invoice_status', 'recipient', 'reference', 'tracker', 'error_code'] as $stateKey)
                                    @if (isset($currentState[$stateKey]) && is_scalar($currentState[$stateKey]))<div class="small" style="overflow-wrap: anywhere">{{ ucfirst(str_replace('_', ' ', $stateKey)) }}: {{ $currentState[$stateKey] }}</div>@endif
                                @endforeach
                            </div></div>
                        @endif
                    @endforeach
                </div>
            </article>
        @empty<p class="text-muted">No linked external state has been received yet. Existing IPTV order payment statuses remain visible below.</p>@endforelse
        {{ $externalLinks->links('pagination::bootstrap-5') }}
    </div>

    <div class="admin-card mb-4" id="customer-activity">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h5 class="mb-0">Customer activity timeline</h5>
            <form method="GET" action="{{ route('admin.clients.show', $client) }}" class="d-flex gap-2"><label class="visually-hidden" for="activity-source">Activity source</label><select id="activity-source" name="activity_source" class="form-select"><option value="">All sources</option>@foreach ($activitySources as $source)<option value="{{ $source }}" @selected(request('activity_source') === $source)>{{ $source }}</option>@endforeach</select><button type="submit" class="btn btn-outline-primary">Filter</button></form>
        </div>
        <p class="text-muted small">Only captured events are shown. Opening WhatsApp or copying a link does not prove a message was sent, a recipient opened it, or a payment completed. Historical records remain in the sections below.</p>
        @forelse ($activities as $activity)
            <article class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2"><strong>{{ str_replace(['.', '_'], ' ', $activity->event_type) }}</strong><time class="text-muted">{{ $activity->occurred_at?->format('Y-m-d H:i:s') }}</time></div>
                <div class="small text-muted">{{ $activity->source }} · {{ $activity->entity_type ?: 'activity' }} #{{ $activity->entity_id ?: $activity->id }}@if ($activity->admin_id) · Recorded by admin #{{ $activity->admin_id }}@endif</div>
                @if ($activity->entity_type === 'shared_checkout')<div class="small text-muted">Associated payment-link activity; actor anonymous / not payer identity.</div>@endif
                @if ($activity->message)<p class="mb-1 mt-2" style="white-space: pre-wrap; overflow-wrap: anywhere">{{ $activity->message }}</p>@endif
                @if ($activity->contact_name || $activity->contact_email || $activity->contact_phone)<div class="small text-muted">Contact at this event: {{ $activity->contact_name }} {{ $activity->contact_email }} {{ $activity->contact_phone }}</div>@endif
                @if ($activity->order_id)<a href="{{ route($activity->order?->type === 'reseller' ? 'admin.panel-orders.show' : 'admin.orders.show', $activity->order_id) }}">View recorded order #{{ $activity->order_id }}</a>@endif
                @if (isset($activity->details['amount_minor']) && is_numeric($activity->details['amount_minor']))<div class="small">Amount: {{ $activity->details['currency'] ?? '' }} {{ number_format((float) $activity->details['amount_minor'] / 100, 2) }}</div>@endif
                @foreach (['status', 'payment_status', 'reference', 'tracker', 'invoice_status', 'recipient', 'error_code', 'due_at'] as $detail)
                    @if (isset($activity->details[$detail]) && is_scalar($activity->details[$detail]))<div class="small">{{ ucfirst(str_replace('_', ' ', $detail)) }}: {{ $activity->details[$detail] }}</div>@endif
                @endforeach
                @foreach (['status', 'payment_status', 'buying_date', 'expiry_date', 'duration', 'paid_currency', 'paid_amount'] as $detail)
                    @if (isset($activity->details['current'][$detail]) && is_scalar($activity->details['current'][$detail]))<div class="small">{{ ucfirst(str_replace('_', ' ', $detail)) }}: {{ $activity->details['current'][$detail] }}</div>@endif
                @endforeach
            </article>
        @empty<p class="text-muted">No captured activity events yet. Existing orders and leads are still listed below.</p>@endforelse
        {{ $activities->links('pagination::bootstrap-5') }}
        <hr>
        <form action="{{ route('admin.clients.notes.store', $client) }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-4"><label for="note-channel" class="form-label">Record type</label><select name="channel" id="note-channel" class="form-select"><option value="internal">Internal note</option><option value="whatsapp_manual">Manually recorded WhatsApp message</option></select></div>
            <div class="col-md-8"><label for="customer-note" class="form-label">Note / message</label><textarea id="customer-note" name="message" class="form-control" rows="3" maxlength="5000" required></textarea></div>
            <div class="col-12"><button type="submit" class="btn btn-primary">Save internal record</button><span class="text-muted small ms-2">Does not send a WhatsApp message or an email.</span></div>
        </form>
    </div>

    <div class="admin-card mb-4">
        <h5>Follow-ups</h5>
        <p class="text-muted small">Internal tasks only. Creating a task does not send an automated reminder to the customer.</p>
        <form action="{{ route('admin.clients.follow-ups.store', $client) }}" method="POST" class="row g-3 mb-4">
            @csrf
            <div class="col-md-5"><label for="follow-up-note" class="form-label">Task</label><input id="follow-up-note" name="note" maxlength="5000" class="form-control" required></div>
            <div class="col-md-3"><label for="follow-up-due" class="form-label">Due ({{ config('app.timezone') }})</label><input id="follow-up-due" name="due_at" type="datetime-local" class="form-control" required></div>
            <div class="col-md-4"><label for="follow-up-assignee" class="form-label">Assigned to</label><select id="follow-up-assignee" name="assigned_admin_id" class="form-select">@if (auth('admin')->user()->hasRole('owner', 'sales'))<option value="">Unassigned</option>@endif @foreach ($admins as $admin)@if (auth('admin')->user()->hasRole('owner', 'sales') || auth('admin')->id() === $admin->id)<option value="{{ $admin->id }}" @selected(auth('admin')->id() === $admin->id)>{{ $admin->name }}</option>@endif @endforeach</select></div>
            <div class="col-12"><button class="btn btn-outline-primary" type="submit">Add follow-up</button></div>
        </form>
        @forelse ($followUps as $followUp)
            <div class="border rounded p-3 mb-2 d-flex flex-wrap justify-content-between gap-3">
                <div><strong style="overflow-wrap: anywhere">{{ $followUp->note }}</strong><div class="small text-muted">{{ $followUp->assignedAdmin?->name ?: 'Unassigned' }} · Due {{ $followUp->due_at?->format('Y-m-d H:i') }}</div><span class="badge {{ $followUp->status === 'pending' && $followUp->due_at?->isPast() ? 'bg-danger' : 'bg-secondary' }}">{{ $followUp->status === 'pending' && $followUp->due_at?->isPast() ? 'Overdue' : ucfirst($followUp->status) }}</span></div>
                @if ($followUp->status === 'pending' && (auth('admin')->user()->hasRole('owner', 'sales') || !$followUp->assigned_admin_id || (int) $followUp->assigned_admin_id === (int) auth('admin')->id()))<form action="{{ route('admin.clients.follow-ups.complete', [$client, $followUp]) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success" type="submit">Mark complete</button></form>@endif
            </div>
        @empty<p class="text-muted">No follow-ups yet.</p>@endforelse
        {{ $followUps->links('pagination::bootstrap-5') }}
    </div>

    <div class="admin-card mb-4" id="customer-conversations">
        <h5>Captured conversations</h5>
        <p class="text-muted small">Tawk transcripts appear only when supplied by a configured webhook. WhatsApp conversations are not automatically imported by a website link.</p>
        @forelse ($conversations as $chat)
            <div class="border-bottom py-2 d-flex flex-wrap justify-content-between gap-2"><div><strong>{{ $chat->channel }}</strong> · {{ $chat->status }} · {{ $chat->messages_count }} message(s)<div class="small text-muted">{{ $chat->started_at?->format('Y-m-d H:i') ?: $chat->created_at?->format('Y-m-d H:i') }}</div></div><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.clients.show', ['client' => $client, 'conversation_id' => $chat->id]) }}#customer-conversations">Read captured messages</a></div>
        @empty<p class="text-muted">No linked conversations.</p>@endforelse
        {{ $conversations->links('pagination::bootstrap-5') }}
        @if ($conversation)
            <h6 class="mt-4">{{ $conversation->channel }} conversation #{{ $conversation->id }}</h6>
            @forelse ($messages as $message)<div class="rounded border p-3 my-2"><div class="small text-muted">{{ $message->sender_name ?: ucfirst($message->sender_type) }} · {{ $message->sent_at?->format('Y-m-d H:i:s') ?: 'Time not supplied' }}</div><div style="white-space: pre-wrap; overflow-wrap: anywhere">{{ $message->content }}</div></div>@empty<p class="text-muted">No transcript messages supplied.</p>@endforelse
            {{ $messages->links('pagination::bootstrap-5') }}
        @endif
    </div>

    <div class="admin-card mb-4">
        <h5 class="mb-3">Package and reseller orders</h5>
        <p class="text-muted small">Payment and fulfillment are separate: paid does not mean activated. Start/end dates are the stored order dates; this screen does not extend or change subscriptions. Catalog-linked durations are in months; legacy manual duration values need verification.</p>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Order date</th><th>Package</th><th>Device</th><th>Type</th><th>Start / end</th><th>Duration</th><th>Fulfillment</th><th>Payment</th><th>Amount</th></tr></thead>
                <tbody>
                @forelse ($orders as $order)
                    @php($orderRoute = $order->type === 'reseller' ? 'admin.panel-orders.show' : 'admin.orders.show')
                    <tr>
                        <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                        <td><a href="{{ route($orderRoute, $order) }}">{{ $order->package ?: 'Order #'.$order->id }}</a></td>
                        <td>{{ $order->device?->name ?: '—' }}</td>
                        <td>{{ ucfirst($order->type) }}</td>
                        <td><div>{{ $order->buying_date?->format('Y-m-d') ?: 'Not set' }}</div><div>{{ $order->expiry_date?->format('Y-m-d') ?: 'No end date set' }}</div>@if ($order->expiry_date && $order->expiry_date->lt(today()))<span class="badge bg-secondary">Past end date</span>@endif</td>
                        <td>{{ $order->duration ? $order->duration . ($order->package_id ? ' month(s)' : ' (stored value)') : 'Not set' }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>{{ ucfirst($order->payment_status) }}</td>
                        <td>{{ $order->currency ?: 'USD' }} {{ number_format((float) ($order->sell_price ?? $order->price), 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-muted text-center">No package or reseller orders.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links('pagination::bootstrap-5') }}
    </div>

    <div class="admin-card mb-4">
        <h5 class="mb-3">Captured leads (WhatsApp / Tawk)</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Latest activity</th><th>Lead</th><th>Intent</th><th>Package</th><th>Source</th><th>Status</th><th>Clicks</th><th>Captured message</th></tr></thead>
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
                        <td style="white-space: pre-wrap; overflow-wrap: anywhere">{{ $lead->message ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-muted text-center">No linked leads.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $trialClicks->links('pagination::bootstrap-5') }}
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

    {{ $checkoutDrafts->links('pagination::bootstrap-5') }}
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

    {{ $digitalOrders->links('pagination::bootstrap-5') }}
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
    <div class="mt-3">{{ $referrals->links('pagination::bootstrap-5') }}{{ $marketingDeliveries->links('pagination::bootstrap-5') }}</div>
@endsection
