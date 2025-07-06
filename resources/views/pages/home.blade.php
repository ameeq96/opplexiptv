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
  "@context": "https://schema.org/", 
  "@type": "Product", 
  "name": "Opplex IPTV",
  "image": "https://opplexiptv.com/images/opplexiptvlogo.webp",
  "description": "Opplex IPTV offers a premium streaming service with over 20,000 live TV channels and more than 100,000 VOD (Video on Demand) options, including the latest movies and series. The service is compatible with all devices and operating systems and features 4K, FHD, and HD picture quality. It includes an Anti-Freeze system for stable viewing and provides 24/7 customer support.",
  "brand": {
    "@type": "Brand",
    "name": "Opplex IPTV"
  },
  "sku": "5434534534",
  "offers": {
    "@type": "Offer",
    "url": "https://opplexiptv.com",
    "priceCurrency": "USD",
    "price": "2",
    "availability": "https://schema.org/InStock",
    "itemCondition": "https://schema.org/NewCondition"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "5",
    "bestRating": "5",
    "worstRating": "1",
    "ratingCount": "1",
    "reviewCount": "1"
  },
  "review": {
    "@type": "Review",
    "name": "Giulia Romano",
    "reviewBody": "Great service! I watch Netflix, Prime Video, and Italian series all in one place via IPTV.",
    "reviewRating": {
      "@type": "Rating",
      "ratingValue": "5",
      "bestRating": "5",
      "worstRating": "1"
    },
    "datePublished": "2025-07-01",
    "author": {"@type": "Person", "name": "Giulia Romano"},
    "publisher": {"@type": "Organization", "name": "Opplex IPTV"}
  }
}
</script>
@endsection
