<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="theme" content="Opplex IPTV UI Theme">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

@php
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
    $firstHeroImage = null;
    if ($routeName === 'home' && empty($isMobile) && !empty($displayMovies[0]['webp_image_url'] ?? null)) {
        $firstHeroImage = $displayMovies[0]['webp_image_url'];
    }
    $preconnectTmdb = $firstHeroImage && str_starts_with((string) $firstHeroImage, 'https://image.tmdb.org/');
@endphp

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

<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
@if ($preconnectTmdb)
    <link rel="preconnect" href="https://image.tmdb.org" crossorigin>
@endif
@if ($pageTitleLcp)
    <link rel="preload" as="image" href="{{ asset($pageTitleLcp[0]) }}" type="image/webp"
        media="(min-width: 768px)" fetchpriority="high">
    <link rel="preload" as="image" href="{{ asset($pageTitleLcp[1]) }}" type="image/webp"
        media="(max-width: 767px)" fetchpriority="high">
@endif
@if ($firstHeroImage)
    <link rel="preload" as="image" href="{{ $firstHeroImage }}" fetchpriority="high">
@endif

<style>
    @font-face {
        font-family: Poppins;
        font-style: normal;
        font-weight: 500;
        font-display: swap;
        src: url('{{ asset('fonts/poppins/poppins-v21-latin-500.woff2') }}') format('woff2');
    }

    @font-face {
        font-family: Poppins;
        font-style: normal;
        font-weight: 600;
        font-display: swap;
        src: url('{{ asset('fonts/poppins/poppins-v21-latin-600.woff2') }}') format('woff2');
    }

    @font-face {
        font-family: Poppins;
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url('{{ asset('fonts/poppins/poppins-v21-latin-700.woff2') }}') format('woff2');
    }

    :root {
        --main-color: rgb(223, 3, 3);
        --main-color-two: rgb(1, 12, 58);
        --white-color: rgb(255, 255, 255);
        --dark-color: rgb(37, 37, 37);
        --dark-color-two: rgb(1, 1, 1);
        --heading-color: rgb(1, 1, 1);
        --font-family-poppins: "Poppins", sans-serif;
        --font-14: 14px;
        --font-15: 15px;
        --font-16: 16px;
        --font-18: 18px;
        --font-24: 24px;
        --font-26: 26px;
        --font-36: 36px;
        --font-50: 50px;
        --font-80: 80px;
        --margin-zero: 0;
        --margin-right-20: 20px;
        --margin-right-25: 25px;
        --margin-right-35: 35px;
        --margin-bottom-15: 15px;
        --margin-bottom-25: 25px;
        --margin-bottom-30: 30px;
        --padding-zero: 0;
        --padding-left-25: 25px;
        --padding-bottom-15: 15px;
    }

    *, ::before, ::after {
        box-sizing: border-box;
    }

    html {
        line-height: 1.15;
        -webkit-text-size-adjust: 100%;
    }

    body {
        margin: 0;
        color: var(--dark-color);
        background: #fff;
        font-family: var(--font-family-poppins);
        font-size: 14px;
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
    }

    article, aside, footer, header, nav, section {
        display: block;
    }

    a {
        color: var(--main-color);
        text-decoration: none;
    }

    img {
        display: inline-block;
        max-width: 100%;
        height: auto;
        border-style: none;
        vertical-align: middle;
    }

    h1, h2, h3, h4, h5, h6, p, ul {
        margin: 0;
        padding: 0;
    }

    ul {
        list-style: none;
    }

    h1, h2, h3, h4, h5, h6 {
        color: var(--heading-color);
        font-weight: 600;
    }

    .container,
    .auto-container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .clearfix::after {
        display: block;
        clear: both;
        content: "";
    }

    .pull-left {
        float: left;
    }

    .pull-right {
        float: right;
    }

    .d-flex {
        display: flex !important;
    }

    .d-none {
        display: none !important;
    }

    .align-items-center {
        align-items: center !important;
    }

    .align-items-start {
        align-items: flex-start !important;
    }

    .justify-content-between {
        justify-content: space-between !important;
    }

    .justify-content-center {
        justify-content: center !important;
    }

    .flex-wrap {
        flex-wrap: wrap !important;
    }

    .text-center {
        text-align: center !important;
    }

    .text-left {
        text-align: left !important;
    }

    .text-right {
        text-align: right !important;
    }

    .text-white {
        color: #fff !important;
    }

    .py-2 {
        padding-top: .5rem !important;
        padding-bottom: .5rem !important;
    }

    .gap-2 {
        gap: .5rem !important;
    }

    .btn,
    .theme-btn {
        display: inline-block;
        cursor: pointer;
    }

    .btn-primary {
        color: #fff;
        background: var(--main-color);
        border-color: var(--main-color);
    }

    .main-header {
        position: relative;
        z-index: 99;
        width: 100%;
        background: #fff;
    }

    .main-header .header-top {
        position: relative;
        overflow: hidden;
        background-color: var(--main-color-two);
    }

    .main-header .header-top .info,
    .main-header .header-top .social-box {
        position: relative;
        padding: 13px 0;
    }

    .main-header .header-top .info li,
    .main-header .header-top .social-box li {
        position: relative;
        display: inline-block;
    }

    .main-header .header-top .info li {
        margin-right: var(--margin-right-20);
        color: #fff;
        font-size: var(--font-15);
    }

    .main-header .header-top .info li a,
    .main-header .header-top .social-box li a {
        color: #fff;
    }

    .main-header .header-top .social-box li {
        margin-left: 25px;
    }

    .main-header .header-lower {
        position: relative;
        background: #fff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, .06);
    }

    .main-header .logo-box {
        position: relative;
        z-index: 10;
        float: left;
        padding: 10px 0;
    }

    .main-header .logo-box .logo img {
        display: inline-block;
        max-width: 100%;
    }

    .main-header .header-lower .nav-outer {
        position: relative;
        float: right;
    }

    .main-header .main-menu,
    .main-header .main-menu .navigation > li {
        position: relative;
        float: left;
    }

    .main-header .main-menu .navbar-collapse {
        display: block !important;
        float: left;
        padding: 0;
    }

    .main-header .main-menu .navigation {
        position: relative;
        margin: 0;
    }

    .main-header .main-menu .navigation > li {
        margin-right: var(--margin-right-35);
    }

    .main-header .main-menu .navigation > li > a {
        position: relative;
        display: block;
        padding: 35px 0;
        color: var(--dark-color);
        font-size: 14px;
        font-weight: 600;
        line-height: 30px;
        text-align: center;
        text-transform: capitalize;
    }

    .main-header .main-menu .navigation > li > ul {
        position: absolute;
        width: 15rem;
        visibility: hidden;
        opacity: 0;
        transform: scaleY(0);
        transform-origin: top;
        background-color: var(--main-color);
    }

    .main-header .main-menu .navigation > li.dropdown:hover > ul {
        visibility: visible;
        opacity: 1;
        transform: scaleY(1);
    }

    .main-header .nav-outer .mobile-nav-toggler {
        position: relative;
        float: right;
        width: 44px;
        height: 44px;
        display: none;
        align-items: center;
        justify-content: center;
        color: #000;
        font-size: var(--font-26);
        line-height: 44px;
        cursor: pointer;
    }

    .main-slider-two {
        position: relative;
        overflow: hidden;
        background: var(--main-color-two);
    }

    .main-slider-two .slide {
        position: relative;
        overflow: hidden;
        background-position: center right;
        background-size: cover;
    }

    .main-slider-two .slide::before {
        position: absolute;
        inset: 0;
        z-index: 1;
        content: "";
        background: linear-gradient(to right, #010c3a 0, rgba(255, 255, 255, 0) 100%);
    }

    .main-slider-two .slide > img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .custom-height {
        min-height: 720px;
        display: flex;
        align-items: center;
    }

    .main-slider-two .content-boxed {
        position: relative;
        z-index: 10;
        max-width: 750px;
    }

    .main-slider-two h1 {
        margin-bottom: var(--margin-bottom-25);
        color: #fff;
        font-size: var(--font-80);
        line-height: 1.1;
    }

    .main-slider-two .text {
        max-width: 600px;
        margin-bottom: var(--margin-bottom-30);
        color: #fff;
        font-size: var(--font-16);
        line-height: 2;
    }

    .btn-style-two {
        position: relative;
        overflow: hidden;
        display: inline-grid;
        padding: 13px 40px;
        border-radius: 3px;
        color: #fff;
        background-color: var(--main-color);
        font-size: var(--font-16);
        font-weight: 500;
        line-height: 30px;
        text-align: center;
        text-transform: capitalize;
    }

    .hero-section-mobile {
        min-height: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        text-align: center;
        background: #fff;
    }

    .hero-section-mobile .heading {
        margin-bottom: 1rem;
        color: #111;
        font-size: 1.4rem;
        font-weight: 700;
    }

    .hero-section-mobile .subtitle {
        margin-bottom: .5rem;
        color: #555;
        font-size: 1rem;
        font-weight: 600;
    }

    .hero-section-mobile .description {
        margin-top: 1rem;
        color: #333;
        font-size: 1rem;
        line-height: 1.6;
    }

    @media (max-width: 1023px) {
        .main-header .nav-outer .mobile-nav-toggler {
            display: inline-flex;
        }
    }

    @media (max-width: 767px) {
        .pull-left,
        .pull-right,
        .main-header .logo-box,
        .main-header .header-lower .nav-outer {
            float: none;
        }

        .main-header .main-menu {
            display: none;
        }

        .main-header .logo-box {
            padding-bottom: var(--padding-bottom-15);
        }

        .main-slider-two,
        .main-slider-two .slide {
            min-height: 220px;
            display: flex;
            align-items: center;
        }

        .custom-height {
            min-height: 220px;
        }

        .main-slider-two h1 {
            font-size: 15px;
        }

        .main-slider-two .text {
            max-height: 300px;
            overflow: hidden;
            font-size: 14px;
            line-height: 1.5;
        }

        .btn-style-two {
            padding: 7px 10px;
        }
    }
</style>

@php
    $preloadedStyles = [
        ['href' => 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css', 'crossorigin' => true],
        ['href' => v('css/style.css')],
        ['href' => v('css/global.css')],
        ['href' => v('css/header.css')],
        ['href' => v('css/responsive.css')],
        ['href' => v('css/fonts.css')],
    ];

    $deferredStyles = [
        ['href' => v('css/discount-wheel.css')],
        ['href' => v('css/footer.css')],
        ['href' => v('css/font-awesome.css')],
        ['href' => v('css/flaticon.css')],
        ['href' => v('css/linearicons.css')],
        ['href' => v('css/voice-assistant.css')],
        ['href' => v('css/accessibility-fixes.css')],
        ['href' => v('css/animate.css')],
        ['href' => v('css/owl.css')],
        ['href' => v('css/swiper.css')],
        ['href' => v('css/jquery-ui.css')],
        ['href' => v('css/custom-animate.css')],
        ['href' => v('css/jquery.fancybox.min.css')],
        ['href' => v('css/jquery.mCustomScrollbar.min.css')],
    ];

    if ($needsBlockingCheckoutCss) {
        $preloadedStyles[] = ['href' => v('css/checkout.css')];
    } else {
        $deferredStyles[] = ['href' => v('css/checkout.css')];
    }

    if ($needsPhoneAssets) {
        $deferredStyles[] = ['href' => 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/css/intlTelInput.css'];
    }
@endphp

@foreach ($preloadedStyles as $style)
    <link rel="preload" href="{{ $style['href'] }}" as="style"
        onload="this.onload=null;this.rel='stylesheet'" @if (!empty($style['crossorigin'])) crossorigin @endif>
@endforeach
@foreach ($deferredStyles as $style)
    <link rel="stylesheet" href="{{ $style['href'] }}" media="print" onload="this.onload=null;this.media='all'">
@endforeach

@stack('styles')

{{-- Preload critical fonts to reduce CLS --}}
<link rel="preload" href="{{ asset('fonts/poppins/poppins-v21-latin-700.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="{{ asset('fonts/poppins/poppins-v21-latin-600.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="{{ asset('fonts/poppins/poppins-v21-latin-500.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="{{ asset('fonts/Linearicons-Free.woff2') }}" as="font" type="font/woff2" crossorigin>

<noscript>
    @foreach (array_merge($preloadedStyles, $deferredStyles) as $style)
        <link rel="stylesheet" href="{{ $style['href'] }}" @if (!empty($style['crossorigin'])) crossorigin @endif>
    @endforeach
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
