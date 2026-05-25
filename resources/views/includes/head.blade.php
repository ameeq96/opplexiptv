<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="theme" content="Opplex IPTV UI Theme">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

@php
    use Illuminate\Support\Facades\Vite;
    use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

    if (!function_exists('v')) {
        function v(string $path) {
            $rel  = ltrim($path, '/');
            $full = public_path($rel);
            $ver  = is_file($full) ? filemtime($full) : time();
            return asset($rel) . '?v=' . $ver;
        }
    }

    $route = Request::route() ? Request::route()->getName() : 'home';
    $locale = app()->getLocale();
    $meta = trans("meta.$route");
    $metaTitle = $meta['title'] ?? 'Default Title';
    $metaDescription = $meta['description'] ?? 'Default Description';
    $keywords = $meta['keywords'] ?? '';

    $fbPixels = config('services.facebook.pixel_ids');
    if (empty($fbPixels) && config('services.facebook.pixel_id')) {
        $fbPixels = [config('services.facebook.pixel_id')];
    }
    if (empty($fbPixels)) {
        $fbPixels = ['1467807554407581'];
    }
    $currency = config('services.app.default_currency', 'USD');

    $routeName = optional(Request::route())->getName();
    $noindexRoutes = ['redirect.ad'];

    $pageParam = (int) request()->input('page', 1);
    $hasSearch = trim((string) request()->input('search', '')) !== '';
    $hasBlockedQuery =
        request()->has('page') ||
        request()->has('price') ||
        request()->has('category') ||
        request()->has('target');

    $shouldNoindex =
        in_array($routeName, $noindexRoutes, true) ||
        ($routeName === 'movies' && ($pageParam > 1 || $hasSearch)) ||
        $hasBlockedQuery;

    $default = LaravelLocalization::getDefaultLocale();
    $hideDefault = (bool) (config('laravellocalization.hideDefaultLocaleInURL') ?? false);

    $currentAbs = url()->current();

    if ($locale === $default && $hideDefault) {
        $canonical = LaravelLocalization::getNonLocalizedURL($currentAbs);
    } else {
        $canonical = LaravelLocalization::getLocalizedURL($locale, $currentAbs, [], true);
    }
    $canonical = preg_replace('~(?<!:)//+~', '/', $canonical);

    $metaTitle = $pageMetaTitle ?? $metaTitle;
    $metaDescription = $pageMetaDescription ?? $metaDescription;
    $keywords = $pageMetaKeywords ?? $keywords;
    $canonical = $pageCanonical ?? $canonical;
    $ogTitle = $pageOgTitle ?? $metaTitle;
    $ogDescription = $pageOgDescription ?? $metaDescription;
    $ogImage = $pageMetaImage ?? v('images/background/7.webp');
    $ogType = $pageOgType ?? 'website';

    $supported = array_keys(config('laravellocalization.supportedLocales') ?? []);

    $isRtl = $isRtl ?? in_array($locale, ['ar', 'ur', 'fa', 'he'], true);

    $phoneAssetRoutes = ['contact', 'checkout', 'digital.checkout.show', 'buynow', 'buynowpanel'];
    $checkoutCssRoutes = ['checkout', 'configure', 'digital.checkout.show', 'digital.checkout.store'];
    $needsPhoneAssets = in_array($routeName, $phoneAssetRoutes, true);
    $needsBlockingCheckoutCss = in_array($routeName, $checkoutCssRoutes, true);

    $pageTitleLcpBackgrounds = [
        'about' => ['images/background/7-lcp.webp', 'images/background/7-mobile.webp'],
        'packages' => ['images/background/9-lcp.webp', 'images/background/9-mobile.webp'],
        'pricing' => ['images/background/7-lcp.webp', 'images/background/7-mobile.webp'],
        'faqs' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
        'contact' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
        'reseller-panel' => ['images/background/7-lcp.webp', 'images/background/7-mobile.webp'],
        'iptv-applications' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
        'shop' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
        'buynow' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
        'buynowpanel' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
        'blogs.index' => ['images/background/10-lcp.webp', 'images/background/10-mobile.webp'],
    ];

    $pageTitleLcp = $pageTitleLcpBackgrounds[$routeName] ?? null;
