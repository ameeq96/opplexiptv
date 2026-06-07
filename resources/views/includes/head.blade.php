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
    $needsCheckoutCss = $needsBlockingCheckoutCss || $routeName !== 'movies';

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
    $isMoviesRoute = $routeName === 'movies';
    $pageTitleCriticalRoutes = ['about', 'contact', 'reseller-panel', 'pricing', 'shop', 'blogs.index'];
    $staticBelowFoldRoutes = ['about', 'reseller-panel', 'pricing'];
    $leanFontRoutes = array_merge(['packages', 'faqs'], $pageTitleCriticalRoutes, ['movies']);
@endphp

@if ($routeName === 'home' && empty($isMobile) && !empty($displayMovies[0]['webp_image_url'] ?? null))
    <link rel="preconnect" href="https://image.tmdb.org" crossorigin>
    <link rel="preload" as="image" href="{{ $displayMovies[0]['webp_image_url'] }}" fetchpriority="high">
@endif
@if ($isMoviesRoute)
    <link rel="preconnect" href="https://image.tmdb.org" crossorigin>
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
@if ($routeName === 'faqs')
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

        .faq-section {
            position: relative;
            padding: 100px 0;
            background-attachment: fixed;
            background-size: cover;
        }

        .faq-section .accordion-column {
            position: relative;
            margin-bottom: 25px;
        }

        .faq-section .accordion-column .inner-column,
        .faq-section .accordion-box {
            position: relative;
        }

        .faq-section .sec-title {
            position: relative;
            margin-bottom: 40px;
        }

        .faq-section .sec-title .separator {
            position: relative;
            width: 88px;
            height: 5px;
            background-color: #df0303;
            margin-bottom: 25px;
        }

        .faq-section .accordion-box .block {
            position: relative;
            border-radius: 4px;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, .1);
        }

        .faq-section .accordion-box .block .acc-btn {
            position: relative;
            cursor: pointer;
            font-size: 18px;
            line-height: 30px;
            font-weight: 700;
            padding: 18px 30px;
            text-transform: capitalize;
            color: #010101;
        }

        .faq-section .accordion-box .block .acc-content {
            position: relative;
            display: none;
        }

        .faq-section .accordion-box .block .acc-content.current {
            display: block;
        }

        .faq-section .accordion-box .block .content {
            position: relative;
            padding: 0 25px 25px 30px;
        }

        @media (max-width: 767px) {
            .page-title {
                padding: 100px 0;
            }
        }
    </style>
