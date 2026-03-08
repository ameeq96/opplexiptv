@extends('layouts.default')
@section('title', __('messages.site_title'))
@push('styles')
<style>
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
    .home-products-carousel .owl-stage-outer {
        padding: 6px 2px 16px;
    }
    .home-products-carousel .owl-nav {
        margin-top: 0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        position: absolute;
        right: 0;
        top: -78px;
        z-index: 3;
    }
    [dir="rtl"] .home-products-carousel .owl-nav {
        right: auto;
        left: 0;
    }
    .home-products-carousel .owl-nav .owl-prev,
    .home-products-carousel .owl-nav .owl-next {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #ffffff !important;
        border: 1px solid #e6ebf3 !important;
        color: #0b1526 !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
    }
    @media (max-width: 991px) {
        .home-products-shell {
            padding: 20px 16px 16px;
            border-radius: 18px;
        }
        .home-product-media {
            height: 200px;
        }
        .home-products-carousel .owl-nav {
            position: static;
            justify-content: center;
            margin-top: 10px;
        }
    }
</style>
@endpush
@section('content')
    @php
        $waTrial = 'https://wa.me/16393903194?text=' . urlencode(__('messages.whatsapp_trial'));
        $currency = config('services.app.default_currency', 'USD');
    @endphp

    @include('includes._slider')

    @include('includes._best-packages')

    @if(!empty($homeProducts) && count($homeProducts) > 0)
        <section class="shop-section shop-section-2"
            style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
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

                    <div class="home-products-carousel spotlight-carousel owl-carousel owl-theme">
                        @foreach($homeProducts as $p)
                            <div class="item px-2">
                                <article class="home-product-card h-100">
                                    <a class="home-product-media" href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                        @if(!empty($p['image']))
                                            <img class="owl-lazy"
                                                 data-src="{{ $p['image'] }}"
                                                 src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
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
        </section>
    @endif

    @if(!empty($homeAffiliateProducts) && count($homeAffiliateProducts) > 0)
        <section class="shop-section shop-section-2 mt-4"
            style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
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

                    <div class="home-products-carousel spotlight-carousel owl-carousel owl-theme">
                        @foreach($homeAffiliateProducts as $p)
                            <div class="item px-2">
                                <article class="home-product-card h-100">
                                    <a class="home-product-media" href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                        @if(!empty($p['image']))
                                            <img class="owl-lazy"
                                                 data-src="{{ $p['image'] }}"
                                                 src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
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
        </section>
    @endif

    @include('includes._we-provide-unlimited')

    @include('includes._services')

    @unless ($isMobile)
        @include('includes._testimonials')
        @include('includes._channels-carousel')
    @endunless

    @include('includes._check-trail')
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
