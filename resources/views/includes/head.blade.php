<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="theme-color" content="#010c3a">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

@php
    use Illuminate\Support\Facades\Vite;
    use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

    if (!function_exists('v')) {
        function v(string $path) {
            $rel  = ltrim($path, '/');
            $full = public_path($rel);
            $ver  = is_file($full) ? filemtime($full) : null;

            return asset($rel) . ($ver ? '?v=' . $ver : '');
        }
    }

    $route = Request::route() ? Request::route()->getName() : 'home';
    $locale = app()->getLocale();
    $meta = trans("meta.$route");
    $metaTitle = $meta['title'] ?? __('interface.common.default_meta_title');
    $metaDescription = $meta['description'] ?? __('interface.common.default_meta_description');
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
    $isMoviesRoute = $routeName === 'movies';
    $noindexRoutes = ['redirect.ad', 'checkout', 'configure', 'thankyou', 'digital.checkout.show', 'digital.cart.index'];

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

    $phoneAssetRoutes = ['checkout', 'digital.checkout.show', 'buynow', 'buynowpanel'];
    $checkoutCssRoutes = ['checkout', 'configure', 'digital.checkout.show', 'digital.checkout.store'];
    $needsPhoneAssets = in_array($routeName, $phoneAssetRoutes, true);
    $needsBlockingCheckoutCss = in_array($routeName, $checkoutCssRoutes, true);

    $pageTitleLcpBackgrounds = [
        'about' => ['images/background/7-lcp.webp', 'images/background/7-mobile.webp'],
        'packages' => ['images/background/9-lcp.webp', 'images/background/9-mobile.webp'],
        'iptv-subscription-service' => ['images/background/9-lcp.webp', 'images/background/9-mobile.webp'],
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
    $pageTitleCriticalRoutes = ['about', 'contact', 'reseller-panel', 'pricing', 'shop', 'blogs.index'];
    $staticBelowFoldRoutes = ['about', 'reseller-panel', 'pricing'];
    $leanFontRoutes = array_merge(['packages', 'faqs'], $pageTitleCriticalRoutes, ['movies']);
@endphp

@if ($routeName === 'home' && empty($isMobile) && !empty($displayMovies[0]['webp_image_url'] ?? null))
    <link rel="preconnect" href="https://image.tmdb.org" crossorigin>
    <link rel="preload" as="image" href="{{ $displayMovies[0]['webp_image_url'] }}" fetchpriority="high">
@endif
@if ($routeName === 'home')
    <link rel="preload" as="image" href="{{ asset('images/resource/movie-night-tv-1024.webp') }}"
        imagesrcset="{{ asset('images/resource/movie-night-tv-480.webp') }} 480w,
            {{ asset('images/resource/movie-night-tv-720.webp') }} 720w,
            {{ asset('images/resource/movie-night-tv-1024.webp') }} 1024w,
            {{ asset('images/resource/movie-night-tv-1280.webp') }} 1280w"
        imagesizes="(min-width: 1340px) 640px, (min-width: 992px) calc(50vw - 30px), calc(100vw - 30px)"
        fetchpriority="high">
    {{-- Discover homepage fonts before parsing the large inline critical-style block. --}}
    <link rel="preload" href="{{ Vite::asset('public/fonts/poppins/poppins-v21-latin-regular.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('public/fonts/poppins/poppins-v21-latin-700.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('public/fonts/poppins/poppins-v21-latin-600.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('public/fonts/fontawesome-webfont.woff2') }}?v=4.3.0" as="font" type="font/woff2" crossorigin fetchpriority="low">
    <link rel="preload" href="{{ Vite::asset('public/fonts/flaticon.woff') }}" as="font" type="font/woff" crossorigin media="(max-width: 767px)" fetchpriority="low">
    <link rel="preload" href="{{ Vite::asset('public/fonts/Linearicons-Free.woff2') }}" as="font" type="font/woff2" crossorigin media="(min-width: 768px)" fetchpriority="low">
@endif
@if ($isMoviesRoute)
    <link rel="preconnect" href="https://image.tmdb.org" crossorigin>
@endif

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">

@if ($shouldNoindex)
    <meta name="robots" content="noindex,follow">
@else
    <meta name="robots" content="index,follow">
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

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
            max-width: 1340px;
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
            max-width: 1340px;
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
            max-width: 1340px;
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

        :root {
            --main-color: #df0303;
            --main-color-two: #010c3a;
            --heading-color: #010c3a;
            --dark-color: #222;
            --white-color: #fff;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Poppins, Arial, sans-serif;
            font-size: 14px;
            color: var(--dark-color);
            line-height: 1.6em;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: var(--main-color);
            text-decoration: none;
        }

        img {
            display: inline-block;
            max-width: 100%;
            height: auto;
        }

        ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
            color: var(--heading-color);
            font-weight: 600;
        }

        .h3 {
            font-size: 1.75rem;
            line-height: 1.3;
        }

        .h4 {
            font-size: 1.5rem;
            line-height: 1.2;
        }

        .page-wrapper {
            position: relative;
            width: 100%;
            min-width: 300px;
            margin: 0 auto;
        }

        .auto-container {
            position: static;
            max-width: 1340px;
            padding: 0 15px;
            margin: 0 auto;
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

        .align-items-center {
            align-items: center !important;
        }

        .justify-content-between {
            justify-content: space-between !important;
        }

        .justify-content-center {
            justify-content: center !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .w-100 {
            width: 100% !important;
        }

        .my-3 {
            margin-top: 1rem !important;
            margin-bottom: 1rem !important;
        }

        .mb-4 {
            margin-bottom: 1.5rem !important;
        }

        .mx-2 {
            margin-right: .5rem !important;
            margin-left: .5rem !important;
        }

        .px-4 {
            padding-right: 1.5rem !important;
            padding-left: 1.5rem !important;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
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
            user-select: none;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem;
            cursor: pointer;
        }

        .btn-search {
            background-color: #df0303;
            color: #fff;
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
            color: #fff;
        }

        .main-header .header-top .info li {
            margin-right: 20px;
        }

        .main-header .header-top .info li a,
        .main-header .header-top .social-box li a {
            color: #fff;
        }

        .main-header .header-top .social-box::before {
            position: absolute;
            min-height: 60px;
            content: "";
            left: -170px;
            top: 0;
            right: -1200px;
            bottom: 0;
            transform: skewX(-30deg);
            background-color: var(--main-color);
        }

        .main-header .header-top .social-box li {
            margin-left: 25px;
        }

        .main-header .header-lower .nav-outer,
        .main-header .logo-box {
            position: relative;
        }

        .main-header .logo-box {
            float: left;
            z-index: 10;
            padding: 10px 0;
        }

        .main-header .header-lower .nav-outer {
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
            text-align: center;
            line-height: 30px;
            font-weight: 600;
            padding: 35px 0;
            font-size: 14px;
            color: var(--dark-color);
            text-transform: capitalize;
        }

        .main-header .main-menu .navigation > li > ul {
            position: absolute;
            width: 15rem;
            border-radius: 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            background-color: var(--main-color);
            transform: scaleY(0);
            transform-origin: top;
            opacity: 0;
            visibility: hidden;
        }

        .main-header .main-menu .navigation > li.dropdown:hover > ul {
            transform: scaleY(1);
            opacity: 1;
            visibility: visible;
        }

        .main-header .main-menu .navigation > li > ul > li > a {
            position: relative;
            display: block;
            padding: 12px 18px;
            line-height: 24px;
            font-weight: 500;
            font-size: 15px;
            color: #fff;
            text-transform: capitalize;
        }

        .main-header .nav-outer .mobile-nav-toggler {
            position: relative;
            float: right;
            padding: 2px 0 0;
            font-size: 26px;
            line-height: 44px;
            cursor: pointer;
            color: #000;
            display: none;
        }

        .mobile-menu {
            position: fixed;
            right: 0;
            top: 0;
            width: 300px;
            padding-right: 30px;
            max-width: 100%;
            height: 100%;
            visibility: hidden;
            z-index: 999999;
        }

        .mobile-menu-visible {
            overflow: hidden;
        }

        .mobile-menu-visible .mobile-menu {
            visibility: visible;
        }

        .mobile-menu .menu-backdrop {
            position: fixed;
            right: 0;
            top: 0;
            width: 0;
            height: 100%;
            z-index: 1;
            background: rgba(0, 0, 0, .9);
            transform: translateX(101%);
            transition: .9s .3s;
        }

        .mobile-menu-visible .mobile-menu .menu-backdrop {
            width: 100%;
            visibility: visible;
            transform: translateX(0);
            transition: .9s;
        }

        .mobile-menu .menu-box {
            position: absolute;
            right: -400px;
            top: 0;
            width: 100%;
            height: 100%;
            max-height: 100%;
            overflow-y: auto;
            background: #fff;
            padding: 0;
            z-index: 5;
            border-radius: 0;
            transition: .9s;
        }

        .mobile-menu-visible .mobile-menu .menu-box {
            right: 0;
            transition-delay: .6s;
        }

        .mobile-menu .close-btn {
            position: absolute;
            right: 15px;
            top: 15px;
            line-height: 30px;
            width: 30px;
            text-align: center;
            font-size: 14px;
            color: #202020;
            cursor: pointer;
            z-index: 10;
            transform: translateY(-50px);
            transition: .5s;
        }

        .mobile-menu-visible .mobile-menu .close-btn {
            transform: translateY(0);
            transition-delay: .9s;
        }

        .mobile-menu .nav-logo {
            position: relative;
            padding: 20px;
            text-align: left;
        }

        .mobile-menu .nav-logo img {
            max-width: 200px;
        }

        .main-header .mobile-menu .navigation {
            position: relative;
            display: block;
            width: 100%;
            border-top: 1px solid #ddd;
        }

        .main-header .mobile-menu .navigation li {
            position: relative;
            display: block;
            border-bottom: 1px solid #ddd;
        }

        .main-header .mobile-menu .navigation li > a {
            position: relative;
            display: block;
            line-height: 24px;
            padding: 10px 20px;
            font-size: 16px;
            color: var(--dark-color);
            text-transform: capitalize;
        }

        .main-header .mobile-menu .navigation li > ul {
            display: none;
        }

        .main-header .mobile-menu .navigation li.dropdown .dropdown-btn {
            position: absolute;
            right: 0;
            top: 0;
            width: 44px;
            height: 44px;
            text-align: center;
            color: var(--dark-color);
            font-size: 16px;
            line-height: 44px;
            cursor: pointer;
            z-index: 5;
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

        .movie-page-section .feature-block {
            position: relative;
            width: 20%;
            padding: 0 15px;
        }

        .movie-page-section .filters li {
            position: relative;
            cursor: pointer;
            font-weight: 500;
            margin: 0 15px;
            display: inline-block;
            color: var(--dark-color);
            font-size: 18px;
            border-bottom: 1px solid transparent;
        }

        .movie-page-section .filters .filter.active,
        .movie-page-section .filters .filter:hover {
            color: var(--main-color);
            border-color: var(--main-color);
        }

        .feature-block {
            margin-bottom: 25px;
        }

        .feature-block .inner-box,
        .feature-block .inner-box .image {
            position: relative;
        }

        .feature-block .inner-box .image {
            border-radius: 3px;
            overflow: hidden;
        }

        .feature-block .inner-box .image img {
            position: relative;
            width: 100%;
            height: 300px;
            display: block;
            object-fit: cover;
        }

        .feature-block .inner-box .image .overlay-box {
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, .5);
        }

        .feature-block .inner-box .post-meta {
            position: absolute;
            right: 15px;
            top: 12px;
            z-index: 2;
        }

        .feature-block .inner-box .post-meta li {
            position: relative;
            margin-left: 10px;
            display: inline-block;
            font-size: 15px;
            color: #fff;
            padding-left: 25px;
        }

        .feature-block .inner-box .post-meta li .icon {
            position: absolute;
            left: 0;
            top: 0;
            font-size: 15px;
            color: var(--main-color);
        }

        .feature-block .inner-box .video-box {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            text-align: center;
            z-index: 2;
            transform: scale(0, 1);
            transform-origin: right center;
        }

        .feature-block .inner-box:hover .video-box {
            transform: scale(1, 1);
            transform-origin: left center;
        }

        .feature-block .inner-box .video-box::before {
            position: absolute;
            content: "";
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            opacity: .5;
            background-color: var(--main-color-two);
        }

        .feature-block .inner-box .video-box span {
            position: absolute;
            width: 70px;
            height: 70px;
            left: 50%;
            top: 50%;
            z-index: 10;
            color: var(--main-color);
            font-size: 20px;
            text-align: center;
            margin-top: -35px;
            margin-left: -35px;
            line-height: 66px;
            border-radius: 50%;
            border: 3px solid var(--main-color);
        }

        .feature-block .inner-box .lower-content {
            position: relative;
            padding-top: 20px;
        }

        .feature-block.style-two .inner-box h6 a {
            color: var(--heading-color);
        }

        .feature-block .inner-box .year {
            position: relative;
            font-weight: 700;
            font-size: 18px;
            color: var(--main-color);
            margin-top: 5px;
        }

        .scroll-to-top {
            position: fixed;
            bottom: 15px;
            right: 15px;
            width: 40px;
            height: 40px;
            color: var(--main-color);
            font-size: 16px;
            line-height: 38px;
            z-index: 100;
            background: #fff;
            display: none;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        }

        @media (max-width: 1023px) {
            .main-header {
                position: relative;
            }

            .main-header .nav-outer {
                width: 100%;
                padding-top: 0;
            }

            .main-header .main-menu .navigation > li {
                margin-right: 25px;
            }

            .main-header .main-menu .navigation > li > a {
                padding: 20px 0;
            }

            .movie-page-section .feature-block {
                width: 33.3333%;
            }
        }

        @media (max-width: 767px) {
            .main-header .main-menu {
                display: none;
            }

            .main-header .logo-box {
                padding-bottom: 15px;
            }

            .main-header .nav-outer .mobile-nav-toggler {
                display: block;
                margin: 0 0 0 20px;
                padding: 8px 0;
            }

            .movie-page-section .feature-block {
                width: 50%;
            }
        }

        @media (max-width: 479px) {
            .h3 {
                font-size: 1.6rem;
            }

            .movie-page-section .feature-block {
                width: 100%;
            }

            .movie-page-section .filters li {
                width: 100%;
                text-align: center;
                margin-right: 0;
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
        .mobile-menu .dropdown-btn {
            appearance: none;
            -webkit-appearance: none;
            border: 0;
            background: transparent;
            padding: 0;
            cursor: pointer;
        }

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

<meta property="og:site_name" content="Opplex IPTV">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:alt" content="{{ $ogTitle }}">
<meta property="og:locale" content="{{ str_replace('-', '_', $locale) }}">
@foreach ($supported as $altLocale)
    @if ($altLocale !== $locale)
        <meta property="og:locale:alternate" content="{{ str_replace('-', '_', $altLocale) }}">
    @endif
@endforeach
<meta name="facebook-domain-verification" content="rnsb3eqoa06k3dwo6gyqpphgu2imo2" />

<link rel="canonical" href="{{ $canonical }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@opplex_iptv">
<meta name="twitter:creator" content="@opplex_iptv">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">
<meta name="twitter:image:alt" content="{{ $ogTitle }}">

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

{{-- Structured data: site-wide brand entity graph (Organization + WebSite) --}}
{!! jsonld(seo()->globalGraph($footer['socials'] ?? [])) !!}
{{-- Per-page nodes (breadcrumbs, FAQ, HowTo, Service…) are pushed via @push('schema')
     and rendered by the single @stack('schema') just before </body> in layouts/default. --}}
@yield('jsonld')

<link rel="shortcut icon" href="{{ v('images/fav-icon.webp') }}" type="image/x-icon">
<link rel="apple-touch-icon" sizes="180x180" href="{{ v('images/apple-touch-icon.webp') }}">

@if ($pageTitleLcp)
    <link rel="preload" as="image" href="{{ asset($pageTitleLcp[0]) }}" type="image/webp"
        media="(min-width: 768px)" fetchpriority="high">
    <link rel="preload" as="image" href="{{ asset($pageTitleLcp[1]) }}" type="image/webp"
        media="(max-width: 767px)" fetchpriority="high">
@endif

{{-- Keep critical CSS cacheable across pages instead of duplicating it inside every HTML response. --}}
@if ($isMoviesRoute)
    <link rel="preload" href="{{ Vite::asset('resources/css/site-critical.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
@else
    @vite('resources/css/site-critical.css')
@endif
@if ($needsBlockingCheckoutCss)
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/checkout.css') }}">
@endif
@if ($isMoviesRoute)
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                var href = "{{ Vite::asset('resources/css/site-deferred.css') }}";
                if (document.querySelector('link[href="' + href + '"]')) return;
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                document.head.appendChild(link);
            }, 1500);
        });
    </script>
