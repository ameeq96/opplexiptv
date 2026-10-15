@extends('pages.account.layout')
@section('title', 'Order #'.$order->id)
@section('content')
<a class="account-link" href="{{ route('customer.account') }}">← Back to overview</a>
<p class="account-eyebrow">Order #{{ $order->id }}</p><h1>{{ $order->package ?: 'Your service' }}</h1>
<p class="account-muted account-lead">Payment, activation and documents for this order.</p>
<section class="account-card" aria-labelledby="activation-title">
    <div class="account-row"><h2 id="activation-title">Service progress</h2><span class="account-badge {{ $state['tone'] }}">{{ $state['label'] }}</span></div>
    <ol class="account-timeline" aria-label="Order progress">
        <li class="{{ $state['step'] >= 1 ? 'complete' : '' }}">Payment received</li>
        <li class="{{ $state['step'] >= 2 ? 'complete' : '' }}" @if($state['step'] === 2) aria-current="step" @endif>Activation pending</li>
        <li class="{{ $state['step'] >= 3 ? 'complete' : '' }}" @if($state['step'] === 3) aria-current="step" @endif>{{ $state['label'] === 'Service expired' ? 'Service expired' : 'Service active' }}</li>
    </ol>
    @if($state['step'] === 2)<p class="account-muted">Your payment is confirmed. Support still needs to activate your service. A payment screenshot alone does not mark a service active.</p>@endif
    @if($order->payment_status === 'test_paid')<p class="account-notice">This is a test payment, not a confirmed live purchase.</p>@endif
    @if($state['step'] === 0 && $order->payment_status !== 'test_paid')<p class="account-muted">If your bank shows a debit, contact support before trying another payment.</p>@endif
    <dl class="account-meta">
        <div><dt>Start date</dt><dd>{{ $order->buying_date?->format('d M Y') ?: 'Not recorded' }}</dd></div>
        <div><dt>End date</dt><dd>{{ $order->expiry_date?->format('d M Y') ?: 'Not recorded' }}</dd></div>
        <div><dt>Payment confirmed</dt><dd>{{ $order->payment_status === 'paid' ? ($order->paid_at?->format('d M Y, H:i T') ?: 'Confirmed') : 'Not confirmed' }}</dd></div>
        <div><dt>Recorded paid amount</dt><dd>{{ $order->payment_status === 'paid' && $order->paid_amount !== null ? ($order->paid_currency ?: $order->currency).' '.number_format((float) $order->paid_amount, 2) : 'Not recorded' }}</dd></div>
    </dl>
    <p class="account-muted">Dates are taken from your existing service record. Viewing or renewing here does not change them automatically.</p>
</section>
<div class="account-grid">
    <section class="account-card"><h2>Your invoice</h2>
        @if($invoiceUrl)<p class="account-muted">Download the original invoice from the payment service. You can save and share the PDF.</p><a class="account-button" href="{{ $invoiceUrl }}" rel="noopener noreferrer" referrerpolicy="no-referrer" target="_blank" aria-label="Download invoice (opens in a new tab)">Download invoice ↗</a>
        @else<p class="account-muted">An original paid invoice is not linked or available in this environment yet. We do not generate a paid invoice from an unverified or test payment.</p><a class="account-button secondary" href="{{ route('customer.support.index') }}">Ask about my invoice</a>@endif
    </section>
    <section class="account-card"><h2>Ready for your next renewal?</h2><p class="account-muted">Review the current plan and final price before paying. No automatic charge, and no changes to this order.</p>
        @if($renewable)<a class="account-button" href="{{ route('customer.orders.renew', $order->id) }}">Review renewal</a>@else<p class="account-muted">This original plan cannot currently be renewed directly.</p><a class="account-button secondary" href="{{ route('packages') }}">Choose a plan</a>@endif
    </section>
</div>
<div class="account-section account-actions"><a class="account-button secondary" href="{{ route('customer.guides') }}">Setup instructions</a><a class="account-button secondary" href="{{ route('customer.support.index') }}">Contact support</a></div>
@endsection
