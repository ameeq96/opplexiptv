@extends('pages.account.layout')
@section('title', 'Order #'.$orderId)
@section('content')
<a class="account-link" href="{{ route('customer.account') }}">← Back to overview</a>
<p class="account-eyebrow">Order #{{ $orderId }}</p><h1 id="orderTitle">Your service</h1>
<p class="account-muted account-lead">Payment, activation and documents for this order.</p>
<div id="orderDetails" aria-busy="true"><p role="status">Loading your order…</p></div>
<div class="account-section account-actions"><a class="account-button secondary" href="{{ route('customer.guides') }}">Setup instructions</a><a class="account-button secondary" href="{{ route('customer.support.index') }}">Contact support</a></div>
<noscript><p>Please enable JavaScript to securely load your order data.</p></noscript>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const api = AccountPortal, text = api.escape, target = document.getElementById('orderDetails');
    const supportUrl = @json(route('customer.support.index')), packagesUrl = @json(route('packages'));
    async function load() {
        target.setAttribute('aria-busy','true');
        try {
            const order = (await api.load(@json(route('api.browser.customer.orders.show', $orderId)))).data;
            document.getElementById('orderTitle').textContent = order.package || 'Your service';
            const state = order.state, tone = ['success','warning','neutral'].includes(state.tone) ? state.tone : 'neutral';
            const steps = ['Payment received','Activation pending',state.label === 'Service expired' ? 'Service expired' : 'Service active'];
            const timeline = steps.map((label,index) => '<li class="' + (state.step >= index + 1 ? 'complete' : '') + '"' + (state.step === index + 1 ? ' aria-current="step"' : '') + '>' + label + '</li>').join('');
            let note = state.step === 2 ? '<p class="account-muted">Your payment is confirmed. Support still needs to activate your service. A payment screenshot alone does not mark a service active.</p>' : '';
            if (order.payment_status === 'test_paid') note += '<p class="account-notice">This is a test payment, not a confirmed live purchase.</p>';
            else if (state.step === 0) note += '<p class="account-muted">If your bank shows a debit, contact support before trying another payment.</p>';
            const amount = order.payment_status === 'paid' && order.paid_amount !== null ? text(order.paid_currency) + ' ' + text(Number(order.paid_amount).toFixed(2)) : 'Not recorded';
            target.innerHTML = '<section class="account-card" aria-labelledby="activation-title"><div class="account-row"><h2 id="activation-title">Service progress</h2><span class="account-badge ' + tone + '">' + text(state.label) + '</span></div><ol class="account-timeline" aria-label="Order progress">' + timeline + '</ol>' + note + '<dl class="account-meta"><div><dt>Start date</dt><dd>' + text(api.date(order.buying_date)) + '</dd></div><div><dt>End date</dt><dd>' + text(api.date(order.expiry_date)) + '</dd></div><div><dt>Payment confirmed</dt><dd>' + (order.payment_status === 'paid' ? (order.paid_at ? text(api.date(order.paid_at,true)) : 'Confirmed') : 'Not confirmed') + '</dd></div><div><dt>Recorded paid amount</dt><dd>' + amount + '</dd></div></dl><p class="account-muted">Dates are taken from your existing service record. Viewing or renewing here does not change them automatically.</p></section><div class="account-grid"><section class="account-card"><h2>Your invoice</h2>' + (order.invoice_url ? '<p class="account-muted">Download the original invoice from the payment service. You can save and share the PDF.</p><a class="account-button" href="' + api.href(order.invoice_url) + '" rel="noopener noreferrer" referrerpolicy="no-referrer" target="_blank" aria-label="Download invoice (opens in a new tab)">Download invoice ↗</a>' : '<p class="account-muted">An original paid invoice is not linked or available in this environment yet. We do not generate a paid invoice from an unverified or test payment.</p><a class="account-button secondary" href="' + api.href(supportUrl) + '">Ask about my invoice</a>') + '</section><section class="account-card"><h2>Ready for your next renewal?</h2><p class="account-muted">Review the current plan and final price before paying. No automatic charge, and no changes to this order.</p>' + (order.renewable ? '<form method="post" action="' + api.href(order.renew_url) + '" data-api-form><button class="account-button" type="submit">Review renewal</button></form>' : '<p class="account-muted">This original plan cannot currently be renewed directly.</p><a class="account-button secondary" href="' + api.href(packagesUrl) + '">Choose a plan</a>') + '</section></div>';
        } catch (error) { target.innerHTML = '<p>Order details are unavailable.</p><button class="account-button secondary" type="button" id="retryOrder">Try again</button>'; api.error(error); }
        finally { target.setAttribute('aria-busy','false'); }
    }
    target.addEventListener('click', event => { if (event.target.id === 'retryOrder') load(); });
    load();
});
</script>
@endpush