@else
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/site-deferred.css') }}" media="print" onload="this.media='all'">
@endif
@stack('styles')

{{-- Preload critical fonts to reduce CLS --}}
@unless ($routeName === 'home')
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
@endunless

@if ($needsPhoneAssets)
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/css/intlTelInput.css">
@endif

<noscript>
    @if ($isMoviesRoute)
        <link rel="stylesheet" href="{{ Vite::asset('resources/css/site-critical.css') }}">
    @endif
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/site-deferred.css') }}">
    @if ($needsPhoneAssets)
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/css/intlTelInput.css">
    @endif
</noscript>

<script>
    (function (w, d) {
        var consentVersion = @json(config('services.marketing.tracking_consent_version'));
        var gaId = @json(config('services.google.analytics_id'));
        var clarityId = @json(config('services.clarity.project_id'));
        var pixelIds = @json(array_values($fbPixels));
        var loaded = { ga: false, clarity: false, pixel: false };

        w.__trackingConsentVersion = consentVersion;
        w.__trackingConsent = { analytics: false, marketing: false };
        w.dataLayer = w.dataLayer || [];
        w.gtag = w.gtag || function () { w.dataLayer.push(arguments); };
        w.gtag('consent', 'default', {
            ad_storage: 'denied',
            analytics_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            wait_for_update: 500
        });

        function hasConsent(category) {
            return w.__trackingConsent && w.__trackingConsent[category] === true;
        }

        function loadGA() {
            if (!hasConsent('analytics') || loaded.ga || !gaId) return;
            loaded.ga = true;
            w.gtag('js', new Date());
            w.gtag('config', gaId, {
                allow_google_signals: hasConsent('marketing'),
                allow_ad_personalization_signals: hasConsent('marketing')
            });
            var script = d.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(gaId);
            (d.head || d.body).appendChild(script);
        }

        function loadClarity() {
            if (!hasConsent('analytics') || loaded.clarity || !clarityId) return;
            loaded.clarity = true;
            w.clarity = w.clarity || function () {
                (w.clarity.q = w.clarity.q || []).push(arguments);
            };
            var script = d.createElement('script');
            script.async = true;
            script.src = 'https://www.clarity.ms/tag/' + clarityId;
            var firstScript = d.getElementsByTagName('script')[0];
            firstScript.parentNode.insertBefore(script, firstScript);
        }

        function loadFBPixel() {
            if (!hasConsent('marketing') || loaded.pixel || !pixelIds.length) return;
            loaded.pixel = true;
            if (!w.fbq) {
                var fbq = w.fbq = function () {
                    fbq.callMethod ? fbq.callMethod.apply(fbq, arguments) : fbq.queue.push(arguments);
                };
                w._fbq = fbq;
                fbq.push = fbq;
                fbq.loaded = false;
                fbq.version = '2.0';
                fbq.queue = [];
            }
            pixelIds.forEach(function (id) { w.fbq('init', id); });
            w.fbq('track', 'PageView');
            var script = d.createElement('script');
            script.async = true;
            script.src = 'https://connect.facebook.net/en_US/fbevents.js';
            var firstScript = d.getElementsByTagName('script')[0];
            firstScript.parentNode.insertBefore(script, firstScript);
        }

        w.__hasTrackingConsent = hasConsent;
        w.__applyTrackingConsent = function (preference) {
            var next = {
                analytics: preference && preference.analytics === true,
                marketing: preference && preference.marketing === true
            };
            w.__trackingConsent = next;
            w.gtag('consent', 'update', {
                analytics_storage: next.analytics ? 'granted' : 'denied',
                ad_storage: next.marketing ? 'granted' : 'denied',
                ad_user_data: next.marketing ? 'granted' : 'denied',
                ad_personalization: next.marketing ? 'granted' : 'denied'
            });
            if (next.analytics) {
                loadGA();
                loadClarity();
            }
            if (next.marketing) loadFBPixel();
        };

        w.__loadConversionTracking = function () {
            loadGA();
            loadFBPixel();
        };

        w.trackMarketingEvent = function (name, params, metaEvent) {
            if (typeof name !== 'string' || name.trim() === '') return;
            if (!hasConsent('analytics') && !hasConsent('marketing')) return;

            var eventParams = params && typeof params === 'object' ? params : {};
            if (hasConsent('analytics')) {
                loadGA();
                try { w.gtag('event', name, eventParams); } catch (e) {}
            }

            if (!metaEvent || !hasConsent('marketing')) return;
            loadFBPixel();
            try {
                var metaParams = Object.assign({}, eventParams);
                var eventId = metaParams.event_id;
                delete metaParams.event_id;
                if (eventId) {
                    w.fbq('track', metaEvent, metaParams, { eventID: String(eventId) });
                } else {
                    w.fbq('track', metaEvent, metaParams);
                }
            } catch (e) {}
        };

        function readSavedPreference() {
            var raw = null;
            try { raw = w.localStorage.getItem('opplex.cookieConsent'); } catch (e) {}
            if (!raw) {
                var match = d.cookie.match(/(?:^|; )opplex_consent=([^;]*)/);
                if (match) {
                    try { raw = decodeURIComponent(match[1]); } catch (e) {}
                }
            }
            if (!raw) return null;
            try {
                var saved = JSON.parse(raw);
                if (saved.version !== consentVersion) return null;
                if (typeof saved.analytics !== 'boolean' || typeof saved.marketing !== 'boolean') return null;
                return saved;
            } catch (e) {
                return null;
            }
        }

        var savedPreference = readSavedPreference();
        if (savedPreference) w.__applyTrackingConsent(savedPreference);
    })(window, document);

    (function () {
        if (!('PerformanceObserver' in window)) return;

        var metrics = { LCP: 0, INP: 0, CLS: 0 };
        var sent = {};

        function rating(name, value) {
            var limits = name === 'LCP' ? [2500, 4000] : (name === 'INP' ? [200, 500] : [0.1, 0.25]);
            return value <= limits[0] ? 'good' : (value <= limits[1] ? 'needs-improvement' : 'poor');
        }

        function report(name) {
            if (sent[name] || !metrics[name]) return;
            sent[name] = true;
            window.trackMarketingEvent('web_vital', {
                metric_name: name,
                metric_value: Number(metrics[name].toFixed(name === 'CLS' ? 3 : 0)),
                metric_rating: rating(name, metrics[name]),
                page_path: location.pathname,
                non_interaction: true
            });
        }

        try {
            new PerformanceObserver(function (list) {
                var entries = list.getEntries();
                if (entries.length) metrics.LCP = entries[entries.length - 1].startTime;
            }).observe({ type: 'largest-contentful-paint', buffered: true });
        } catch (e) {}

        try {
            new PerformanceObserver(function (list) {
                list.getEntries().forEach(function (entry) {
                    if (!entry.hadRecentInput) metrics.CLS += entry.value;
                });
            }).observe({ type: 'layout-shift', buffered: true });
        } catch (e) {}

        try {
            new PerformanceObserver(function (list) {
                list.getEntries().forEach(function (entry) {
                    if (entry.interactionId && entry.duration > metrics.INP) metrics.INP = entry.duration;
                });
            }).observe({ type: 'event', buffered: true, durationThreshold: 40 });
        } catch (e) {}

        function reportVitals() {
            report('LCP');
            report('INP');
            report('CLS');
        }

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') reportVitals();
        });
        window.addEventListener('pagehide', reportVitals, { once: true });
    })();