@endphp

@if ($routeName === 'home' && empty($isMobile) && !empty($displayMovies[0]['webp_image_url'] ?? null))
    <link rel="preconnect" href="https://image.tmdb.org" crossorigin>
    <link rel="preload" as="image" href="{{ $displayMovies[0]['webp_image_url'] }}" fetchpriority="high">
@endif

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="keywords" content="{{ $keywords }}">

@if ($shouldNoindex)
    <meta name="robots" content="noindex,follow">
@else
    <meta name="robots" content="index,follow">
@endif

<script>
    var isRtl = {{ $isRtl ? 'true' : 'false' }};
</script>
<style>
    @media (max-width: 767px) {
        .page-title {
            background-image: var(--page-title-bg-mobile) !important;
        }
    }
</style>
@if ($routeName === 'packages')
    <style>
        .page-title {
            position: relative;
            overflow: hidden;
            padding: 200px 0;
            background-size: cover;
        }

        .page-title:before {
            position: absolute;
            content: "";
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to right, #010c3a 0, rgba(255, 255, 255, 0) 100%);
        }

        .page-title .auto-container {
            position: static;
            max-width: 1200px;
            padding: 0 15px;
            margin: 0 auto;
        }

        .page-title h2 {
            position: relative;
            color: #fff;
            padding-bottom: 15px;
        }

        .page-title .bread-crumb {
            position: relative;
        }

        .page-title .bread-crumb li {
            position: relative;
            font-weight: 500;
            display: inline-block;
            text-transform: uppercase;
            font-size: 15px;
            color: #fff;
            margin-right: 15px;
            padding-right: 15px;
        }

        .page-title .bread-crumb li:before {
            position: absolute;
            right: -3px;
            top: 0;
            content: "|";
            font-weight: 400;
            font-size: 15px;
            color: #fff;
        }

        .page-title .bread-crumb li:last-child {
            padding-right: 0;
            margin-right: 0;
        }

        .page-title .bread-crumb li:last-child:before {
            display: none;
        }

        .page-title .bread-crumb li a {
            font-weight: 500;
            color: #df0303;
        }

        .pricing-section.style-two {
            background-color: #fff;
            display: block;
            height: auto;
        }

        @media (max-width: 767px) {
            .page-title {
                padding: 100px 0;
            }
        }
    </style>
@endif
@if ($routeName === 'home')
    <style>
        @media (max-width: 767px) {
            .hero-section-mobile {
                min-height: 320px !important;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem 1rem;
                background-color: #fff;
                text-align: center;
            }

            .hero-section-mobile .container {
                max-width: 600px;
                margin: 0 auto;
            }

            .hero-section-mobile .subtitle,
            .hero-section-mobile .heading,
            .hero-section-mobile .description {
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
                animation: none !important;
            }

            .hero-section-mobile .subtitle {
                font-weight: 600;
                font-size: 1rem;
                color: #555;
                margin-bottom: .5rem;
            }

            .hero-section-mobile .heading {
                font-size: 1.4rem;
                font-weight: 700;
                color: #111;
                margin-bottom: 1rem;
            }

            .hero-section-mobile .description {
                margin-top: 1rem;
                color: #333;
                font-size: 1rem;
                line-height: 1.6;
            }
        }
    </style>
@endif

<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta name="facebook-domain-verification" content="rnsb3eqoa06k3dwo6gyqpphgu2imo2" />

<link rel="canonical" href="{{ $canonical }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">

@foreach ($supported as $lg)
    @php
        $href =
            $lg === $default && $hideDefault
                ? LaravelLocalization::getNonLocalizedURL($currentAbs)
                : LaravelLocalization::getLocalizedURL($lg, $currentAbs, [], true);
        $href = preg_replace('~(?<!:)//+~', '/', $href);
    @endphp
    <link rel="alternate" hreflang="{{ $lg }}" href="{{ $href }}" />