@endif
@if (in_array($routeName, $pageTitleCriticalRoutes, true))
    <style>
        .page-title {
            position: relative;
            overflow: hidden;
            padding: 200px 0;
            background-size: cover;
            background-position: center center;
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

        .contact-page-section {
            position: relative;
            padding: 110px 0;
        }

        .contact-page-section .contact-form-box {
            position: relative;
            max-width: 920px;
            width: 100%;
            margin: 45px auto 0;
            border-radius: 5px;
            padding: 60px;
            box-shadow: 0 0 25px rgba(0, 0, 0, .1);
        }

        .shop-section {
            position: relative;
        }

        .blogs-wrap {
            padding: 50px 0 90px;
        }

        @media (max-width: 767px) {
            .page-title {
                padding: 100px 0;
            }
        }
    </style>
@endif
@if ($isMoviesRoute)
    <style>
        @font-face {
            font-family: Poppins;
            font-style: normal;
            font-weight: 600;
            font-display: swap;
            src: url("{{ Vite::asset('public/fonts/poppins/poppins-v21-latin-600.woff2') }}") format("woff2");
        }

        @font-face {
            font-family: Poppins;
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url("{{ Vite::asset('public/fonts/poppins/poppins-v21-latin-700.woff2') }}") format("woff2");
        }

        *,
        *:before,
        *:after {
            box-sizing: border-box;
        }

        html {
            font-family: sans-serif;
            line-height: 1.15;
            -webkit-text-size-adjust: 100%;
        }

        body {
            margin: 0;
            color: #555;
            font-family: Poppins, Arial, sans-serif;
            font-size: 16px;
            line-height: 1.7;
            background: #fff;
        }

        a {
            color: #df0303;
            text-decoration: none;
            background-color: transparent;
        }

        img {
            max-width: 100%;
            height: auto;
            border-style: none;
            vertical-align: middle;
        }

        ul {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .clearfix:after {
            display: block;
            clear: both;
            content: "";
        }

        .auto-container {
            position: relative;
            max-width: 1200px;
            padding: 0 15px;
            margin: 0 auto;
        }

        .pull-left {
            float: left;
        }

        .pull-right {
            float: right;
        }

        .d-flex {
            display: flex;
        }

        .align-items-center {
            align-items: center;
        }

        .justify-content-start {
            justify-content: flex-start;
        }

        .justify-content-center {
            justify-content: center;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #6c757d;
        }

        .my-3 {
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .mx-2 {
            margin-left: .5rem;
            margin-right: .5rem;
        }

        .px-4 {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .h3,
        h1 {
            font-size: 1.75rem;
            line-height: 1.2;
        }

        .h4,
        h2 {
            font-size: 1.5rem;
            line-height: 1.2;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: .25rem;
        }

        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            vertical-align: middle;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem;
        }

        .btn-search {
            color: #fff;
            background-color: #df0303;
            border-color: #df0303;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .main-header {
            position: relative;
            z-index: 99;
            width: 100%;
        }

        .main-header .header-top {
            position: relative;
            overflow: hidden;
            background-color: #010c3a;
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
            margin-right: 20px;
            color: #fff;
            font-size: 15px;
        }

        .main-header .header-top .info li a,
        .main-header .header-top .social-box li a {
            color: #fff;
        }

        .main-header .header-top .social-box:before {
            position: absolute;
            min-height: 60px;
            content: "";
            left: -170px;
            top: 0;
            right: -1200px;
            bottom: 0;
            transform: skewX(-30deg);
            background-color: #df0303;
        }

        .main-header .header-top .social-box li {
            margin-left: 25px;
        }

        .main-header .header-lower,
        .main-header .header-upper {
            position: relative;
            background-color: #fff;
        }

        .main-header .logo-box {
            position: relative;
            float: left;
            z-index: 10;
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

        .main-header .main-menu {
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
            position: relative;
            float: left;
            margin-right: 35px;
        }

        .main-header .main-menu .navigation > li > a {
            position: relative;
            display: block;
            padding: 35px 0;
            color: #222;
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
            background-color: #df0303;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
        }

        .main-header .main-menu .navigation > li:hover > ul {
            visibility: visible;
            opacity: 1;
            transform: scaleY(1);
        }

        .main-header .main-menu .navigation > li > ul > li > a {
            display: block;
            padding: 12px 18px;
            color: #fff;
            font-size: 15px;
            line-height: 24px;
        }

        .main-header .nav-outer .mobile-nav-toggler {
            position: relative;
            float: right;
            width: 44px;
            height: 44px;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 2px 0 0;
            color: #000;
            font-size: 26px;
            line-height: 44px;
            cursor: pointer;
        }

        .main-header .nav-outer .mobile-nav-toggler .icon:before {
            content: "\2630";
            font-family: Arial, sans-serif;
        }

        .mobile-menu {
            position: fixed;
            right: 0;
            top: 0;
            width: 300px;
            max-width: 100%;
            height: 100%;
            visibility: hidden;
            z-index: 999999;
        }

        .movie-page-section {
            position: relative;
            padding: 110px 0 80px;
        }

        .movie-page-section .filters {
            position: relative;
            margin-bottom: 60px;
            text-align: center;
        }

        .movie-page-section .filters li {
            position: relative;
            display: inline-block;
            cursor: pointer;
            color: #010101;
            font-size: 16px;
            font-weight: 600;
            margin: 0 14px 12px;
            text-transform: capitalize;
        }

        .movie-page-section .filters li.active,
        .movie-page-section .filters li:hover {
            color: #df0303;
        }

        .movie-page-section .feature-block {
            position: relative;
            width: 20%;
            padding: 0 15px;
        }

        .movie-page-section .feature-block .inner-box {
            position: relative;
            margin-bottom: 30px;
        }

        .movie-page-section .feature-block .image {
            position: relative;
            overflow: hidden;
            background: #f2f4f8;
        }

        .movie-page-section .feature-block .image img {
            display: block;
            width: 100%;
            height: auto;
        }

        .movie-page-section .feature-block .lower-content {
            position: relative;
            padding-top: 18px;
        }

        .movie-page-section .feature-block h6 {
            margin: 0;
            font-size: 16px;
            line-height: 1.35;
            font-weight: 600;
        }

        .movie-page-section .feature-block h6 a {
            color: #010101;
        }

        .movie-page-section .feature-block .year {
            color: #df0303;
            font-size: 14px;
            font-weight: 600;
        }

        @media (max-width: 1023px) {
            .main-header .nav-outer {
                width: 100%;
                padding-top: 0;
            }

            .main-header .main-menu .navigation > li {
                margin-right: 25px;
            }

            .main-header .main-menu .navigation > li > a {
                padding: 20px 0;
                font-size: 14px;
            }

            .movie-page-section .feature-block {
                width: 33.3333%;
            }
        }

        @media (max-width: 767px) {
            .main-header .main-menu {
                display: none;
            }

            .main-header .nav-outer .mobile-nav-toggler {
                display: inline-flex;
                margin: 0 0 0 20px;
                padding: 8px 0;
            }

            .main-header .logo-box {
                padding-bottom: 15px;
            }

            .movie-page-section {
                padding: 70px 0 50px;
            }

            .movie-page-section .feature-block {
                width: 50%;
            }

            .movie-page-section .search-bar form {
                flex-wrap: wrap;
                gap: 10px;
            }

            .movie-page-section .search-bar .form-control,
            .movie-page-section .search-bar .btn {
                width: 100%;
                margin-left: 0;
                margin-right: 0;
            }
        }

        @media (max-width: 479px) {
            .movie-page-section .feature-block {
                width: 100%;
            }
        }
    </style>
@endif
@if (in_array($routeName, $staticBelowFoldRoutes, true))
    <style>
        .services-section-three,
        .trial-cta,
        .internet-section-three {
            content-visibility: auto;
            contain-intrinsic-size: 800px;
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

@if ($isMoviesRoute)
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/site-critical.css') }}" media="print" onload="this.media='all'">
@else
    @vite('resources/css/site-critical.css')
@endif
@if ($needsCheckoutCss)
    @if ($needsBlockingCheckoutCss)
        <link rel="stylesheet" href="{{ Vite::asset('resources/css/checkout.css') }}" media="all">
    @else
        <link rel="stylesheet" href="{{ Vite::asset('resources/css/checkout.css') }}" media="print" onload="this.media='all'">
    @endif
@endif
<link rel="stylesheet" href="{{ Vite::asset('resources/css/site-deferred.css') }}" media="print" onload="this.media='all'">
@stack('styles')

{{-- Preload critical fonts to reduce CLS --}}
@if (in_array($routeName, $leanFontRoutes, true))
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
    @if ($isMoviesRoute)
        <link rel="stylesheet" href="{{ Vite::asset('resources/css/site-critical.css') }}">
    @endif
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/site-deferred.css') }}">
    @if ($needsCheckoutCss && !$needsBlockingCheckoutCss)
        <link rel="stylesheet" href="{{ Vite::asset('resources/css/checkout.css') }}">
    @endif
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
