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

    {{-- Translatable Page Title --}}
    <title>@yield('title', __('messages.site_title'))</title>

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
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        document.addEventListener("DOMContentLoaded", function() {
            let hasLoaded = false;

            function loadGTM() {
                if (hasLoaded) return;
                hasLoaded = true;

                let script = document.createElement("script");
                script.src = "https://www.googletagmanager.com/gtag/js?id=G-L98JG9ZT7H";
                script.async = true;
                document.head.appendChild(script);

                script.onload = function() {
                    gtag('js', new Date());
                    gtag('config', 'G-L98JG9ZT7H');
                };
            }

            ['scroll', 'mousemove', 'touchstart'].forEach(event =>
                window.addEventListener(event, loadGTM, {
                    once: true
                })
            );
        });
    </script>
</head>
