@extends('pages.account.layout')
@section('title', 'My overview')
@section('content')
<p class="account-eyebrow">My overview</p>
<h1>Your services, at a glance.</h1>
<p class="account-muted account-lead" id="accountIdentity">Loading your account…</p>
<div class="account-stats" aria-label="Account summary">
    <div class="account-stat"><strong id="activeCount">—</strong><span>Active services</span></div>
    <div class="account-stat"><strong id="pendingCount">—</strong><span>Awaiting activation</span></div>
    <div class="account-stat"><strong id="openTickets">—</strong><span>Open support requests</span></div>
</div>
<section aria-labelledby="services-title">
    <div class="account-row"><h2 id="services-title">Your orders & services</h2><a class="account-link" href="{{ route('packages') }}">Browse plans</a></div>
    <div id="accountOrders" aria-busy="true"><p role="status">Loading your orders…</p></div>
</section>
<section class="account-section" aria-labelledby="trials-title">
    <h2 id="trials-title">Your trials</h2>
    <p class="account-muted">A request is not an activated trial. Actual dates appear when support confirms your service.</p>
    <div id="accountTrials" aria-busy="true"><p role="status">Loading your trials…</p></div>
    <details class="account-card" id="trialRequest" hidden><summary>Request a trial</summary>
        <form method="post" action="{{ route('api.browser.customer.trials.request') }}" data-api-form id="trialRequestForm" style="margin-top:18px">@csrf
            <input type="hidden" name="submission_id" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
            <div class="account-field"><label for="trial-package">Available trial plan</label><select id="trial-package" name="package_id" required><option value="">Choose a plan</option></select></div>
            <p class="account-muted">Support will confirm availability and activation. This does not start a payment or auto-renewal.</p>
            <button type="submit" class="account-button">Request trial</button>
        </form>
    </details>
</section>
<section class="account-section account-grid">
    <div class="account-card"><h2>Make setup simple</h2><p class="account-muted">Choose an optional guide for your device. Device selection is never required to complete checkout.</p><a class="account-button secondary" href="{{ route('customer.guides') }}">Explore setup guides</a></div>
    <div class="account-card"><h2>Expiry reminders</h2>
        <form method="post" action="{{ route('api.browser.customer.preferences') }}" data-api-form>@csrf @method('PATCH')
            <input type="hidden" name="renewal_email_enabled" value="0">
            <label class="account-check"><input type="checkbox" name="renewal_email_enabled" id="renewalPreference" value="1" disabled><span>Email me before my service expires.</span></label>
            <p class="account-muted">Reminders 7 days and 1 day before expiry. This preference does not subscribe you to promotions. Renewals always need your approval.</p>
            <button class="account-button secondary" type="submit" id="savePreference" disabled>Save preference</button>
        </form>
    </div>
