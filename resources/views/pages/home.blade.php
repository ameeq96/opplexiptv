@extends('layouts.default')
@section('title', __('messages.site_title'))
@section('content')
    @php
        $waTrial = 'https://wa.me/16393903194?text=' . urlencode(__('messages.whatsapp_trial'));
        $currency = config('services.app.default_currency', 'USD');
    @endphp

    @include('includes._slider')

    @include('includes._best-packages')

    <section class="shop-section shop-section-2"
        style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
        <div class="auto-container">
            <div class="sec-title" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                <div class="separator"></div>
                <div style="display:flex; align-items:center; justify-content: space-between; gap:12px; flex-wrap:wrap;">
                    <h2 class="h3" style="margin:0;">{{ __('messages.our_products') }}</h2>
                    <a href="{{ route('shop') }}" class="btn btn-primary" style="background:#df0303; border:none;">
                        {{ __('View All') }}
                    </a>
                </div>
            </div>

            @include('includes._product-carousel', [
                'products' => $shopProducts ?? [],
                'isRtl' => $isRtl,
                'id' => 'homeProductsCarousel',
            ])
        </div>
    </section>

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
