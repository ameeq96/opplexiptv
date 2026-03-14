@extends('layouts.default')
@section('title', __('messages.site_title'))
@push('styles')
<style>
    .skeleton-section {
        position: relative;
        overflow: hidden;
    }

    .skeleton-section:not(.is-loaded) > *:not(.section-skeleton__overlay) {
        opacity: 0;
        visibility: hidden;
    }

    .skeleton-section.is-loaded > *:not(.section-skeleton__overlay) {
        opacity: 1;
        visibility: visible;
        transition: opacity .35s ease;
    }

    .section-skeleton__overlay {
        position: absolute;
        inset: 0;
        z-index: 6;
        pointer-events: none;
        background: linear-gradient(135deg, rgba(8, 15, 28, .96) 0%, rgba(16, 24, 48, .92) 100%);
    }

    .section-skeleton__overlay::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, .12) 48%, transparent 100%);
        transform: translateX(-100%);
        animation: sectionSkeletonShimmer 1.5s linear infinite;
    }

    .section-skeleton__content {
        position: relative;
        height: 100%;
        padding: 32px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .section-skeleton__pill,
    .section-skeleton__line,
    .section-skeleton__card,
    .section-skeleton__button {
        display: block;
        border-radius: 999px;
        background: rgba(255, 255, 255, .14);
    }

    .section-skeleton__pill {
        width: 110px;
        height: 12px;
    }

    .section-skeleton__line {
        height: 16px;
        width: 100%;
        max-width: 520px;
        border-radius: 10px;
    }

    .section-skeleton__line--lg {
        height: 22px;
        max-width: 620px;
    }

    .section-skeleton__line--md {
        max-width: 420px;
    }

    .section-skeleton__button {
        width: 220px;
        height: 50px;
        margin-top: 10px;
        border-radius: 14px;
    }

    .section-skeleton__cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 22px;
        margin-top: 16px;
    }

    .section-skeleton__card {
        height: 180px;
        border-radius: 22px;
    }

    .skeleton-section--hero {
        min-height: 560px;
    }

    .skeleton-section--hero .section-skeleton__content {
        justify-content: center;
        padding: 70px 7vw;
    }

    .skeleton-section--hero .section-skeleton__cards {
        display: none;
    }

    .skeleton-section--pricing .section-skeleton__cards,
    .skeleton-section--products .section-skeleton__cards {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .skeleton-section--pricing .section-skeleton__card {
        height: 360px;
    }

    .skeleton-section--products .section-skeleton__card {
        height: 300px;
    }

    .skeleton-section--services .section-skeleton__card {
        height: 220px;
    }

    .skeleton-section--testimonials .section-skeleton__card {
        height: 260px;
    }

    .skeleton-section--logos .section-skeleton__cards {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .skeleton-section--logos .section-skeleton__card {
        height: 90px;
        border-radius: 18px;
    }

    .skeleton-section--cta .section-skeleton__content {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }

    .skeleton-section--cta .section-skeleton__cards {
        display: none;
    }

    .skeleton-section--cta .section-skeleton__meta {
        display: flex;
        flex-direction: column;
        gap: 14px;
        flex: 1 1 auto;
    }

    .skeleton-section--cta .section-skeleton__button {
        margin-top: 0;
        width: 280px;
        flex: 0 0 auto;
    }

    .skeleton-section.is-loaded .section-skeleton__overlay {
        opacity: 0;
        visibility: hidden;
        transition: opacity .28s ease, visibility .28s ease;
    }

    @keyframes sectionSkeletonShimmer {
        100% {
            transform: translateX(100%);
        }
    }

    .native-carousel {
        --native-gap: 30px;
        --native-items: 1;
        position: relative;
    }

    .native-carousel__viewport {
        overflow: hidden;
    }

    .native-carousel__track {
        display: flex;
        gap: var(--native-gap);
        transition: transform .55s ease;
        will-change: transform;
    }

    .native-carousel__slide {
        min-width: 0;
        flex: 0 0 calc((100% - (var(--native-gap) * (var(--native-items) - 1))) / var(--native-items));
    }

    .native-carousel__arrow {
        width: 42px;
        height: 42px;
        border: 1px solid #e6ebf3;
        border-radius: 999px;
        background: #ffffff;
        color: #0b1526;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 24px rgba(17, 27, 46, .12);
        transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease;
    }

    .native-carousel__arrow:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px rgba(17, 27, 46, .16);
    }

    .native-carousel__arrow.is-hidden {
        opacity: .45;
    }

    .native-carousel--hero {
        height: 100%;
    }

    .native-carousel--hero .native-carousel__viewport,
    .native-carousel--hero .native-carousel__track,
    .native-carousel--hero .native-carousel__slide {
        height: 100%;
    }

    .native-carousel--hero .native-carousel__viewport {
        position: relative;
    }

    .native-carousel--hero .native-carousel__track {
        display: block;
    }

    .native-carousel--hero .native-carousel__slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        visibility: hidden;
        transition: opacity .7s ease, visibility .7s ease;
    }

    .native-carousel--hero .native-carousel__slide.is-active {
        opacity: 1;
        visibility: visible;
        z-index: 1;
    }

    .native-home-hero .slide::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(8, 15, 28, .85) 0%, rgba(8, 15, 28, .52) 40%, rgba(8, 15, 28, .2) 100%);
        z-index: 0;
    }

    .native-home-hero .slide {
        position: absolute;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .native-home-hero .slide .auto-container {
        position: relative;
        z-index: 1;
    }

    .native-home-hero .inner-box > * {
        animation: nativeHeroReveal .8s ease both;
    }

    .native-home-hero .native-carousel__arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
    }

    .native-home-hero .native-carousel__arrow:hover {
        transform: translateY(-50%) scale(1.03);
    }

    .native-home-hero .native-carousel__arrow--prev {
        left: 24px;
    }

    .native-home-hero .native-carousel__arrow--next {
        right: 24px;
    }

    .home-products-shell {
        position: relative;
    }

        .home-products-headline {
        display: inline-block;
        width: 92px;
        height: 6px;
        border-radius: 999px;
        margin-bottom: 14px;
        background: linear-gradient(90deg, #df0303 0%, #ff4d4d 100%);
    }

    .home-products-filter {
        display: inline-flex;
        background: #ffffff;
        border-radius: 999px;
        padding: 4px;
        gap: 4px;
        border: 1px solid #e8edf5;
        box-shadow: 0 8px 20px rgba(12, 22, 38, 0.08);
    }
    .home-products-filter a {
        text-decoration: none !important;
        border-radius: 999px;
        padding: 8px 15px;
        font-weight: 600;
        font-size: 12px;
        color: #4b4b4b;
        transition: all .2s ease;
    }
    .home-products-filter a:hover {
        color: #0a67ff;
    }
    .home-products-filter a:first-child,
    .home-products-filter .is-active {
        color: #fff;
        background: linear-gradient(90deg, #0454f7, #0a67ff);
    }
    .home-product-card {
        background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
        border-radius: 18px;
        border: 1px solid #e9edf3;
        overflow: hidden;
        box-shadow: 0 10px 26px rgba(17, 27, 46, .08);
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }
    .home-product-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 36px rgba(17, 27, 46, .14);
        border-color: rgba(4, 84, 247, 0.24);
    }
    .home-product-media { display:block; height:270px; background:#eef2f8; }
    .home-product-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }
    .home-product-body { padding:14px 14px 15px; }
    .home-product-title {
        font-size: 19px;
        line-height: 1.25;
        margin-bottom: 6px;
        font-weight: 700;
        min-height: 48px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .home-product-title a { color:#141414; text-decoration:none; }
    .home-product-title a:hover { color:#0454f7; }
    .home-product-price {
        font-size:20px;
        font-weight:800;
        color:#0f172a;
        margin-bottom:10px;
        letter-spacing: .1px;
    }
    .home-product-badge { border-radius:999px; padding:4px 10px; font-size:11px; font-weight:700; }
    .home-product-badge--digital { color:#065f46; background:#d1fae5; }
    .home-product-badge--affiliate { color:#374151; background:#e5e7eb; }
    .home-product-action {
        width:100%;
        border-radius:11px;
        font-size:13px;
        font-weight:700;
        padding:10px 12px;
        letter-spacing:.2px;
    }
    .home-products-carousel {
        --native-gap: 0px;
    }

    .home-products-carousel .native-carousel__viewport {
        padding: 6px 2px 16px;
    }

    .home-products-carousel .native-carousel__arrow {
        margin-top: 0;
        gap: 10px;
        position: absolute;
        right: 0;
        top: -78px;
        z-index: 3;
    }

    .home-products-carousel .native-carousel__arrow--prev {
        right: 46px;
    }

    .home-products-carousel .native-carousel__arrow--next {
        right: 0;
    }

    [dir="rtl"] .home-products-carousel .native-carousel__arrow--prev,
    [dir="rtl"] .home-products-carousel .native-carousel__arrow--next {
        right: auto;
    }

    [dir="rtl"] .home-products-carousel .native-carousel__arrow--prev {
        left: 46px;
    }

    [dir="rtl"] .home-products-carousel .native-carousel__arrow--next {
        left: 0;
    }

    .native-carousel--services,
    .native-carousel--testimonials,
    .native-carousel--logos {
        padding: 0 58px;
    }

    .native-carousel--services .native-carousel__arrow,
    .native-carousel--testimonials .native-carousel__arrow,
    .native-carousel--logos .native-carousel__arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
    }

    .native-carousel--services .native-carousel__arrow--prev,
    .native-carousel--testimonials .native-carousel__arrow--prev,
    .native-carousel--logos .native-carousel__arrow--prev {
        left: 0;
    }

    .native-carousel--services .native-carousel__arrow--next,
    .native-carousel--testimonials .native-carousel__arrow--next,
    .native-carousel--logos .native-carousel__arrow--next {
        right: 0;
    }

    .native-carousel--logos .native-carousel__slide {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .native-carousel--logos .image-box {
        width: 100%;
        display: flex;
        justify-content: center;
    }

    .native-carousel--testimonials .testimonial-block,
    .native-carousel--services .service-block-two {
        height: 100%;
    }

    .native-carousel--testimonials .testimonial-block .inner-box,
    .native-carousel--services .service-block-two .inner-box {
        height: 100%;
    }

    .native-carousel--testimonials .testimonial-block .inner-box {
        display: flex;
        flex-direction: column;
    }

    .native-carousel--testimonials .testimonial-block .upper-box {
        flex: 1 1 auto;
        display: flex;
    }

    .native-carousel--testimonials .testimonial-block .upper-box .text {
        width: 100%;
    }

    .native-carousel--testimonials .testimonial-block .lower-box {
        margin-top: auto;
    }

    .native-carousel--services .service-block-two .inner-box {
        padding: 18px 16px;
        min-height: 235px;
    }

    .native-carousel--services .service-block-two h4 {
        margin-bottom: 8px;
        font-size: 19px;
        line-height: 1.25;
    }

    .native-carousel--services .service-block-two .text {
        margin-bottom: 10px;
        font-size: 14px;
        line-height: 1.45;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .native-carousel--services .service-block-two .icon {
        margin-bottom: 10px;
    }

    .native-carousel--services .service-block-two .learn-more {
        margin-top: auto;
    }

    @keyframes nativeHeroReveal {
        from {
            opacity: 0;
            transform: translateY(24px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 991px) {
        .section-skeleton__content {
            padding: 24px 18px;
        }

        .section-skeleton__cards,
        .skeleton-section--pricing .section-skeleton__cards,
        .skeleton-section--products .section-skeleton__cards,
        .skeleton-section--logos .section-skeleton__cards {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .skeleton-section--cta .section-skeleton__content {
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
        }

        .skeleton-section--cta .section-skeleton__button {
            width: 220px;
        }

        .home-products-shell {
            padding: 20px 16px 16px;
            border-radius: 18px;
        }
        .home-product-media {
            height: 200px;
        }
        .native-home-hero .native-carousel__arrow {
            top: auto;
            bottom: 82px;
            transform: none;
        }

        .native-home-hero .native-carousel__arrow:hover {
            transform: none;
        }

        .native-home-hero .native-carousel__arrow--prev {
            left: 16px;
        }

        .native-home-hero .native-carousel__arrow--next {
            right: 16px;
        }

        .native-carousel--services,
        .native-carousel--testimonials,
        .native-carousel--logos {
            padding: 0 0 58px;
        }

        .native-carousel--services .service-block-two .inner-box {
            min-height: 210px;
            padding: 16px 14px;
        }

        .native-carousel--services .native-carousel__arrow,
        .native-carousel--testimonials .native-carousel__arrow,
        .native-carousel--logos .native-carousel__arrow,
        .home-products-carousel .native-carousel__arrow {
            position: static;
            margin-top: 10px;
        }

        .home-products-carousel .native-carousel__arrow--prev,
        .home-products-carousel .native-carousel__arrow--next {
            right: auto;
            left: auto;
        }
    }

    @media (max-width: 767px) {
        .skeleton-section--hero {
            min-height: 320px;
        }

        .section-skeleton__cards,
        .skeleton-section--pricing .section-skeleton__cards,
        .skeleton-section--products .section-skeleton__cards,
        .skeleton-section--logos .section-skeleton__cards {
            grid-template-columns: 1fr;
        }
    }

</style>
@endpush
@section('content')
    @php
        $waTrial = 'https://wa.me/16393903194?text=' . urlencode(__('messages.whatsapp_trial'));
        $currency = config('services.app.default_currency', 'USD');
        $useNativeHomeCarousel = true;
        $useSectionSkeletons = true;
    @endphp

    @include('includes._slider', ['useNativeCarousel' => $useNativeHomeCarousel, 'useSectionSkeletons' => $useSectionSkeletons])

    @include('includes._best-packages', ['useSectionSkeletons' => $useSectionSkeletons])

    @if(!empty($homeProducts) && count($homeProducts) > 0)
        <section class="shop-section shop-section-2 skeleton-section skeleton-section--products"
            data-skeleton-section
            style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
            <div class="section-skeleton__overlay" aria-hidden="true">
                <div class="section-skeleton__content">
                    <span class="section-skeleton__pill"></span>
                    <span class="section-skeleton__line section-skeleton__line--lg"></span>
                    <span class="section-skeleton__line section-skeleton__line--md"></span>
                    <div class="section-skeleton__cards">
                        <span class="section-skeleton__card"></span>
                        <span class="section-skeleton__card"></span>
                        <span class="section-skeleton__card"></span>
                        <span class="section-skeleton__card"></span>
                    </div>
                </div>
            </div>
            <div class="auto-container">
                <div class="home-products-shell">
                    <div class="sec-title mb-4" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                        <span class="home-products-headline" aria-hidden="true"></span>
                        <div style="display:flex; align-items:center; justify-content: space-between; gap:12px; flex-wrap:wrap;">
                            <div>
                                <h2 class="h3" style="margin:0;">Digital Products</h2>
                                <p class="text-muted mb-0" style="font-size:14px;">Premium digital subscriptions and services.</p>
                            </div>
                            <span></span>
                        </div>
                    </div>

                    <div class="home-products-carousel native-carousel native-carousel--cards"
                        data-native-carousel
                        data-items-desktop="4"
                        data-items-tablet="2"
                        data-items-mobile="1"
                        data-gap="30"
                        data-autoplay="5000">
                        <div class="native-carousel__viewport">
                            <div class="native-carousel__track">
                        @foreach($homeProducts as $p)
                            <div class="native-carousel__slide px-2">
                                <article class="home-product-card h-100">
                                    <a class="home-product-media" href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                        @if(!empty($p['image']))
                                            <img src="{{ $p['image'] }}"
                                                 alt="{{ $p['name'] }}"
                                                 loading="lazy"
                                                 decoding="async">
                                        @endif
                                    </a>
                                    <div class="home-product-body">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                            <h3 class="home-product-title">
                                                <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                                    {{ $p['name'] }}
                                                </a>
                                            </h3>
                                            <span class="home-product-badge {{ $p['type'] === 'digital' ? 'home-product-badge--digital' : 'home-product-badge--affiliate' }}">
                                                {{ ucfirst($p['type']) }}
                                            </span>
                                        </div>
                                        @if(!empty($p['price']))
                                            <div class="home-product-price">{{ $p['currency'] }} {{ number_format((float) $p['price'], 2) }}</div>
                                        @endif
                                        @if($p['type'] === 'digital' && !empty($p['buy_now_url']))
                                            <a href="{{ $p['buy_now_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary home-product-action">Buy Now</a>
                                        @else
                                            <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif class="btn btn-outline-primary home-product-action">Open Link</a>
                                        @endif
                                    </div>
                                </article>
                            </div>
                        @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if(!empty($homeAffiliateProducts) && count($homeAffiliateProducts) > 0)
        <section class="shop-section shop-section-2 mt-5 skeleton-section skeleton-section--products"
            data-skeleton-section
            style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
            <div class="section-skeleton__overlay" aria-hidden="true">
                <div class="section-skeleton__content">
                    <span class="section-skeleton__pill"></span>
                    <span class="section-skeleton__line section-skeleton__line--lg"></span>
                    <span class="section-skeleton__line section-skeleton__line--md"></span>
                    <div class="section-skeleton__cards">
                        <span class="section-skeleton__card"></span>
                        <span class="section-skeleton__card"></span>
                        <span class="section-skeleton__card"></span>
                        <span class="section-skeleton__card"></span>
                    </div>
                </div>
            </div>
            <div class="auto-container">
                <div class="home-products-shell">
                    <div class="sec-title mb-4" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                        <span class="home-products-headline" aria-hidden="true"></span>
                        <div style="display:flex; align-items:center; justify-content: space-between; gap:12px; flex-wrap:wrap;">
                            <div>
                                <h2 class="h3" style="margin:0;">Affiliate Products</h2>
                                <p class="text-muted mb-0" style="font-size:14px;">Top affiliate gadgets and device picks.</p>
                            </div>
                            <span></span>
                        </div>
                    </div>

                    <div class="home-products-carousel native-carousel native-carousel--cards"
                        data-native-carousel
                        data-items-desktop="4"
                        data-items-tablet="2"
                        data-items-mobile="1"
                        data-gap="30"
                        data-autoplay="5000">
                        <div class="native-carousel__viewport">
                            <div class="native-carousel__track">
                        @foreach($homeAffiliateProducts as $p)
                            <div class="native-carousel__slide px-2">
                                <article class="home-product-card h-100">
                                    <a class="home-product-media" href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                        @if(!empty($p['image']))
                                            <img src="{{ $p['image'] }}"
                                                 alt="{{ $p['name'] }}"
                                                 loading="lazy"
                                                 decoding="async">
                                        @endif
                                    </a>
                                    <div class="home-product-body">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                            <h3 class="home-product-title">
                                                <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                                    {{ $p['name'] }}
                                                </a>
                                            </h3>
                                            <span class="home-product-badge {{ $p['type'] === 'digital' ? 'home-product-badge--digital' : 'home-product-badge--affiliate' }}">
                                                {{ ucfirst($p['type']) }}
                                            </span>
                                        </div>
                                        @if(!empty($p['price']))
                                            <div class="home-product-price">{{ $p['currency'] }} {{ number_format((float) $p['price'], 2) }}</div>
                                        @endif
                                        @if($p['type'] === 'digital' && !empty($p['buy_now_url']))
                                            <a href="{{ $p['buy_now_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary home-product-action">Buy Now</a>
                                        @else
                                            <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif class="btn btn-outline-primary home-product-action">Open Link</a>
                                        @endif
                                    </div>
                                </article>
                            </div>
                        @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @include('includes._we-provide-unlimited', ['useSectionSkeletons' => $useSectionSkeletons])

    @include('includes._services', ['useNativeCarousel' => $useNativeHomeCarousel, 'useSectionSkeletons' => $useSectionSkeletons])

    @unless ($isMobile)
        @include('includes._testimonials', ['useNativeCarousel' => $useNativeHomeCarousel, 'useSectionSkeletons' => $useSectionSkeletons])
        @include('includes._channels-carousel', ['useNativeCarousel' => $useNativeHomeCarousel, 'useSectionSkeletons' => $useSectionSkeletons])
    @endunless

    @include('includes._check-trail', ['useSectionSkeletons' => $useSectionSkeletons])
@stop

@section('jsonld')
<script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Opplex IPTV",
    "url": "https://opplexiptv.com/",
    "description": "Opplex IPTV provides IPTV subscription services with live TV, sports, movies, and premium entertainment channels.",
    "logo": "https://opplexiptv.com/logo.png"
    }
</script>

<script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "Service",
    "name": "IPTV Subscription Service",
    "provider": {
        "@type": "Organization",
        "name": "Opplex IPTV"
    },
    "serviceType": "IPTV Streaming Service",
    "areaServed": "Worldwide"
    }
</script>

<script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "Opplex IPTV Subscription",
    "brand": {
        "@type": "Brand",
        "name": "Opplex IPTV"
    },
    "offers": {
        "@type": "Offer",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock"
    }
    }
</script>

<script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {
        "@type": "ListItem",
        "position": 1,
        "name": "Home",
        "item": "https://opplexiptv.com/"
        }
    ]
    }
</script>
@endsection
