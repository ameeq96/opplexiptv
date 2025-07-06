@extends('layouts.default')
@section('title', __('messages.title'))
@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    <!-- Page Title -->
    <section class="page-title" style="background-image: url('{{ asset('images/background/8.webp') }}')">
        <div class="auto-container">
            <h2>{{ __('messages.heading') }}</h2>
            <ul class="bread-crumb clearfix">
                <li><a href="/">{{ __('messages.breadcrumb.home') }}</a></li>
                <li>{{ __('messages.breadcrumb.current') }}</li>
            </ul>
        </div>
    </section>
    <!-- End Page Title -->

    <!-- Pricing Section -->
    @include('includes._best-packages')
    <!-- End Pricing Section -->

    <!-- Internet Section Three -->
    <section class="internet-section-three" style="background-image: url('{{ asset('images/background/1.webp') }}')">
        <div class="auto-container">
            <div class="row clearfix">

                <!-- Image Column -->
                <div class="image-column col-lg-6 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="transparent-image">
                            <iframe title="Opplex IPTV Video Overview" aria-label="Opplex IPTV Introduction Video"
                                src="https://www.facebook.com/plugins/video.php?height=476&href=https%3A%2F%2Fwww.facebook.com%2F61565476366548%2Fvideos%2F8317598665026269%2F&show_text=false&width=476&t=0"
                                width="476" height="476" style="border:none;overflow:hidden" scrolling="no"
                                frameborder="0" allowfullscreen="true"
                                allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                            </iframe>
                        </div>
                    </div>
                </div>

                <!-- Content Column -->
                <div class="content-column col-lg-6 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="sec-title light">
                            <div class="separator"></div>
                            <h2>{{ __('messages.sub_heading') }}</h2>
                        </div>
                        <div class="text">{{ __('messages.description') }}</div>
                        <div class="price">{{ __('messages.price') }}</div>
                        <a href="{{ route('about') }}" class="theme-btn btn-style-two"
                            aria-label="Read more about Opplex IPTV">
                            <span class="txt">{{ __('messages.read_more') }} <i class="lnr lnr-arrow-right"
                                    aria-hidden="true"></i></span>
                        </a>

                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- End Internet Section Three -->


@stop

@section('jsonld')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "Opplex IPTV Packages",
    "image": "{{ asset('images/background/9.webp') }}",
    "description": "Affordable IPTV pricing with HD & 4K streaming. Monthly and yearly plans with access to 12,000+ channels and 50,000+ VOD content.",
    "brand": {
        "@type": "Brand",
        "name": "Opplex IPTV"
    },
    "url": "{{ url()->current() }}",
    "review": {
        "@type": "Review",
        "reviewRating": {
            "@type": "Rating",
            "ratingValue": "5",
            "bestRating": "5"
        },
        "author": {
            "@type": "Organization",
            "name": "Opplex IPTV"
        }
    },
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.9",
        "reviewCount": "285"
    },
    "offers": [
        {
            "@type": "Offer",
            "name": "Monthly Plan",
            "price": "350",
            "priceCurrency": "PKR",
            "availability": "https://schema.org/InStock"
        },
        {
            "@type": "Offer",
            "name": "Half Yearly Plan",
            "price": "1799",
            "priceCurrency": "PKR",
            "availability": "https://schema.org/InStock"
        },
        {
            "@type": "Offer",
            "name": "Yearly Plan",
            "price": "3400",
            "priceCurrency": "PKR",
            "availability": "https://schema.org/InStock"
        }
    ]
}
</script>
@endsection