</script>

<script>
    // --------- WhatsApp click tracking + trial CAPI beacon ---------
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
            try {
                const url = new URL(href, window.location.href);
                const hostname = url.hostname.toLowerCase().replace(/^www\./, '');
                return url.protocol === 'whatsapp:'
                    || hostname === 'wa.me'
                    || hostname === 'whatsapp.com'
                    || hostname.endsWith('.whatsapp.com');
            } catch (e) {
                return href.toLowerCase().startsWith('whatsapp://send');
            }
        }

        function closestAttribute(el, attribute) {
            const owner = el.closest('[' + attribute + ']');
            return owner ? (owner.getAttribute(attribute) || '') : '';
        }

        function whatsappPlacement(el) {
            const explicit = closestAttribute(el, 'data-whatsapp-placement')
                || closestAttribute(el, 'data-wa-placement');
            if (explicit) return explicit;
            if (el.id === 'dw-copy') return 'discount_claim';
            if (el.classList.contains('whatsapp-icon')) return 'floating_button';

            const region = el.closest('header, footer, section[id], [role="dialog"][id], [id]');
            if (!region) return 'page_content';
            if (region.tagName === 'HEADER') return 'header';
            if (region.tagName === 'FOOTER') return 'footer';
            return region.id || 'page_content';
        }

        function readCookie(name) {
            const value = '; ' + document.cookie;
            const parts = value.split('; ' + name + '=');
            if (parts.length !== 2) return null;
            return decodeURIComponent(parts.pop().split(';').shift() || '');
        }

        function sendCAPI(eventId, dest) {
            if (!window.__hasTrackingConsent || !window.__hasTrackingConsent('marketing')) return;
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
            const el = e.target.closest('a[href], button[data-wa-href], [data-whatsapp-button], #dw-copy');
            if (!el) return;
            const href = el.tagName === 'A' ? el.getAttribute('href') : el.getAttribute('data-wa-href');
            const isDynamicWhatsAppButton = el.hasAttribute('data-whatsapp-button') || el.id === 'dw-copy';
            if (!isDynamicWhatsAppButton && (!href || !isWhatsApp(href))) return;

            const rawValue = closestAttribute(el, 'data-whatsapp-value')
                || closestAttribute(el, 'data-wa-value')
                || closestAttribute(el, 'data-price');
            const parsedValue = Number.parseFloat(rawValue);
            const plan = closestAttribute(el, 'data-whatsapp-package')
                || closestAttribute(el, 'data-wa-plan')
                || closestAttribute(el, 'data-plan');
            const intent = closestAttribute(el, 'data-whatsapp-intent') || 'contact';
            const packageName = plan || (intent === 'trial' ? 'free_trial' : 'not_applicable');
            const currency = closestAttribute(el, 'data-whatsapp-currency')
                || closestAttribute(el, 'data-wa-currency')
                || "{{ $currency }}";
            const placement = whatsappPlacement(el);

            try {
                window.trackMarketingEvent('whatsapp_click', {
                    page_path: window.location.pathname,
                    placement: placement,
                    package: packageName,
                    plan: packageName,
                    intent: intent,
                    value: Number.isFinite(parsedValue) ? parsedValue : 0,
                    currency: currency,
                    contact_channel: 'whatsapp'
                });
            } catch(e) { /* analytics queues may not be available yet */ }

            if (!el.hasAttribute('data-trial')) return;

            const eventId = uuidv4();
            try {
                window.trackMarketingEvent('generate_lead', {
                    value: 0, currency: "{{ $currency }}",
                    content_name: 'WhatsApp', contact_channel: 'whatsapp', destination: href,
                    event_id: eventId
                }, 'StartTrial');
            } catch(e) { /* analytics queues may not be available yet */ }

            sendCAPI(eventId, href);

            if (el.tagName === 'BUTTON') {
                e.preventDefault();
                setTimeout(function () { window.open(href, '_blank', 'noopener'); }, 50);
            }
        }, { passive:true });
    })();
</script>
