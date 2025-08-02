@extends('layouts.default')
@section('title', __('messages.title'))
@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    <!-- Page Title -->
    <section class="page-title" style="background-image: url('{{ asset('images/background/9.webp') }}')"
        aria-label="Page title section with IPTV background">
        <div class="auto-container">
            <h2>{{ __('messages.heading') }}</h2>
            <ul class="bread-crumb clearfix" aria-label="Breadcrumb navigation">
                <li><a href="/" aria-label="Go to Home">{{ __('messages.breadcrumb.home') }}</a></li>
                <li aria-current="page">{{ __('messages.breadcrumb.current') }}</li>
            </ul>
        </div>
    </section>

    <!-- End Page Title -->

    <!-- Pricing Section -->
    @include('includes._best-packages')
    <!-- End Pricing Section -->

    <!-- Internet Section -->
    <section class="internet-section" style="background-image: url('{{ asset('images/background/1.webp') }}')"
        aria-label="Opplex IPTV package overview section">
        <div class="auto-container">
            <div class="clearfix">
                <div class="content-column">
                    <h2>{{ __('messages.sub_heading') }}</h2>
                    <div class="text">{{ __('messages.description') }}</div>
                    <div class="price">{{ __('messages.price') }}</div>
                    <a href="{{ route('about') }}" class="theme-btn btn-style-four"
                        aria-label="Read more about Opplex IPTV">
                        <span class="txt">{{ __('messages.read_more') }}
                            <i class="lnr lnr-arrow-right" aria-hidden="true"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- End Internet Section -->

    <!-- Check Trial Section -->
    @include('includes._check-trail')

    <!-- Choose Us Section -->
    @include('includes._choose-us')

@stop

@section('jsonld')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@graph": [
        {
            "@type": "Organization",
            "@id": "{{ url('/') }}#organization",
            "name": "{{ config('app.name', 'IPTV Service Provider') }}",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('images/logo.png') }}"
        },
        {
            "@type": "WebSite",
            "@id": "{{ url('/') }}#website",
            "url": "{{ url('/') }}",
            "name": "{{ config('app.name', 'IPTV Service Provider') }}",
            "publisher": {
                "@id": "{{ url('/') }}#organization"
            }
        },
        {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}#webpage",
            "url": "{{ url()->current() }}",
            "name": "{{ __('messages.heading') }}",
            "isPartOf": {
                "@id": "{{ url('/') }}#website"
            },
            "breadcrumb": {
                "@id": "{{ url()->current() }}#breadcrumb"
            },
            "inLanguage": "{{ app()->getLocale() }}"
        },
        {
            "@type": "BreadcrumbList",
            "@id": "{{ url()->current() }}#breadcrumb",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "{{ __('messages.breadcrumb.home') }}",
                    "item": "{{ url('/') }}"
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": "{{ __('messages.breadcrumb.current') }}"
                }
            ]
        },
        {
            "@type": "Product",
            "name": "IPTV Subscription Service",
            "description": "{{ __('messages.description') }}",
            "image": "{{ asset('images/background/9.webp') }}",
            "brand": {
                "@type": "Organization",
                "name": "{{ config('app.name', 'IPTV Service Provider') }}"
            },
            // Se listan los diferentes planes como "offers" (ofertas)
            "offers": [
                {
                    "@type": "Offer",
                    "name": "Monthly Plan",
                    "price": "350",
                    "priceCurrency": "USD",
                    "priceSpecification": {
                        "@type": "PriceSpecification",
                        "price": "350",
                        "priceCurrency": "USD",
                        "valueAddedTaxIncluded": true,
                        "billingIncrement": "P1M" // ISO 8601 duration for 1 month
                    }
                },
                {
                    "@type": "Offer",
                    "name": "Half Yearly Plan",
                    "price": "1799",
                    "priceCurrency": "USD",
                    "priceSpecification": {
                        "@type": "PriceSpecification",
                        "price": "1799",
                        "priceCurrency": "USD",
                        "valueAddedTaxIncluded": true,
                        "billingIncrement": "P6M" // ISO 8601 duration for 6 months
                    }
                },
                {
                    "@type": "Offer",
                    "name": "Yearly Plan",
                    "price": "3400",
                    "priceCurrency": "USD",
                    "priceSpecification": {
                        "@type": "PriceSpecification",
                        "price": "3400",
                        "priceCurrency": "USD",
                        "valueAddedTaxIncluded": true,
                        "billingIncrement": "P1Y" // ISO 8601 duration for 1 year
                    }
                }
            ]
        }
    ]
}
</script>
@endsection
