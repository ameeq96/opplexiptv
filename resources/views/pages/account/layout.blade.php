<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <meta name="referrer" content="no-referrer">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'My Account') · Opplex</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        :root { color-scheme: light; --ink:#202b40; --muted:#647087; --line:#e5e8ef; --brand:#5658d9; --soft:#ededfb; }
        * { box-sizing:border-box; }
        body { margin:0; background:#f4f6fa; color:var(--ink); font:500 14px/1.65 Manrope,system-ui,-apple-system,"Segoe UI",sans-serif; -webkit-font-smoothing:antialiased; }
        a { color:#5052c5; text-underline-offset:3px; }
        button,input,select,textarea { font:inherit; }
        :focus-visible { outline:3px solid #9c9eef; outline-offset:3px; }
        [hidden] { display:none !important; }
        .account-skip { position:fixed; top:-80px; left:16px; z-index:100; background:#fff; padding:12px; }
        .account-skip:focus { top:12px; }
        .account-header { position:sticky; top:0; z-index:20; background:#f4f6fa; border-bottom:1px solid var(--line); }
        .account-header-inner { min-height:92px; padding:18px 0; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .account-header-title { font-size:19px; font-weight:800; letter-spacing:-.04em; }
        .account-header-label { display:block; color:var(--muted); font-size:12px; margin-top:2px; }
        .account-header-actions { display:flex; align-items:center; gap:14px; }
        .account-environment { display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border:1px solid #eadfc7; border-radius:6px; background:#fffaf0; font-size:11px; color:#80601e; white-space:nowrap; }
        .account-environment::before { content:''; width:6px; height:6px; border-radius:50%; background:#c59c49; }
        .account-brand { display:block; max-width:190px; padding:0; background:transparent; line-height:0; }
        .account-brand img { display:block; width:100%; height:auto; object-fit:contain; }
        .account-brand-mobile { display:none; width:156px; padding:0; background:transparent; }
        .account-shell { display:grid; grid-template-columns:246px minmax(0,1fr); align-items:start; min-height:100vh; }
        .account-workspace { min-width:0; padding:0 32px 28px; }
        .account-sidebar { position:sticky; top:0; display:flex; flex-direction:column; height:100vh; height:100dvh; padding:27px 12px 20px; background:#111d32; color:#dae1f0; }
        .account-sidebar-brand { margin:0 10px 28px; }
        .account-sidebar-heading { margin:0 14px 10px; color:#9cabc3; font-size:10px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
        .account-sidebar nav { display:grid; gap:5px; }
        .account-nav-link { display:flex; align-items:center; gap:12px; min-height:46px; border-radius:9px; padding:11px 14px; color:#c5cfe0; font-size:13px; font-weight:600; text-decoration:none; }
        .account-nav-link svg { width:19px; height:19px; flex-shrink:0; color:#93a4c3; }
        .account-nav-link:hover { background:#1e2b42; color:#fff; }
        .account-nav-link[aria-current="page"] { background:var(--brand); color:#fff; box-shadow:inset 3px 0 0 #b9baff; }
        .account-nav-link[aria-current="page"] svg { color:#fff; }
        .account-sidebar-note { margin:auto 10px 0; padding:20px 4px 0; border-top:1px solid #ffffff12; font-size:12px; color:#9cabc3; }
        .account-sidebar-note strong { display:block; margin-bottom:5px; color:#e5eaf3; font-size:13px; }
        main { min-width:0; }
        #account-main { padding-top:28px; max-width:1400px; margin:auto; }
        #account-main:focus { outline:none; }
        h1 { margin:4px 0 8px; font-size:clamp(27px,3vw,36px); font-weight:800; line-height:1.25; letter-spacing:-.04em; }
        h2 { margin:0 0 12px; font-size:21px; line-height:1.4; }
        h3 { margin:0 0 8px; font-size:17px; }
        p { margin:0 0 16px; }
        .account-eyebrow { margin:0 0 8px; color:var(--brand); font-size:12px; font-weight:800; letter-spacing:.13em; text-transform:uppercase; }
        .account-muted { color:var(--muted); }
        .account-lead { margin-bottom:28px; max-width:660px; }
        .account-card { background:#fff; border:1px solid var(--line); border-radius:14px; padding:24px; margin-bottom:20px; box-shadow:0 4px 20px rgba(27,39,63,.045); overflow-wrap:anywhere; }
        .account-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
        .account-grid .account-card { margin-bottom:0; }
        .account-grid > .account-card:not(details) { display:flex; flex-direction:column; }
        .account-grid > .account-card > .account-button:last-child { margin-top:auto; align-self:flex-start; }
        .account-section.account-grid > .account-card > form { display:flex; flex-direction:column; flex:1; }
        .account-section.account-grid > .account-card > form > .account-button:last-child { margin-top:auto; align-self:flex-start; }
        .account-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin-bottom:28px; }
        .account-stat { position:relative; padding:22px; border:1px solid var(--line); border-radius:14px; background:#fff; box-shadow:0 4px 20px rgba(27,39,63,.045); }
        .account-stat::before { content:''; display:block; width:30px; height:5px; background:var(--brand); border-radius:4px; margin-bottom:18px; }
        .account-stat:nth-child(2)::before { background:#ab741b; }
        .account-stat:nth-child(3)::before { background:#19806a; }
        .account-stat strong { display:block; font-size:32px; font-weight:800; line-height:1.3; letter-spacing:-.04em; }
        .account-stat span { display:block; margin-top:6px; color:var(--muted); font-size:12px; }
        .account-section { margin-top:32px; }
        .account-row { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
        .account-actions { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,180px),1fr)); grid-auto-rows:1fr; align-items:stretch; gap:10px; }
        .account-actions > .account-button { min-width:0; }
        .account-button { display:inline-flex; justify-content:center; align-items:center; min-height:46px; max-width:100%; padding:10px 18px; border:1px solid var(--brand); border-radius:10px; background:var(--brand); color:#fff; font-weight:700; text-decoration:none; text-align:center; cursor:pointer; line-height:1.4; }
        .account-button:hover { background:#4547ba; color:#fff; }
        .account-button.secondary { color:var(--ink); background:#fff; border-color:#c7d6dc; }
        .account-button.secondary:hover { background:#edf3f5; }
        .account-button:disabled { opacity:.65; cursor:wait; }
        .account-link { display:inline-flex; align-items:center; min-height:44px; padding:4px; }
        .account-badge { display:inline-block; padding:5px 10px; border-radius:8px; background:#edf2f5; color:#435965; font-size:12px; font-weight:750; }
        .account-badge.success { background:#dcf3e9; color:#135c42; }
        .account-badge.warning { background:#fff0d4; color:#7c4a08; }
        .account-meta { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; margin:20px 0; }
        .account-meta dt { color:var(--muted); font-size:13px; }
        .account-meta dd { margin:3px 0 0; font-weight:650; }
        .account-field { display:block; margin-bottom:18px; }
        .account-field label,.account-field > span { display:block; margin-bottom:6px; font-weight:650; }
        .account-field input:not([type="checkbox"]),.account-field select,.account-field textarea { display:block; width:100%; min-height:48px; padding:10px 12px; color:var(--ink); border:1px solid #adbec7; border-radius:9px; background:#fff; }
        textarea { resize:vertical; }
        .account-field small { display:block; margin-top:6px; color:var(--muted); }
        .select2-container { max-width:100%; font-size:14px; }
        .select2-container--default .select2-selection--single { min-height:48px; border:1px solid #adbec7; border-radius:9px; background:#fff; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { padding:9px 34px 9px 12px; line-height:28px; color:var(--ink); }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height:46px; right:8px; }
        .select2-container--default .select2-selection--multiple { min-height:48px; border:1px solid #adbec7; border-radius:9px; padding:4px 8px; }
        .select2-container--default .select2-selection--multiple .select2-selection__choice { background:var(--soft); border-color:#d6d6ee; color:#5355a5; }
        .select2-container--default.select2-container--open .select2-selection, .select2-container--default.select2-container--focus .select2-selection { border-color:#8b8ce4; box-shadow:0 0 0 3px #5658d914; }
        .select2-dropdown { border-color:var(--line); border-radius:9px; overflow:hidden; box-shadow:0 10px 30px #202b4014; }
        .select2-search--dropdown { padding:8px; }
        .select2-container--default .select2-search--dropdown .select2-search__field { border:1px solid #dfe4ed; border-radius:6px; padding:9px 10px; font:inherit; }
        .select2-results__option { padding:10px 12px; }
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable { background:var(--brand); color:#fff; }
        .select2-selection[aria-invalid="true"] { border-color:#c44555; }
        .account-check { display:flex; align-items:flex-start; gap:12px; margin-bottom:16px; cursor:pointer; }
        .account-check input { width:20px; height:20px; margin-top:4px; flex-shrink:0; accent-color:var(--brand); }
        .account-notice { padding:15px 18px; margin-bottom:20px; border-radius:12px; border:1px solid #acd8c8; background:#eaf7f1; color:#174f3a; }
        .account-notice.error { border-color:#e5b3b3; background:#fff0f0; color:#8b2525; }
        .account-notice ul { margin:0; padding-left:20px; }
        .account-empty { padding:22px 24px; }
        .account-empty p { max-width:520px; }
        .account-timeline { padding:0; margin:24px 0; list-style:none; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; counter-reset:step; }
        .account-timeline li { border-top:3px solid var(--line); padding-top:12px; color:var(--muted); font-size:14px; }
        .account-timeline li::before { counter-increment:step; content:counter(step); display:block; font-weight:800; margin-bottom:5px; }
        .account-timeline .complete { border-color:var(--brand); color:var(--ink); }
        .account-timeline [aria-current="step"] { font-weight:750; }
        .account-auth { max-width:540px; margin:20px auto; }
        .account-footer { margin-top:32px; padding-top:22px; border-top:1px solid var(--line); font-size:11px; color:var(--muted); }
        .account-pagination { display:flex; gap:12px; justify-content:space-between; margin-top:20px; }
        @media(max-width:760px) {
            .account-header-inner { min-height:76px; padding:14px 0; gap:10px; }
            .account-header-title, .account-header-label, .account-environment { display:none; }
            .account-header-actions { gap:8px; }
            .account-header-actions .account-button { padding:9px 12px; font-size:12px; }
            .account-brand-mobile { display:block; }
            .account-shell { display:flex; flex-direction:column; }
            .account-workspace { width:100%; padding:0 16px 32px; }
            .account-sidebar { position:sticky; top:auto; bottom:0; z-index:25; width:100%; height:auto; padding:8px 10px calc(8px + env(safe-area-inset-bottom,0px)); order:2; margin-top:auto; border-top:1px solid #ffffff14; }
            .account-sidebar nav { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:4px; }
            .account-nav-link { flex-direction:column; justify-content:center; text-align:center; padding:8px 4px; font-size:10px; gap:4px; }
            .account-sidebar-brand, .account-sidebar-heading, .account-sidebar-note { display:none; }
            #account-main { padding-top:22px; }
            .account-card { padding:20px; }
            .account-grid { grid-template-columns:1fr; }
            .account-stats { gap:8px; }
            .account-stat { padding:14px 10px; }
            .account-stat strong { font-size:25px; }
            .account-stat span { font-size:12px; }
            .account-stat::before { margin-bottom:12px; width:24px; }
        }
        @media(prefers-reduced-motion:reduce) { *,*::before,*::after { scroll-behavior:auto !important; transition:none !important; animation:none !important; } }
    </style>
    @stack('styles')
</head>
<body>
<a class="account-skip" href="#account-main">Skip to account content</a>
<div class="account-shell">
    <aside class="account-sidebar">
        <div class="account-sidebar-brand"><a class="account-brand" href="{{ route('home') }}"><img src="{{ asset('images/opplexiptvlogo.webp') }}" alt="Opplex IPTV" width="250" height="65"></a></div>
        <p class="account-sidebar-heading">Customer workspace</p>
        <nav aria-label="Customer navigation">
            <a class="account-nav-link" href="{{ route('customer.account') }}" @if(request()->routeIs('customer.account','customer.orders.*')) aria-current="page" @endif><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z"/></svg><span>My overview</span></a>
            <a class="account-nav-link" href="{{ route('customer.support.index') }}" @if(request()->routeIs('customer.support.*')) aria-current="page" @endif><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 16a2 2 0 0 1-2 2H8l-5 3V5a2 2 0 0 1 2-2h13a2 2 0 0 1 2 2Z"/><path d="M7 8h9M7 12h6"/></svg><span>Support requests</span></a>
            <a class="account-nav-link" href="{{ route('customer.guides') }}" @if(request()->routeIs('customer.guides')) aria-current="page" @endif><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 5v16M3 4h5a4 4 0 0 1 4 2 4 4 0 0 1 4-2h5v15h-5a4 4 0 0 0-4 2 4 4 0 0 0-4-2H3Z"/></svg><span>Setup guides</span></a>
        </nav>
        <p class="account-sidebar-note"><strong>Your customer space</strong>Your service, your updates.<br>Everything in one place.</p>
    </aside>
    <div class="account-workspace">
    <header class="account-header"><div class="account-header-inner">
        <div><a class="account-brand account-brand-mobile" href="{{ route('home') }}"><img src="{{ asset('images/opplexiptvlogo.webp') }}" alt="Opplex IPTV" width="250" height="65"></a><div class="account-header-title">@yield('title', 'My Account')</div><span class="account-header-label">Customer portal / Your workspace</span></div>
        <div class="account-header-actions">
            @if(app()->environment('local'))<span class="account-environment">Local preview</span>@endif
            <form method="post" action="{{ route('api.browser.customer.logout') }}" data-api-form id="accountSignOut" hidden>@csrf<button class="account-button secondary" type="submit">Sign out</button></form>
            <a class="account-link" href="{{ route('home') }}" id="accountBackHome">Back to website</a>
        </div>
    </div></header>
    <main id="account-main" tabindex="-1">
        @if($errors->any())<div class="account-notice error" role="alert" tabindex="-1" data-account-errors><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @if(session('status') || session('success'))<div class="account-notice" role="status">{{ session('status') ?: session('success') }}</div>@endif
        <div class="account-notice error" id="accountValidationNotice" role="alert" hidden></div>
        @yield('content')
        <footer class="account-footer">Payment confirmation and service activation are separate. We never charge you automatically from this account.</footer>
    </main>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/api-browser.js') }}" defer></script>
<script>
    window.AccountPortal = {
        escape(value) { return String(value ?? '').replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[character])); },
        date(value, time = false) { if (!value) return 'Not recorded'; const date = new Date(value.length === 10 ? `${value}T12:00:00` : value); return Number.isNaN(date.getTime()) ? 'Not recorded' : date.toLocaleString('en-GB', time ? {dateStyle:'medium',timeStyle:'short'} : {dateStyle:'medium'}); },
        href(value) { try { const url = new URL(value, location.origin), local = ['127.0.0.1','localhost','[::1]']; if (url.username || url.password) return '#'; return url.origin === location.origin || (url.protocol === 'https:' && ['opplexify.com','www.opplexify.com'].includes(url.hostname)) || (local.includes(location.hostname) && local.includes(url.hostname) && ['http:','https:'].includes(url.protocol)) ? this.escape(url.href) : '#'; } catch (_) { return '#'; } },
        error(error) { const notice = document.getElementById('accountValidationNotice'); notice.textContent = error.status === 401 ? 'Your session has expired. Please sign in again using your email. ' : (error.body?.message || error.message || 'Unable to load this information. Please try again.'); if (error.status === 401) { const link = document.createElement('a'); link.href = @json(route('customer.login')); link.textContent = 'Sign in'; notice.appendChild(link); } notice.hidden = false; notice.setAttribute('tabindex','-1'); notice.focus(); },
        async load(url) { return window.OpplexApi.request(url); },
        pagination(meta, action) { if (meta.last_page <= 1) return ''; return `<nav class="account-pagination" aria-label="Pages">${meta.current_page > 1 ? `<button class="account-button secondary" data-account-page="${action}" data-page="${meta.current_page - 1}">Previous</button>` : '<span></span>'}<span>Page ${meta.current_page} of ${meta.last_page}</span>${meta.current_page < meta.last_page ? `<button class="account-button secondary" data-account-page="${action}" data-page="${meta.current_page + 1}">Next</button>` : '<span></span>'}</nav>`; }
    };
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const response = await AccountPortal.load(@json(route('api.browser.customer.session')));
            document.getElementById('accountSignOut').hidden = !response.data.authenticated;
            document.getElementById('accountBackHome').hidden = response.data.authenticated;
        } catch (error) { AccountPortal.error(error); }
    });
    (() => {
        if (window.jQuery?.fn?.select2) {
            jQuery('select').each(function () {
                const field = this;
                const $field = jQuery(field);
                if ($field.data('select2')) return;
                const blank = Array.from(field.options).find(option => option.value === '');
                $field.select2({ width: '100%', placeholder: $field.data('placeholder') || (blank ? blank.text : undefined), allowClear: !!blank && !field.required, dropdownParent: jQuery('body') });
                const selection = $field.next('.select2-container').find('.select2-selection');
                const label = field.labels?.[0];
                if (label && field.id) {
                    if (!label.id) label.id = `${field.id}-account-label`;
                    selection.attr('aria-labelledby', `${label.id} ${selection.attr('aria-labelledby') || ''}`.trim());
                } else if (field.getAttribute('aria-label')) selection.attr('aria-label', field.getAttribute('aria-label')).removeAttr('aria-labelledby');
                if (field.required) selection.attr('aria-required', 'true');
                $field.on('change.accountSelect', function (event) {
                    if (event.isTrigger && !event.originalEvent && !this.onchange) this.dispatchEvent(new Event('change', { bubbles: true }));
                    if (field.validity.valid) {
                        selection.removeAttr('aria-invalid');
                        document.getElementById('accountValidationNotice').hidden = true;
                    }
                });
            });
        }
        document.addEventListener('invalid', event => {
            const field = event.target;
            if (!field.matches('select.select2-hidden-accessible')) return;
            event.preventDefault();
            const details = field.closest('details');
            if (details) details.open = true;
            const selection = field.nextElementSibling?.querySelector('.select2-selection');
            selection?.setAttribute('aria-invalid', 'true');
            selection?.focus();
            const notice = document.getElementById('accountValidationNotice');
            notice.textContent = `${field.labels?.[0]?.textContent.trim() || 'Selection'}: ${field.validationMessage}`;
            notice.hidden = false;
        }, true);
        document.querySelector('[data-account-errors]')?.focus();
        document.querySelectorAll('form:not([data-api-form])').forEach(form => form.addEventListener('submit', event => {
            if (form.dataset.submitting === '1') { event.preventDefault(); return; }
            if (!form.checkValidity()) return;
            form.dataset.submitting = '1';
            form.setAttribute('aria-busy', 'true');
            const button = event.submitter;
            if (button) { button.dataset.originalLabel = button.textContent; button.disabled = true; button.textContent = 'Please wait…'; }
        }));
        window.addEventListener('pageshow', () => {
            document.querySelectorAll('form[data-submitting]').forEach(form => { delete form.dataset.submitting; form.removeAttribute('aria-busy'); });
            document.querySelectorAll('button[data-original-label]').forEach(button => { button.disabled = false; button.textContent = button.dataset.originalLabel; delete button.dataset.originalLabel; });
        });
    })();
</script>
@stack('scripts')
</body>
</html>