@endforeach
@php
    $xDefaultHref = $hideDefault
        ? LaravelLocalization::getNonLocalizedURL($currentAbs)
        : LaravelLocalization::getLocalizedURL($default, $currentAbs, [], true);
    $xDefaultHref = preg_replace('~(?<!:)//+~', '/', $xDefaultHref);
@endphp
<link rel="alternate" hreflang="x-default" href="{{ $xDefaultHref }}" />

@yield('jsonld')

<link rel="shortcut icon" href="{{ v('images/fav-icon.webp') }}" type="image/x-icon">
<link rel="apple-touch-icon" sizes="180x180" href="{{ v('images/apple-touch-icon.webp') }}">

@if ($pageTitleLcp)
    <link rel="preload" as="image" href="{{ asset($pageTitleLcp[0]) }}" type="image/webp"
        media="(min-width: 768px)" fetchpriority="high">
    <link rel="preload" as="image" href="{{ asset($pageTitleLcp[1]) }}" type="image/webp"
        media="(max-width: 767px)" fetchpriority="high">
@endif

@vite('resources/css/site-critical.css')
@if ($needsBlockingCheckoutCss)
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/checkout.css') }}" media="all">
@else
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/checkout.css') }}" media="print" onload="this.media='all'">
@endif
<link rel="stylesheet" href="{{ Vite::asset('resources/css/site-deferred.css') }}" media="print" onload="this.media='all'">
@stack('styles')

{{-- Preload critical fonts to reduce CLS --}}
@if ($routeName === 'packages')
    <link rel="preload" href="{{ Vite::asset('public/fonts/poppins/poppins-v21-latin-700.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('public/fonts/poppins/poppins-v21-latin-600.woff2') }}" as="font" type="font/woff2" crossorigin>
@else
    <link rel="preload" href="{{ Vite::asset('public/fonts/poppins/poppins-v21-latin-regular.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('public/fonts/poppins/poppins-v21-latin-700.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('public/fonts/poppins/poppins-v21-latin-600.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('public/fonts/poppins/poppins-v21-latin-500.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('public/fonts/Linearicons-Free.woff2') }}" as="font" type="font/woff2" crossorigin>
@endif

@if ($needsPhoneAssets)
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/css/intlTelInput.css">
@endif

<noscript>
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/site-deferred.css') }}">
    @unless ($needsBlockingCheckoutCss)
        <link rel="stylesheet" href="{{ Vite::asset('resources/css/checkout.css') }}">
    @endunless
    @if ($needsPhoneAssets)
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/css/intlTelInput.css">
    @endif
</noscript>

@if (!empty($fbPixels))
    <script>
        (function (w, d) {
            // Lightweight fbq queue (no network yet)
            w.__fbqScriptLoaded = w.__fbqScriptLoaded || false;
            w.__fbqPixelIds = w.__fbqPixelIds || [];
            if (!w.fbq) {
                var n = w.fbq = function () {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
                };
                if (!w._fbq) w._fbq = n;
                n.push = n; n.loaded = false; n.version = '2.0'; n.queue = [];
            }

            var ids = @json($fbPixels);
            ids.forEach(function (id) {
                if (w.__fbqPixelIds.indexOf(id) === -1) {
                    w.__fbqPixelIds.push(id);
                    fbq('init', id);
                }
            });
            // Event will sit in queue until script loads (on user interaction)
            fbq('track', 'PageView');

            // Loader is exposed but NOT called here (privacy + perf)
            function ensureFBScript() {
                if (w.__fbqScriptLoaded) return;
                var t = d.createElement('script');
                t.async = true;
                t.src = 'https://connect.facebook.net/en_US/fbevents.js';
                var s = d.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(t, s);
                w.__fbqScriptLoaded = true;
            }
            w.__ensureFBScript = ensureFBScript;
        })(window, document);
    </script>

    @foreach ($fbPixels as $pId)
        <noscript>
            <img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id={{ $pId }}&ev=PageView&noscript=1" />
        </noscript>
    @endforeach
@endif

