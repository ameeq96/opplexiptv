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
        "@type": "Person",
        "name": "Verified Customer"
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
        "price": "9.99",
        "priceCurrency": "USD",
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
            "currency": "USD"
          },
          "shippingDestination": {
            "@type": "DefinedRegion",
            "addressCountry": "US"
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

    <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "Forfaits Opplex IPTV",
    "image": "https://opplexiptv.com/images/background/9.webp",
    "description": "Tarifs IPTV abordables avec streaming HD et 4K. Forfaits mensuels et annuels avec accès à plus de 12 000 chaînes et 50 000+ contenus VOD.",
    "brand": {
      "@type": "Brand",
      "name": "Opplex IPTV"
    },
    "url": "https://opplexiptv.com/fr/prix",
    "review": {
      "@type": "Review",
      "reviewRating": {
        "@type": "Rating",
        "ratingValue": "5",
        "bestRating": "5"
      },
      "author": {
        "@type": "Person",
        "name": "Client Vérifié"
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
        "name": "Forfait Mensuel",
        "price": "9.99",
        "priceCurrency": "USD",
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
            "currency": "USD"
          },
          "shippingDestination": {
            "@type": "DefinedRegion",
            "addressCountry": "FR"
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

    <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "Pacchetti Opplex IPTV",
    "image": "https://opplexiptv.com/images/background/9.webp",
    "description": "Prezzi IPTV convenienti con streaming HD e 4K. Piani mensili e annuali con accesso a oltre 12.000 canali e più di 50.000 contenuti VOD.",
    "brand": {
      "@type": "Brand",
      "name": "Opplex IPTV"
    },
    "url": "https://opplexiptv.com/it/prezzi",
    "review": {
      "@type": "Review",
      "reviewRating": {
        "@type": "Rating",
        "ratingValue": "5",
        "bestRating": "5"
      },
      "author": {
        "@type": "Person",
        "name": "Cliente Verificato"
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
        "name": "Piano Mensile",
        "price": "9.99",
        "priceCurrency": "USD",
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
            "currency": "USD"
          },
          "shippingDestination": {
            "@type": "DefinedRegion",
            "addressCountry": "IT"
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


    <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Opplex IPTV",
  "url": "https://opplexiptv.com",
  "logo": "https://opplexiptv.com/images/opplexiptvlogo.webp",
  "sameAs": [
    "https://www.facebook.com/profile.php?id=61565476366548",
    "https://www.linkedin.com/company/digitalize-store",
    "https://www.instagram.com/oplextv"
  ],
  "contactPoint": [
    {
      "@type": "ContactPoint",
      "telephone": "+1-639-390-3194",
      "contactType": "customer service",
      "areaServed": "Worldwide",
      "availableLanguage": ["English", "Italian", "French"]
    }
  ]
}
</script>

    <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebSite",
    "url": "https://opplexiptv.com/",
    "potentialAction": {
      "@type": "SearchAction",
      "target": "https://opplexiptv.com/?s={search_term_string}",
      "query-input": "required name=search_term_string"
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
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Pricing",
      "item": "https://opplexiptv.com/pricing"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "About",
      "item": "https://opplexiptv.com/about"
    },
    {
      "@type": "ListItem",
      "position": 4,
      "name": "Movies",
      "item": "https://opplexiptv.com/movies"
    },
    {
      "@type": "ListItem",
      "position": 5,
      "name": "Reseller Panel",
      "item": "https://opplexiptv.com/reseller-panel"
    },
    {
      "@type": "ListItem",
      "position": 6,
      "name": "Our Packages",
      "item": "https://opplexiptv.com/packages"
    },
    {
      "@type": "ListItem",
      "position": 7,
      "name": "IPTV Applications",
      "item": "https://opplexiptv.com/iptv-applications"
    },
    {
      "@type": "ListItem",
      "position": 8,
      "name": "FAQ's",
      "item": "https://opplexiptv.com/faqs"
    },
    {
      "@type": "ListItem",
      "position": 9,
      "name": "Contact Us",
      "item": "https://opplexiptv.com/contact"
    }
  ]
}
</script>

@endsection
