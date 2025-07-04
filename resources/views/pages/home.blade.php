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
  "@type": "Service",
  "name": "Opplex IPTV",
  "url": "{{ route('home') }}",
  "description": "Stream 12,000+ live channels, 50,000+ movies, and 5,000+ series in HD & 4K. Available across Europe — France, Italy, UK, and more.",
  "provider": {
    "@type": "Organization",
    "name": "Opplex IPTV"
  },
  "areaServed": {
    "@type": "Place",
    "name": "Europe"
  }
}
</script>
@endsection
