<head>

    @php

        $route = Request::route() ? Request::route()->getName() : 'home';
        $locale = app()->getLocale();

        $meta = trans("meta.$route");

        $metaTitle = $meta['title'] ?? 'Default Title';
        $metaDescription = $meta['description'] ?? 'Default Description';
        $keywords = $meta['keywords'] ?? '';
    @endphp

    <title>{{ $metaTitle }}</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme" content="Opplex IPTV UI Theme">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $keywords }}">
    <meta name="robots" content="index, follow">

    <script>
        var isRtl = {{ $isRtl ? 'true' : 'false' }};
    </script>

    {{-- OpenGraph / Facebook --}}
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/background/7.webp') }}">

    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Twitter Cards --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ asset('images/background/7.webp') }}">

    {{-- Multilingual hreflang (important for Europe targeting) --}}
    <link rel="alternate" hreflang="en" href="{{ LaravelLocalization::getLocalizedURL('en') }}" />
    <link rel="alternate" hreflang="fr" href="{{ LaravelLocalization::getLocalizedURL('fr') }}" />
    <link rel="alternate" hreflang="it" href="{{ LaravelLocalization::getLocalizedURL('it') }}" />
    <link rel="alternate" hreflang="x-default"
        href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getDefaultLocale()) }}" />

    @yield('jsonld')

    <style>
        .hero-section-mobile {
            padding: 2rem 1rem;
            background-color: #fff;
            text-align: center;
        }

        .hero-section-mobile .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .hero-section-mobile .subtitle {
            font-weight: 600;
            font-size: 1rem;
            color: #555;
            margin-bottom: 0.5rem;
        }

        .hero-section-mobile .heading {
            font-size: 1.4rem;
            font-weight: bold;
            color: #111;
            margin-bottom: 1rem;
        }

        .hero-section-mobile .description {
            margin-top: 1rem;
            color: #333;
            font-size: 1rem;
            line-height: 1.6;
        }

        .hero-section-mobile .btn-group {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-section-mobile .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .hero-section-mobile .btn-primary {
            background-color: #df0303;
            color: white;
        }

        .hero-section-mobile .btn-primary:hover {
            background-color: #df0303;
        }

        .hero-section-mobile .btn-outline {
            border: 2px solid #df0303;
            color: #df0303;
            background-color: transparent;
        }

        .hero-section-mobile .btn-outline:hover {
            background-color: #f0f8ff;
        }
    </style>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/fav-icon.webp') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.webp') }}">

    @if (!empty($displayMovies[0]['webp_image_url']))
        <link rel="preload" as="image" href="{{ $displayMovies[0]['webp_image_url'] }}" fetchpriority="high">
    @endif

    <link rel="preload" href="{{ asset('css/style.css') }}" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" as="style"
        crossorigin>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        media="all">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" media="all">
    <link rel="stylesheet" href="{{ asset('css/discount-wheel.css') }}" media="all">

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

    <noscript>
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        @foreach ($nonCriticalStyles as $style)
            <link rel="stylesheet" href="{{ asset("css/$style") }}">
        @endforeach
        <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
        <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    </noscript>

    {{-- Google Analytics Load Optimization --}}
    <script>
        // ---- Google Analytics (as you had) ----
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        document.addEventListener("DOMContentLoaded", function() {
            let hasLoadedGTM = false;
            let hasLoadedClarity = false;
            let hasLoadedFBPixel = false;

            function loadGTM() {
                if (hasLoadedGTM) return;
                hasLoadedGTM = true;

                const gtmScript = document.createElement("script");
                gtmScript.src = "https://www.googletagmanager.com/gtag/js?id=G-L98JG9ZT7H";
                gtmScript.async = true;
                document.head.appendChild(gtmScript);

                gtmScript.onload = function() {
                    gtag('js', new Date());
                    gtag('config', 'G-L98JG9ZT7H');
                };
            }

            function loadClarity() {
                if (hasLoadedClarity) return;
                hasLoadedClarity = true;

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

            // ---------- META PIXEL ----------
            function loadFBPixel() {
                if (hasLoadedFBPixel) return;
                hasLoadedFBPixel = true;

                ! function(f, b, e, v, n, t, s) {
                    if (f.fbq) return;
                    n = f.fbq = function() {
                        n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                    };
                    if (!f._fbq) f._fbq = n;
                    n.push = n;
                    n.loaded = !0;
                    n.version = '2.0';
                    n.queue = [];
                    t = b.createElement(e);
                    t.async = !0;
                    t.src = v;
                    s = b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t, s);
                }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

                // --- Your Pixel ID ---
                fbq('init', '1467807554407581');
                fbq('track', 'PageView'); // base page view

                // when fbq is ready, bind events
                whenFBQReady(bindMetaEvents);
            }

            // wait until fbq is callable
            function whenFBQReady(cb) {
                if (window.fbq && fbq.callMethod) return cb();
                const iv = setInterval(function() {
                    if (window.fbq && fbq.callMethod) {
                        clearInterval(iv);
                        cb();
                    }
                }, 100);
                setTimeout(() => clearInterval(iv), 10000);
            }

            // ---------- EVENT BINDINGS (edit URLs/IDs if needed) ----------
            function bindMetaEvents() {
                // Toggle this to true to see console logs:
                const DEBUG = false;
                const log = (...a) => {
                    if (DEBUG) console.log('[PIXEL]', ...a);
                };

                const path = location.pathname.toLowerCase();

                // a) ViewContent for key content pages (home + iptv apps/services)
                if (path === '/' || path.includes('/iptv-applications') || path.includes('/services')) {
                    fbq('track', 'ViewContent');
                    log('ViewContent');
                }

                // b) WhatsApp clicks -> Contact
                document.querySelectorAll('a[href*="wa.me"], a[href*="api.whatsapp.com"]').forEach(a => {
                    a.addEventListener('click', function() {
                        fbq('track', 'Contact');
                        log('Contact (WhatsApp)');
                    }, {
                        passive: true
                    });
                });

                // c) CTA / pricing / checkout clicks -> InitiateCheckout
                document.querySelectorAll(
                    'a[href*="/buy"], a[href*="pricing"], a[href*="/cart"], a[href*="/checkout"]').forEach(
                a => {
                    a.addEventListener('click', function() {
                        fbq('track', 'InitiateCheckout');
                        log('InitiateCheckout');
                    }, {
                        passive: true
                    });
                });

                // d) Thank-you/success pages -> Lead
                if (path.includes('thank') || path.includes('/success') || path.includes('/trial-submitted')) {
                    fbq('track', 'Lead');
                    log('Lead (thank-you/success)');
                }

                // e) Purchase page (placeholder — adjust to your real success URL & value)
                // e.g., WooCommerce: /checkout/order-received/
                if (path.includes('/order-received') || path.includes('/purchase-complete')) {
                    // If amount is available in your DOM, read and send it; else keep 0
                    fbq('track', 'Purchase', {
                        value: 0,
                        currency: 'USD'
                    });
                    log('Purchase (placeholder)');
                    // Prevent double-fire on refresh:
                    sessionStorage.setItem('purchaseFired', '1');
                }
                if (sessionStorage.getItem('purchaseFired') === '1' && (path.includes('/order-received') || path
                        .includes('/purchase-complete'))) {
                    // do nothing on subsequent reloads
                }

                // Utility: manual test trigger from console
                window.__pixelTest = function() {
                    fbq('track', 'ViewContent');
                    fbq('track', 'Lead');
                    console.log('Test events fired');
                };
            }

            // Load all trackers together
            const loadTrackingScripts = () => {
                loadGTM();
                loadClarity();
                loadFBPixel();
            };

            // Load on first interaction (wide set of events)
            const onceOpts = {
                once: true,
                passive: true
            };
            ['scroll', 'mousemove', 'touchstart', 'pointerdown', 'keydown'].forEach(ev => {
                window.addEventListener(ev, loadTrackingScripts, onceOpts);
            });

            // Fallback: overlay/ETT ki wajah se interaction na aaye to 4s baad auto-load
            setTimeout(loadTrackingScripts, 4000);
        });
    </script>

    <!-- No-JS fallback for Meta Pixel -->
    <noscript>
        <img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=1467807554407581&ev=PageView&noscript=1" />
    </noscript>




</head>
