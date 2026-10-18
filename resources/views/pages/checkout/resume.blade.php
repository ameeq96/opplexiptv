<!doctype html>
<html lang="{{ $locale }}" dir="{{ in_array($locale, ['ar', 'ur'], true) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive"><meta name="referrer" content="no-referrer">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ $ui['resumeHeading'] }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f3f5fb;color:#17213a;font:16px/1.6 system-ui,sans-serif;padding:32px 16px}main{max-width:660px;margin:24px auto;background:#fff;padding:clamp(22px,5vw,40px);border:1px solid #dfe4ee;border-radius:20px}h1{font-size:clamp(1.7rem,6vw,2.3rem);line-height:1.2}p{overflow-wrap:anywhere}.note{padding:14px;background:#f0efff;border-radius:10px}.warning{border-inline-start:4px solid #986300;padding:12px;background:#fff4d8}button,a{font:inherit}button{border:0;border-radius:10px;background:#5147d7;color:#fff;padding:14px 18px;cursor:pointer;width:100%;white-space:normal}button:disabled{opacity:.65;cursor:wait}a{color:#4238af}button:focus-visible,a:focus-visible{outline:3px solid #b16c00;outline-offset:4px}#result{min-height:1.6em}
    </style>
</head>
<body><main>
    <h1>{{ $ui['resumeHeading'] }}</h1>
    @if ($plan)
        <p>{{ $brand['name'] }} — {{ $plan['title'] ?? $plan['provider_name'] ?? $brand['name'] }}</p>
        <p class="note">{{ $ui['resumeCurrentPrice'] }}: <strong>{{ $plan['currency'] }} {{ number_format((float) $plan['price_amount'], 2) }}</strong></p>
        <p>{{ $ui['resumePlanOnly'] }}</p><p>{{ $ui['resumeFinalFees'] }}</p>
        @if ($priceChanged)<p class="warning">{{ $ui['resumePriceChanged'] }}</p>@endif
        <p class="warning">{{ $ui['resumePaymentWarning'] }}</p>
        <form id="resume-form" action="{{ $confirmUrl }}" method="post" data-amount="{{ (int) round((float) $plan['price_amount'] * 100) }}" data-currency="{{ $plan['currency'] }}">
            @csrf
            <button type="submit">{{ $ui['resumeContinue'] }}</button>
        </form>
        <p id="result" role="status" aria-live="polite"></p>
    @else
        <p role="alert">{{ $ui['resumeInvalidLink'] }}</p>
    @endif
    @if ($brand)<p><a href="https://{{ $brand['domain'] }}/{{ $locale === 'en' ? '' : $locale.'/' }}packages/" rel="noreferrer">{{ $ui['resumeReturn'] }}</a></p>@endif
</main>
@if ($plan)
<script>
(() => {
    const form = document.getElementById('resume-form');
    const button = form.querySelector('button');
    const result = document.getElementById('result');
    const failure = @json($ui['resumeFailed']);
    let pending = false;
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (pending) return;
        pending = true; button.disabled = true; result.textContent = @json($ui['resumeSaving']);
        try {
            const response = await fetch(form.action, {method: 'POST', credentials: 'same-origin', headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify({confirm_selection: true, amount_minor: Number(form.dataset.amount), currency: form.dataset.currency})});
            const payload = await response.json();
            if (!response.ok || typeof payload.redirect_url !== 'string') throw new Error(failure);
            const destination = new URL(payload.redirect_url, location.origin);
            if (destination.origin !== location.origin || destination.pathname !== '/checkout') throw new Error(failure);
            location.assign(destination.href);
        } catch { result.textContent = failure; pending = false; button.disabled = false; }
    });
})();
</script>
@endif
</body></html>
