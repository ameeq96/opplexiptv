<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title')</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/fav-icon.webp') }}">

    @if (!empty($movies[0]['webp_image_url']))
        <link rel="preload" as="image" href="{{ $movies[0]['webp_image_url'] }}" fetchpriority="high">
    @endif

    <link rel="preload" href="{{ asset('css/style.css') }}" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" as="style" crossorigin>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" media="all">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" media="all">

    @php
        $nonCriticalStyles = [
            'global.css', 'header.css', 'footer.css', 'font-awesome.css', 'flaticon.css',
            'animate.css', 'owl.css', 'swiper.css', 'linearicons.css', 'jquery-ui.css',
            'custom-animate.css', 'jquery.fancybox.min.css', 'jquery.mCustomScrollbar.min.css'
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

    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        document.addEventListener("DOMContentLoaded", function () {
            let hasLoaded = false;

            function loadGTM() {
                if (hasLoaded) return;
                hasLoaded = true;

                let script = document.createElement("script");
                script.src = "https://www.googletagmanager.com/gtag/js?id=G-L98JG9ZT7H";
                script.async = true;
                document.head.appendChild(script);

                script.onload = function () {
                    gtag('js', new Date());
                    gtag('config', 'G-L98JG9ZT7H');
                };
            }

            ['scroll', 'mousemove', 'touchstart'].forEach(event =>
                window.addEventListener(event, loadGTM, { once: true })
            );
        });
    </script>
</head>