</section>
<noscript><p>Please enable JavaScript to securely load your account data.</p></noscript>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const api = AccountPortal, text = api.escape;
    const supportUrl = @json(route('customer.support.index')), guidesUrl = @json(route('customer.guides')), packagesUrl = @json(route('packages'));
    async function loadOverview() {
        try {
            const data = (await api.load(@json(route('api.browser.customer.overview')))).data;
            document.getElementById('accountIdentity').textContent = 'Signed in as ' + data.customer.email + '. Track activation, get help and manage your next renewal.';
            ['activeCount','pendingCount','openTickets'].forEach((id,index) => document.getElementById(id).textContent = [data.active_count,data.pending_count,data.open_tickets][index]);
            document.getElementById('renewalPreference').checked = data.customer.renewal_email_enabled;
            document.getElementById('renewalPreference').disabled = false;
            document.getElementById('savePreference').disabled = false;
        } catch (error) { api.error(error); }
    }
    async function loadOrders(page = 1) {
        const target = document.getElementById('accountOrders'); target.setAttribute('aria-busy','true');
        try {
            const response = await api.load(@json(route('api.browser.customer.orders.index')) + '?page=' + page);
            target.innerHTML = '<div class="account-grid">' + (response.data.length ? response.data.map(order => {
                const tone = ['success','warning','neutral'].includes(order.state.tone) ? order.state.tone : 'neutral';
                const payment = order.payment_status === 'paid' ? 'Confirmed' : order.payment_status === 'test_paid' ? 'Test only' : 'Not confirmed';
                return '<article class="account-card"><div class="account-row"><p class="account-eyebrow">Order #' + text(order.id) + '</p><span class="account-badge ' + tone + '">' + text(order.state.label) + '</span></div><h3>' + text(order.package || 'Your service') + '</h3><dl class="account-meta"><div><dt>Start date</dt><dd>' + text(api.date(order.buying_date)) + '</dd></div><div><dt>End date</dt><dd>' + text(api.date(order.expiry_date)) + '</dd></div><div><dt>Payment</dt><dd>' + payment + '</dd></div><div><dt>Remaining</dt><dd>' + (order.remaining_days === null ? '—' : text(order.remaining_days) + ' days') + '</dd></div></dl><a class="account-button secondary" href="' + api.href(order.url) + '">View order & invoice</a></article>';
            }).join('') : '<div class="account-card account-empty"><h3>No orders linked yet</h3><p class="account-muted">Use the same email when paying. If an older order is missing, support can verify and link it safely.</p><a class="account-button secondary" href="' + api.href(supportUrl) + '">Get help with an order</a></div>') + '</div>' + api.pagination(response.meta,'orders');
        } catch (error) { target.innerHTML = '<button type="button" class="account-button secondary" data-account-page="orders" data-page="' + page + '">Retry loading orders</button>'; api.error(error); }
        finally { target.setAttribute('aria-busy','false'); }
    }
    async function loadTrials() {
        const target = document.getElementById('accountTrials');
        try {
            const response = await api.load(@json(route('api.browser.customer.trials.index')));
            target.innerHTML = response.data.length ? response.data.map(trial => '<article class="account-card"><div class="account-row"><h3>' + text(trial.package_name || 'Trial request') + '</h3><span class="account-badge ' + (trial.status === 'active' ? 'success' : 'neutral') + '">' + text(trial.status === 'pending' ? 'Awaiting activation' : trial.status) + '</span></div><p class="account-muted">Reference ' + text(trial.reference) + '</p><dl class="account-meta"><div><dt>Starts</dt><dd>' + text(trial.started_at ? api.date(trial.started_at,true) : 'Not activated yet') + '</dd></div><div><dt>Ends</dt><dd>' + text(trial.expires_at ? api.date(trial.expires_at,true) : 'Not set yet') + '</dd></div></dl>' + (trial.remaining_minutes === null ? '' : '<p role="status">' + text(trial.remaining_minutes) + ' minutes remaining. Refresh to update.</p>') + '<div class="account-actions"><a class="account-button secondary" href="' + api.href(guidesUrl) + '">Setup help</a>' + (trial.plan_url ? '<form method="post" action="' + api.href(trial.plan_url) + '" data-api-form><button class="account-button" type="submit">Continue with paid plan</button></form>' : '<a class="account-button" href="' + api.href(packagesUrl) + '">Choose a paid plan</a>') + '</div></article>').join('') : '<p class="account-muted">No verified trials are linked to this account. <a href="' + api.href(supportUrl) + '">Ask support about trial availability.</a></p>';
        } catch (error) { target.textContent = 'Trials could not be loaded. Refresh to try again.'; api.error(error); }
        finally { target.setAttribute('aria-busy','false'); }
    }
    async function loadPackages() {
        try {
            const response = await api.load(@json(route('api.browser.customer.trial-packages')));
            const select = document.getElementById('trial-package');
            response.data.forEach(plan => select.add(new Option(plan.vendor + ' · ' + plan.title + ' · ' + plan.free_trial_hours + ' hours',plan.id)));
            document.getElementById('trialRequest').hidden = !response.data.length;
            if (window.jQuery) jQuery(select).trigger('change.select2');
        } catch (error) { api.error(error); }
    }
    document.getElementById('accountOrders').addEventListener('click', event => { const button = event.target.closest('[data-account-page="orders"]'); if (button) loadOrders(Number(button.dataset.page)); });
    document.getElementById('trialRequestForm').addEventListener('data-api-success', event => {
        if (window.crypto?.randomUUID) event.target.querySelector('[name="submission_id"]').value = crypto.randomUUID();
        else { location.reload(); return; }
        document.getElementById('trial-package').value = '';
        if (window.jQuery) jQuery('#trial-package').trigger('change.select2');
        loadTrials();
    });
    loadOverview(); loadOrders(Number(new URL(location.href).searchParams.get('page')) || 1); loadTrials(); loadPackages();
});
</script>
@endpush
