@extends('layouts.default')
@section('title', __('messages.reseller.panel.title'))
@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    <section class="page-title" style="background-image: url('{{ asset('images/background/7.webp') }}')"
        aria-label="Opplex IPTV Reseller Panel Page Title">
        <div class="auto-container">
            <h2>{{ __('messages.reseller.panel.title') }}</h2>
            <ul class="bread-crumb clearfix" aria-label="Breadcrumb Navigation">
                <li><a href="/" aria-label="Go to Home">{{ __('messages.app.breadcrumb.home') }}</a></li>
                <li aria-current="page">{{ __('messages.reseller.panel.title') }}</li>
            </ul>
        </div>
    </section>


    <!-- Pricing Section -->
    @include('includes._best-packages')
    <!-- END Pricing Section -->


    <!-- Start We provide unlimited Section -->
    @include('includes._we-provide-unlimited')
    <!-- END We provide unlimited Section -->

    <!-- Start Check Trail Section -->
    @include('includes._check-trail')
    <!-- Start Check Trail Section -->

    <!-- Services Section Three -->
    <section class="services-section-three" style="background-image: url('{{ asset('images/background/pattern-6.webp') }}')"
        aria-label="Why Choose Opplex IPTV - HD Streaming, Flexible Subscriptions, Easy Setup, Reliable Service">
        <div class="auto-container">
            <div class="sec-title clearfix">
                <div class="pull-left">
                    <div class="separator"></div>
                    <h2>{{ __('messages.reasons.title') }}</h2>
                </div>
                <div class="pull-right">
                    <a href="{{ route('packages') }}" class="theme-btn btn-style-four" aria-label="View IPTV plans">
                        <span class="txt">{{ __('messages.view.services') }} <i class="lnr lnr-arrow-right"></i></span>
                    </a>
                </div>
            </div>
            <div class="row clearfix">
                @php
                    $seoServices = [
                        [
                            'icon' => 'flaticon-swimming-pool',
                            'title' => 'IPTV in HD & 4K Quality',
                            'description' =>
                                'Enjoy IPTV streaming with crystal-clear HD and 4K resolution, perfect for movies, sports, and live TV on any device.',
                        ],
                        [
                            'icon' => 'flaticon-5g',
                            'title' => 'Flexible IPTV Subscriptions',
                            'description' =>
                                'Choose from monthly or yearly IPTV plans tailored for France, Italy, and UK users – affordable and easy to upgrade.',
                        ],
                        [
                            'icon' => 'flaticon-8k',
                            'title' => 'Reliable IPTV Across Europe',
                            'description' =>
                                'Stream over 12,000 channels with 99.9% uptime and zero buffering. Ideal for Smart TVs, Firestick, and Android devices.',
                        ],
                        [
                            'icon' => 'flaticon-customer-service',
                            'title' => 'Easy IPTV Setup on Any Device',
                            'description' =>
                                'Start streaming instantly on Smart TV, Firestick, MAG, Android, and iOS – no tech skills needed. Our IPTV setup is beginner-friendly.',
                        ],
                    ];
                @endphp

                @foreach ($seoServices as $service)
                    <div class="facility-block col-lg-3 col-md-6 col-sm-12">
                        <div class="inner-box" aria-label="{{ $service['title'] }}">
                            <div class="pattern-layer"
                                style="background-image: url('{{ asset('images/background/pattern-14.webp') }}')"></div>
                            <div class="icon-box {{ $service['icon'] }}"></div>
                            <h5><a href="{{ route('packages') }}"
                                    aria-label="{{ $service['title'] }}">{{ $service['title'] }}</a></h5>
                            <div class="text">{{ $service['description'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- End Services Section Three -->

    <!-- Start Testimonial Section -->
    @include('includes._testimonials')
    <!-- End Testimonial Section -->


    <!-- Clients Section -->
    <section class="clients-section">
        <div class="auto-container">

            <div class="carousel-outer">
                <!--Sponsors Slider-->
                <ul class="sponsors-carousel owl-carousel owl-theme">
                    <li>
                        <div class="image-box"><a href="#" aria-label="Channel Logo 1"><img
                                    src="{{ asset('images/resource/1.webp') }}" alt="Channel Logo 1" loading="lazy"></a>
                        </div>
                    </li>
                    <li>
                        <div class="image-box"><a href="#" aria-label="Channel Logo 5"><img
                                    src="{{ asset('images/resource/5.webp') }}" alt="Channel Logo 5" loading="lazy"></a>
                        </div>
                    </li>
                    <li>
                        <div class="image-box ptv-sports"><a href="#" aria-label="PTV Sports"><img
                                    src="{{ asset('images/resource/4.webp') }}" alt="PTV Sports Logo" loading="lazy"></a>
                        </div>
                    </li>
                    <li>
                        <div class="image-box ary-digital"><a href="#" aria-label="ARY Digital"><img
                                    src="{{ asset('images/resource/3.webp') }}" alt="ARY Digital Logo" loading="lazy"></a>
                        </div>
                    </li>
                    <li>
                        <div class="image-box"><a href="#" aria-label="Channel Logo 6"><img
                                    src="{{ asset('images/resource/6.webp') }}" alt="Channel Logo 6" loading="lazy"></a>
                        </div>
                    </li>
                    <li>
                        <div class="image-box star-plus"><a href="#" aria-label="Star Plus"><img
                                    src="{{ asset('images/resource/7.webp') }}" alt="Star Plus Logo" loading="lazy"></a>
                        </div>
                    </li>
                    <li>
                        <div class="image-box"><a href="#" aria-label="Channel Logo 8"><img
                                    src="{{ asset('images/resource/8.webp') }}" alt="Channel Logo 8" loading="lazy"></a>
                        </div>
                    </li>
                    <li>
                        <div class="image-box"><a href="#" aria-label="Channel Logo 9"><img
                                    src="{{ asset('images/resource/9.webp') }}" alt="Channel Logo 9" loading="lazy"></a>
                        </div>
                    </li>
                </ul>

            </div>

        </div>
    </section>
    <!-- End Clients Section / Style Two -->



@stop

@section('jsonld')
<!-- Reseller Panel JSON-LD (All Fixes Applied) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Opplex IPTV Reseller Panel",
  "image": "{{ asset('images/icons/service-5.webp') }}",
  "description": "Join Opplex IPTV’s reseller program and start earning by selling IPTV subscriptions in France, Italy, UK, and across Europe. Instant activation, panel access, and 24/7 support.",
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
    "ratingValue": "4.8",
    "reviewCount": "152"
  },
  "offers": [
    {
      "@type": "Offer",
      "name": "Starter Reseller Package",
      "price": "4399",
      "priceCurrency": "USD",
      "availability": "https://schema.org/InStock",
      "priceValidUntil": "2026-12-31",
      "hasMerchantReturnPolicy": {
        "@type": "MerchantReturnPolicy",
        "applicableCountry": "PK",
        "returnPolicyCategory": "https://schema.org/MerchantReturnNotPermitted"
      },
      "shippingDetails": {
        "@type": "OfferShippingDetails",
        "shippingRate": {
          "@type": "MonetaryAmount",
          "value": "0",
          "currency": "USD"
        },
        "deliveryTime": {
          "@type": "ShippingDeliveryTime",
          "handlingTime": {
            "@type": "QuantitativeValue",
            "minValue": 0,
            "maxValue": 1,
            "unitCode": "DAY"
          },
          "transitTime": {
            "@type": "QuantitativeValue",
            "minValue": 0,
            "maxValue": 1,
            "unitCode": "DAY"
          }
        },
        "shippingDestination": {
          "@type": "DefinedRegion",
          "addressCountry": "PK"
        }
      },
      "itemOffered": {
        "@type": "Service",
        "name": "20 Reseller Credits"
      }
    },
    {
      "@type": "Offer",
      "name": "Essential Reseller Bundle",
      "price": "10499",
      "priceCurrency": "USD",
      "availability": "https://schema.org/InStock",
      "priceValidUntil": "2026-12-31",
      "hasMerchantReturnPolicy": {
        "@type": "MerchantReturnPolicy",
        "applicableCountry": "PK",
        "returnPolicyCategory": "https://schema.org/MerchantReturnNotPermitted"
      },
      "shippingDetails": {
        "@type": "OfferShippingDetails",
        "shippingRate": {
          "@type": "MonetaryAmount",
          "value": "0",
          "currency": "USD"
        },
        "deliveryTime": {
          "@type": "ShippingDeliveryTime",
          "handlingTime": {
            "@type": "QuantitativeValue",
            "minValue": 0,
            "maxValue": 1,
            "unitCode": "DAY"
          },
          "transitTime": {
            "@type": "QuantitativeValue",
            "minValue": 0,
            "maxValue": 1,
            "unitCode": "DAY"
          }
        },
        "shippingDestination": {
          "@type": "DefinedRegion",
          "addressCountry": "PK"
        }
      },
      "itemOffered": {
        "@type": "Service",
        "name": "50 Reseller Credits"
      }
    },
    {
      "@type": "Offer",
      "name": "Pro Reseller Suite",
      "price": "18999",
      "priceCurrency": "USD",
      "availability": "https://schema.org/InStock",
      "priceValidUntil": "2026-12-31",
      "hasMerchantReturnPolicy": {
        "@type": "MerchantReturnPolicy",
        "applicableCountry": "PK",
        "returnPolicyCategory": "https://schema.org/MerchantReturnNotPermitted"
      },
      "shippingDetails": {
        "@type": "OfferShippingDetails",
        "shippingRate": {
          "@type": "MonetaryAmount",
          "value": "0",
          "currency": "USD"
        },
        "deliveryTime": {
          "@type": "ShippingDeliveryTime",
          "handlingTime": {
            "@type": "QuantitativeValue",
            "minValue": 0,
            "maxValue": 1,
            "unitCode": "DAY"
          },
          "transitTime": {
            "@type": "QuantitativeValue",
            "minValue": 0,
            "maxValue": 1,
            "unitCode": "DAY"
          }
        },
        "shippingDestination": {
          "@type": "DefinedRegion",
          "addressCountry": "PK"
        }
      },
      "itemOffered": {
        "@type": "Service",
        "name": "100 Reseller Credits"
      }
    },
    {
      "@type": "Offer",
      "name": "Advanced Reseller Toolkit",
      "price": "35999",
      "priceCurrency": "USD",
      "availability": "https://schema.org/InStock",
      "priceValidUntil": "2026-12-31",
      "hasMerchantReturnPolicy": {
        "@type": "MerchantReturnPolicy",
        "applicableCountry": "PK",
        "returnPolicyCategory": "https://schema.org/MerchantReturnNotPermitted"
      },
      "shippingDetails": {
        "@type": "OfferShippingDetails",
        "shippingRate": {
          "@type": "MonetaryAmount",
          "value": "0",
          "currency": "USD"
        },
        "deliveryTime": {
          "@type": "ShippingDeliveryTime",
          "handlingTime": {
            "@type": "QuantitativeValue",
            "minValue": 0,
            "maxValue": 1,
            "unitCode": "DAY"
          },
          "transitTime": {
            "@type": "QuantitativeValue",
            "minValue": 0,
            "maxValue": 1,
            "unitCode": "DAY"
          }
        },
        "shippingDestination": {
          "@type": "DefinedRegion",
          "addressCountry": "PK"
        }
      },
      "itemOffered": {
        "@type": "Service",
        "name": "200 Reseller Credits"
      }
    }
  ]
}
</script>
@endsection
