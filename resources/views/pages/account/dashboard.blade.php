@extends('pages.account.layout')
@section('title', 'My overview')
@section('content')
<p class="account-eyebrow">My overview</p>
<h1>Your services, at a glance.</h1>
<p class="account-muted account-lead">Signed in as {{ $customer->email }}. Track activation, get help and manage your next renewal.</p>
<div class="account-stats" aria-label="Account summary">
    <div class="account-stat"><strong>{{ $activeCount }}</strong><span>Active services</span></div>
    <div class="account-stat"><strong>{{ $pendingCount }}</strong><span>Awaiting activation</span></div>
    <div class="account-stat"><strong>{{ $openTickets }}</strong><span>Open support requests</span></div>
</div>
<section aria-labelledby="services-title">
    <div class="account-row"><h2 id="services-title">Your orders & services</h2><a class="account-link" href="{{ route('packages') }}">Browse plans</a></div>
    <div class="account-grid">
        @forelse($orders as $order)
        @php($state = $orderStates[$order->id])
        <article class="account-card">
            <div class="account-row"><p class="account-eyebrow">Order #{{ $order->id }}</p><span class="account-badge {{ $state['tone'] }}">{{ $state['label'] }}</span></div>
            <h3>{{ $order->package ?: 'Your service' }}</h3>
            <dl class="account-meta">
                <div><dt>Start date</dt><dd>{{ $order->buying_date?->format('d M Y') ?: 'Not recorded' }}</dd></div>
                <div><dt>End date</dt><dd>{{ $order->expiry_date?->format('d M Y') ?: 'Not recorded' }}</dd></div>
                <div><dt>Payment</dt><dd>{{ $order->payment_status === 'paid' ? 'Confirmed' : ($order->payment_status === 'test_paid' ? 'Test only' : 'Not confirmed') }}</dd></div>
                <div><dt>Remaining</dt><dd>{{ $order->status === 'active' && $order->payment_status === 'paid' && $order->expiry_date ? max(0, (int) ceil(now()->diffInDays($order->expiry_date->copy()->endOfDay(), false))).' days' : '—' }}</dd></div>
            </dl>
            <a class="account-button secondary" href="{{ route('customer.orders.show', $order->id) }}">View order & invoice</a>
        </article>
        @empty
        <div class="account-card account-empty"><h3>No orders linked yet</h3><p class="account-muted">Use the same email when paying. If an older order is missing, support can verify and link it safely.</p><a class="account-button secondary" href="{{ route('customer.support.index') }}">Get help with an order</a></div>
        @endforelse
    </div>
    @if($orders->hasPages())<nav class="account-pagination" aria-label="Order pages">
        @if($orders->previousPageUrl())<a class="account-button secondary" href="{{ $orders->previousPageUrl() }}">Previous</a>@endif
        <span>Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}</span>
        @if($orders->nextPageUrl())<a class="account-button secondary" href="{{ $orders->nextPageUrl() }}">Next</a>@endif
    </nav>@endif
</section>
<section class="account-section" aria-labelledby="trials-title">
    <h2 id="trials-title">Your trials</h2>
    <p class="account-muted">A request is not an activated trial. Actual dates appear when support confirms your service.</p>
    @foreach($trials as $trial)
    @php($trialStatus = $trial->trial_status)
    <article class="account-card">
        <div class="account-row"><h3>{{ $trial->package_name ?: 'Trial request' }}</h3><span class="account-badge {{ $trialStatus === 'active' ? 'success' : 'neutral' }}">{{ $trialStatus === 'pending' ? 'Awaiting activation' : ucfirst($trialStatus) }}</span></div>
        <p class="account-muted">Reference {{ $trial->lead_code }}</p>
        <dl class="account-meta"><div><dt>Starts</dt><dd>{{ $trial->trial_started_at?->format('d M Y, H:i T') ?: 'Not activated yet' }}</dd></div><div><dt>Ends</dt><dd>{{ $trial->trial_expires_at?->format('d M Y, H:i T') ?: 'Not set yet' }}</dd></div></dl>
        @if($trialStatus === 'active')<p role="status">{{ max(0, (int) ceil(now()->diffInMinutes($trial->trial_expires_at, false))) }} minutes remaining as of {{ now()->format('H:i T') }}. Refresh to update.</p>@endif
        <div class="account-actions"><a class="account-button secondary" href="{{ route('customer.guides') }}">Setup help</a>
            @if($trial->trial_package_id)<a class="account-button" href="{{ route('customer.trials.plan', $trial->id) }}">Continue with paid plan</a>@else<a class="account-button" href="{{ route('packages') }}">Choose a paid plan</a>@endif
        </div>
    </article>
    @endforeach
    @if($trialPackages->isNotEmpty())
    <details class="account-card"><summary>Request a trial</summary>
        <form method="post" action="{{ route('customer.trials.request') }}" style="margin-top:18px">@csrf
            <input type="hidden" name="submission_id" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
            <div class="account-field"><label for="trial-package">Available trial plan</label><select id="trial-package" name="package_id" required>
                <option value="">Choose a plan</option>@foreach($trialPackages as $package)<option value="{{ $package->id }}">{{ $package->vendor }} · {{ $package->title }} · {{ $package->free_trial_hours }} hours</option>@endforeach
            </select></div>
            <p class="account-muted">Support will confirm availability and activation. This does not start a payment or auto-renewal.</p>
            <button type="submit" class="account-button">Request trial</button>
        </form>
    </details>
    @elseif($trials->isEmpty())<p class="account-muted">No verified trials are linked to this account. <a href="{{ route('customer.support.index') }}">Ask support about trial availability.</a></p>@endif
</section>
<section class="account-section account-grid">
    <div class="account-card"><h2>Make setup simple</h2><p class="account-muted">Choose an optional guide for your device. Device selection is never required to complete checkout.</p><a class="account-button secondary" href="{{ route('customer.guides') }}">Explore setup guides</a></div>
    <div class="account-card"><h2>Expiry reminders</h2>
        <form method="post" action="{{ route('customer.preferences') }}">@csrf @method('PATCH')
            <input type="hidden" name="renewal_email_enabled" value="0">
            <label class="account-check"><input type="checkbox" name="renewal_email_enabled" value="1" @checked($customer->wantsRenewalEmail())><span>Email me before my service expires.</span></label>
            <p class="account-muted">Reminders 7 days and 1 day before expiry. This preference does not subscribe you to promotions. Renewals always need your approval.</p>
            <button class="account-button secondary" type="submit">Save preference</button>
        </form>
    </div>
</section>
@endsection
