<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="theme" content="Opplex IPTV UI Theme">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

@php
    use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

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
    $noindexRoutes = ['redirect.ad', 'buynow', 'buy-now-panel'];

    $pageParam = (int) request()->input('page', 1);
    $hasSearch = trim((string) request()->input('search', '')) !== '';

    $shouldNoindex =
        in_array($routeName, $noindexRoutes, true) || ($routeName === 'movies' && ($pageParam > 1 || $hasSearch));

    $default = LaravelLocalization::getDefaultLocale();
    $hideDefault = (bool) (config('laravellocalization.hideDefaultLocaleInURL') ?? false);

    $currentAbs = url()->current();

    if ($locale === $default && $hideDefault) {
        $canonical = LaravelLocalization::getNonLocalizedURL($currentAbs);
    } else {
        $canonical = LaravelLocalization::getLocalizedURL($locale, $currentAbs, [], true);
    }
    $canonical = preg_replace('~(?<!:)//+~', '/', $canonical);

    $supported = array_keys(config('laravellocalization.supportedLocales') ?? []);

    $isRtl = $isRtl ?? in_array($locale, ['ar', 'ur', 'fa', 'he'], true);
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="cryptomus" content="72ee2329" />

@if ($shouldNoindex)
    <meta name="robots" content="noindex,follow">
@else
    <meta name="robots" content="index,follow">
@endif

<script>
    var isRtl = {{ $isRtl ? 'true' : 'false' }};
</script>

<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ asset('images/background/7.webp') }}">
<meta name="facebook-domain-verification" content="rnsb3eqoa06k3dwo6gyqpphgu2imo2" />

<link rel="canonical" href="{{ $canonical }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ asset('images/background/7.webp') }}">

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

<link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/fav-icon.webp') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.webp') }}">

<link rel="preload" href="{{ asset('css/style.css') }}" as="style">
<link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" as="style"
    crossorigin>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" media="all">
<link rel="stylesheet" href="{{ asset('css/style.css') }}" media="all">

@php
    $nonCriticalStyles = [
        'global.css',
        'header.css',
        'footer.css',
        'font-awesome.css',
        'flaticon.css',
        'animate.css',
        'owl.css',
        'swiper.css',
        'linearicons.css',
        'jquery-ui.css',
        'custom-animate.css',
        'jquery.fancybox.min.css',
        'jquery.mCustomScrollbar.min.css',
    ];
@endphp

@foreach ($nonCriticalStyles as $style)
    <link rel="preload" href="{{ asset("css/$style") }}" as="style">
@endforeach
@foreach ($nonCriticalStyles as $style)
    <link rel="stylesheet" href="{{ asset("css/$style") }}" media="all">
@endforeach

<link rel="stylesheet" href="{{ asset('css/responsive.css') }}" media="all">
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}" media="all">

@if (!empty($displayMovies[0]['webp_image_url'] ?? null))
    <link rel="preload" as="image" href="{{ $displayMovies[0]['webp_image_url'] }}" fetchpriority="high">
@endif

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/css/intlTelInput.css">

<noscript>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @foreach ($nonCriticalStyles as $style)
        <link rel="stylesheet" href="{{ asset("css/$style") }}">
    @endforeach
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
</noscript>

@if (!empty($fbPixels))
    <script>
        (function(w, d) {
            w.__fbqScriptLoaded = w.__fbqScriptLoaded || false;
            w.__fbqPixelIds = w.__fbqPixelIds || [];
            if (!w.fbq) {
                var n = w.fbq = function() {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
                };
                if (!w._fbq) w._fbq = n;
                n.push = n;
                n.loaded = false;
                n.version = '2.0';
                n.queue = [];
            }
            var ids = @json($fbPixels);
            ids.forEach(function(id) {
                if (w.__fbqPixelIds.indexOf(id) === -1) {
                    w.__fbqPixelIds.push(id);
                    fbq('init', id);
                }
            });
            fbq('track', 'PageView');

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
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const loaded = {
            ga: false,
            clarity: false,
            pixel: false
        };
        const events = ['scroll', 'mousemove', 'touchstart', 'pointerdown', 'keydown'];
        const opts = {
            once: true,
            passive: true
        };

        function loadGA() {
            if (loaded.ga) return;
            loaded.ga = true;
            const s = document.createElement("script");
            s.src = "https://www.googletagmanager.com/gtag/js?id=G-L98JG9ZT7H";
            s.async = true;
            (document.head || document.body).appendChild(s);
            s.onload = function() {
                gtag('js', new Date());
                gtag('config', 'G-L98JG9ZT7H');
            };
        }

        function loadClarity() {
            if (loaded.clarity) return;
            loaded.clarity = true;
            (function(c, l, a, r, i, t, y) {
                c[a] = c[a] || function() {
                    (c[a].q = c[a].q || []).push(arguments)
                };
                t = l.createElement(r);
                t.async = 1;
                t.src = "https://www.clarity.ms/tag/" + i;
                y = l.getElementsByTagName(r)[0];
                y.parentNode.insertBefore(t, y);
            })(window, document, "clarity", "script", "sq6nn3dn69");
        }

        function loadFBPixel() {
            if (loaded.pixel) return;
            loaded.pixel = true;
            if (window.__ensureFBScript) {
                window.__ensureFBScript();
                return;
            }
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
        events.forEach(ev => window.addEventListener(ev, loadAll, opts));
        loadAll();
        setTimeout(loadAll, 4000);
    });
</script>

<script>
    (function() {
        function uuidv4() {
            if (crypto && crypto.randomUUID) return crypto.randomUUID();
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0,
                    v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }

        function isWhatsApp(href) {
            if (!href) return false;
            href = href.toLowerCase();
            return href.startsWith('https://wa.me/') || href.startsWith('https://api.whatsapp.com/send') || href
                .startsWith('whatsapp://send');
        }

        function sendCAPI(eventId, dest) {
            var payload = {
                event_id: eventId,
                destination: dest,
                page: location.href,
                _token: "{{ csrf_token() }}"
            };
            if (navigator.sendBeacon) {
                const blob = new Blob([JSON.stringify(payload)], {
                    type: 'application/json'
                });
                navigator.sendBeacon("{{ route('track.whatsapp.trial') }}", blob);
            } else {
                fetch("{{ route('track.whatsapp.trial') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(payload),
                    keepalive: true
                });
            }
        }
        document.addEventListener('click', function(e) {
            const el = e.target.closest('a[data-trial], button[data-trial]');
            if (!el) return;
            const href = el.tagName === 'A' ? el.getAttribute('href') : el.getAttribute('data-wa-href');
            if (!href || !isWhatsApp(href)) return;

            const eventId = uuidv4();
            try {
                fbq('track', 'StartTrial', {
                    value: 0,
                    currency: "{{ $currency }}",
                    content_name: 'WhatsApp',
                    contact_channel: 'whatsapp',
                    destination: href
                }, {
                    eventID: eventId
                });
            } catch (e) {}
            sendCAPI(eventId, href);

            if (el.tagName === 'BUTTON') {
                e.preventDefault();
                setTimeout(function() {
                    window.open(href, '_blank', 'noopener');
                }, 50);
            }
        }, {
            passive: true
        });
    })();
</script>
