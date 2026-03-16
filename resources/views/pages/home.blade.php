@extends('layouts.default')
@section('title', __('messages.site_title'))

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
                                <h3>{{ __('messages.home_products_digital_title') }}</h3>
                                <p class="text-muted mb-0" style="font-size:14px;">{{ __('messages.home_products_digital_desc') }}</p>
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
                                                {{ $p['type'] === 'digital' ? __('messages.home_products_type_digital') : __('messages.home_products_type_affiliate') }}
                                            </span>
                                        </div>
                                        @if(!empty($p['price']))
                                            <div class="home-product-price">{{ $p['currency'] }} {{ number_format((float) $p['price'], 2) }}</div>
                                        @endif
                                        <div class="home-product-actions">
                                            @if($p['type'] === 'digital' && !empty($p['buy_now_url']))
                                                <a href="{{ $p['buy_now_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary home-product-action">{{ __('messages.buy_now') }}</a>
                                            @else
                                                <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif class="btn btn-primary home-product-action">{{ __('messages.home_products_open_link') }}</a>
                                            @endif
                                            <button type="button"
                                                class="home-product-share"
                                                aria-label="{{ __('messages.home_products_share_label', ['name' => $p['name']]) }}"
                                                data-share-url="{{ $p['share_url'] ?? $p['url'] }}"
                                                data-share-title="{{ $p['name'] }}"
                                                data-share-text="{{ $p['share_text'] ?? __('messages.home_products_share_text', ['name' => $p['name']]) }}">
                                                <i class="fa fa-share-alt" aria-hidden="true"></i>
                                            </button>
                                        </div>
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
                                <h3>{{ __('messages.home_products_affiliate_title') }}</h3>
                                <p class="text-muted mb-0" style="font-size:14px;">{{ __('messages.home_products_affiliate_desc') }}</p>
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
                                                {{ $p['type'] === 'digital' ? __('messages.home_products_type_digital') : __('messages.home_products_type_affiliate') }}
                                            </span>
                                        </div>
                                        @if(!empty($p['price']))
                                            <div class="home-product-price">{{ $p['currency'] }} {{ number_format((float) $p['price'], 2) }}</div>
                                        @endif
                                        <div class="home-product-actions">
                                            @if($p['type'] === 'digital' && !empty($p['buy_now_url']))
                                                <a href="{{ $p['buy_now_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary home-product-action">{{ __('messages.buy_now') }}</a>
                                            @else
                                                <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif class="btn btn-primary home-product-action">{{ __('messages.home_products_open_link') }}</a>
                                            @endif
                                            <button type="button"
                                                class="home-product-share"
                                                aria-label="{{ __('messages.home_products_share_label', ['name' => $p['name']]) }}"
                                                data-share-url="{{ $p['share_url'] ?? $p['url'] }}"
                                                data-share-title="{{ $p['name'] }}"
                                                data-share-text="{{ $p['share_text'] ?? __('messages.home_products_share_text', ['name' => $p['name']]) }}">
                                                <i class="fa fa-share-alt" aria-hidden="true"></i>
                                            </button>
                                        </div>
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