<script>
    // ------- Analytics/Pixel lazy loader (no 4s fallback) -------
    window.dataLayer = window.dataLayer || [];
    function gtag(){ dataLayer.push(arguments); }

    document.addEventListener("DOMContentLoaded", function () {
        const loaded = { ga:false, clarity:false, pixel:false };
        const events = ['scroll','mousemove','touchstart','pointerdown','keydown'];
        const opts = { once:true, passive:true };

        function loadGA() {
            if (loaded.ga) return; loaded.ga = true;
            const s = document.createElement("script");
            s.src = "https://www.googletagmanager.com/gtag/js?id=G-L98JG9ZT7H";
            s.async = true;
            (document.head || document.body).appendChild(s);
            s.onload = function () {
                gtag('js', new Date());
                gtag('config', 'G-L98JG9ZT7H');
            };
        }

        function loadClarity() {
            if (loaded.clarity) return; loaded.clarity = true;
            (function(c,l,a,r,i,t,y){
                c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                t=l.createElement(r); t.async=1; t.src="https://www.clarity.ms/tag/"+i;
                y=l.getElementsByTagName(r)[0]; y.parentNode.insertBefore(t,y);
            })(window, document, "clarity", "script", "sq6nn3dn69");
        }

        function loadFBPixel() {
            if (loaded.pixel) return; loaded.pixel = true;
            if (window.__ensureFBScript) { window.__ensureFBScript(); return; }
            var t = document.createElement('script');
            t.async = true;
            t.src = 'https://connect.facebook.net/en_US/fbevents.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(t, s);
        }

        function loadAll() {
            loadGA();
            loadClarity();
            loadFBPixel();
            events.forEach(ev => window.removeEventListener(ev, loadAll, opts));
        }

        // Load only after first interaction (no immediate call, no timeout)
        events.forEach(ev => window.addEventListener(ev, loadAll, opts));
        // If you need a consent gate, call loadAll() only after consent given.
    });
</script>

<script>
    // --------- WhatsApp tracking + CAPI beacon (unchanged) ---------
    (function () {
        function uuidv4() {
            if (crypto && crypto.randomUUID) return crypto.randomUUID();
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c){
                const r = Math.random()*16|0, v = c === 'x' ? r : (r&0x3|0x8);
                return v.toString(16);
            });
        }

        function isWhatsApp(href) {
            if (!href) return false;
            href = href.toLowerCase();
            return href.startsWith('https://wa.me/')
                || href.startsWith('https://api.whatsapp.com/send')
                || href.startsWith('whatsapp://send');
        }

        function readCookie(name) {
            const value = '; ' + document.cookie;
            const parts = value.split('; ' + name + '=');
            if (parts.length !== 2) return null;
            return decodeURIComponent(parts.pop().split(';').shift() || '');
        }

        function sendCAPI(eventId, dest) {
            var payload = {
                event_id:eventId,
                destination:dest,
                page:location.href,
                fbp:readCookie('_fbp'),
                fbc:readCookie('_fbc')
            };
            if (navigator.sendBeacon) {
                const blob = new Blob([JSON.stringify(payload)], { type:'application/json' });
                navigator.sendBeacon("{{ route('track.whatsapp.trial') }}", blob);
            } else {
                fetch("{{ route('track.whatsapp.trial') }}", {
                    method:'POST',
                    headers:{ 'Content-Type':'application/json' },
                    body: JSON.stringify(payload),
                    keepalive:true
                });
            }
        }

        document.addEventListener('click', function (e) {
            const el = e.target.closest('a[data-trial], button[data-trial]');
            if (!el) return;
            const href = el.tagName === 'A' ? el.getAttribute('href') : el.getAttribute('data-wa-href');
            if (!href || !isWhatsApp(href)) return;

            const eventId = uuidv4();
            try {
                fbq('track', 'StartTrial', {
                    value: 0, currency: "{{ $currency }}",
                    content_name: 'WhatsApp', contact_channel: 'whatsapp', destination: href
                }, { eventID: eventId });
            } catch(e) { /* fbq may not be loaded yet, but queue will hold */ }

            sendCAPI(eventId, href);

            if (el.tagName === 'BUTTON') {
                e.preventDefault();
                setTimeout(function () { window.open(href, '_blank', 'noopener'); }, 50);
            }
        }, { passive:true });
    })();
</script>
