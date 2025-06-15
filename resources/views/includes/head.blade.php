<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title')</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/fav-icon.webp') }}">

<style>
    .xmobile-slider {
        width: 100%;
        max-width: 428px;
        margin: auto;
        position: relative;
        overflow: hidden;
    }

    input[type="radio"] {
        display: none;
    }

    .xslide-frame {
        width: 100%;
        flex-shrink: 0;
        position: relative;
    }

    .xslide-frame img {
        width: 100%;
        height: auto;
    }

    .xslide-text {
        position: absolute;
        bottom: 10px;
        left: 10px;
        right: 10px;
        color: #fff;
        padding: 10px;
        border-radius: 6px;
    }

    .xslide-text h5 {
        margin: 0 0 5px;
        font-size: 1rem;
        color: #fff;
    }

    .xslide-btn,
    .xslide-text p {
        color: #fff;
        font-size: 0.85rem;
    }

    .xslide-text p {
        margin: 0 0 10px;
    }

    .xslide-btn {
        background: #df0303;
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
    }

    .xslide-dots {
        text-align: center;
        margin-top: 8px;
    }

    .xslide-wrapper {
        display: flex;
        transition: transform 0.6s ease-in-out;
    }

    .xslide-wrapper.autoplay-track {
        animation: 10s ease-in-out infinite autoplaySlider;
    }

    @keyframes autoplaySlider {

        0%,
        100%,
        20%,
        75% {
            transform: translateX(0);
        }

        25%,
        45% {
            transform: translateX(-100%);
        }

        50%,
        70% {
            transform: translateX(-200%);
        }
    }
</style>
<script>
    window.addEventListener('load', () => {
        setTimeout(() => {
            document.getElementById('xslider-autoplay').classList.add('autoplay-track');
        }, 3000);
    });
</script>
<link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" as="style"
    onload="this.onload=null;this.rel='stylesheet'">

<link rel="preload" href="{{ asset('css/style.css') }}" as="style">

<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<noscript>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</noscript>

<!-- Preload CSS files for faster load -->
<link rel="preload" href="{{ asset('css/global.css') }}" as="style">
<link rel="preload" href="{{ asset('css/header.css') }}" as="style">
<link rel="preload" href="{{ asset('css/footer.css') }}" as="style">
<link rel="preload" href="{{ asset('css/font-awesome.css') }}" as="style">
<link rel="preload" href="{{ asset('css/flaticon.css') }}" as="style">
<link rel="preload" href="{{ asset('css/animate.css') }}" as="style">
<link rel="preload" href="{{ asset('css/owl.css') }}" as="style">
<link rel="preload" href="{{ asset('css/swiper.css') }}" as="style">
<link rel="preload" href="{{ asset('css/linearicons.css') }}" as="style">
<link rel="preload" href="{{ asset('css/jquery-ui.css') }}" as="style">
<link rel="preload" href="{{ asset('css/custom-animate.css') }}" as="style">
<link rel="preload" href="{{ asset('css/jquery.fancybox.min.css') }}" as="style">
<link rel="preload" href="{{ asset('css/jquery.mCustomScrollbar.min.css') }}" as="style">
<link rel="preload" as="image" href="images/background/pattern-4.webp" as="style">
@if (!empty($displayMovies[0]['webp_image_url']))
    <link rel="preload" as="image" href="{{ $displayMovies[0]['webp_image_url'] }}" />
@endif

<!-- Apply the CSS files -->
<link rel="stylesheet" href="{{ asset('css/global.css') }}">
<link rel="stylesheet" href="{{ asset('css/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/footer.css') }}">
<link rel="stylesheet" href="{{ asset('css/font-awesome.css') }}">
<link rel="stylesheet" href="{{ asset('css/flaticon.css') }}">
<link rel="stylesheet" href="{{ asset('css/animate.css') }}">
<link rel="stylesheet" href="{{ asset('css/owl.css') }}">
<link rel="stylesheet" href="{{ asset('css/swiper.css') }}">
<link rel="stylesheet" href="{{ asset('css/linearicons.css') }}">
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<link rel="stylesheet" href="{{ asset('css/custom-animate.css') }}">
<link rel="stylesheet" href="{{ asset('css/jquery.fancybox.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/jquery.mCustomScrollbar.min.css') }}">
@if (!empty($movies[0]['webp_image_url']))
    <link rel="stylesheet" as="image" href="{{ $movies[0]['webp_image_url'] }}" />
@endif

<noscript>
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.css') }}">
    <link rel="stylesheet" href="{{ asset('css/swiper.css') }}">
    <link rel="stylesheet" href="{{ asset('css/linearicons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom-animate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery.fancybox.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery.mCustomScrollbar.min.css') }}">
    @if (!empty($movies[0]['webp_image_url']))
        <link rel="stylesheet" as="image" href="{{ $movies[0]['webp_image_url'] }}" />
    @endif
</noscript>


<link href="{{ asset('css/responsive.css') }}" rel="stylesheet">
<link href="{{ asset('css/fonts.css') }}" rel="stylesheet">

<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    document.addEventListener("DOMContentLoaded", function() {
        let hasGTMLoaded = false;

        function loadGTM() {
            if (!hasGTMLoaded) {
                hasGTMLoaded = true;
                let script = document.createElement("script");
                script.src = "https://www.googletagmanager.com/gtag/js?id=G-L98JG9ZT7H";
                script.async = true;
                document.head.appendChild(script);

                script.onload = function() {
                    gtag('js', new Date());
                    gtag('config', 'G-L98JG9ZT7H');
                };
            }
        }

        window.addEventListener("scroll", loadGTM, {
            once: true
        });
        window.addEventListener("mousemove", loadGTM, {
            once: true
        });
        window.addEventListener("touchstart", loadGTM, {
            once: true
        });
    });
</script>
