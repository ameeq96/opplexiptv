@extends('layouts.default')
@section('title', __('messages.site_title'))
@section('content')
    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $displayMovies = $agent->isMobile() ? $movies->take(3) : $movies;
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    <!-- Pricing Section -->
    @include('includes._slider')
    <!-- END Pricing Section -->

    <!-- Pricing Section -->
    @include('includes._best-packages')
    <!-- END Pricing Section -->

    <!-- We Provide Unlimited Section -->
    @include('includes._we-provide-unlimited')
    <!-- END We Provide Unlimited Section -->

    <!-- Services Section -->
    @include('includes._services')
    <!-- End Services Section -->

    <!-- Start Testimonial Section -->
    @if (!$agent->isMobile())
        @include('includes._testimonials')
    @endif
    <!-- End Testimonial Section -->

    <!-- Channels Section -->
    @if (!$agent->isMobile())
        @include('includes._channels-carousel')
    @endif
    <!-- End Channels Section -->

    <!-- Start Check Trail Section -->
    @include('includes._check-trail')
    <!-- Start Check Trail Section -->

@stop

@section('jsonld')
    <!-- Homepage JSON-LD -->
    <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Opplex IPTV Packages",
  "image": "https://opplexiptv.com/images/background/9.webp",
  "description": "Affordable IPTV pricing with HD & 4K streaming. Monthly and yearly plans with access to 12,000+ channels and 50,000+ VOD content.",
  "brand": {
    "@type": "Brand",
    "name": "Opplex IPTV"
  },
  "url": "https://opplexiptv.com/pricing",
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
      "availability": "https://schema.org/InStock",
      "priceValidUntil": "2025-12-31",
      "hasMerchantReturnPolicy": {
        "@type": "MerchantReturnPolicy",
        "returnPolicyCategory": "https://schema.org/NoReturns"
      },
      "shippingDetails": {
        "@type": "OfferShippingDetails",
        "shippingRate": {
          "@type": "MonetaryAmount",
          "value": "0",
          "currency": "PKR"
        },
        "shippingDestination": {
          "@type": "DefinedRegion",
          "addressCountry": "PK"
        },
        "deliveryTime": {
          "@type": "ShippingDeliveryTime",
          "handlingTime": {
            "@type": "QuantitativeValue",
            "minValue": 0,
            "maxValue": 1,
            "unitCode": "d"
          },
          "transitTime": {
            "@type": "QuantitativeValue",
            "minValue": 0,
            "maxValue": 1,
            "unitCode": "d"
          }
        }
      }
    }
  ]
}


</script>
@endsection
