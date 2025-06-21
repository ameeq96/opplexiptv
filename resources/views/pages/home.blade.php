@extends('layouts.default')
@section('title', 'Opplex IPTV | IPTV Smarters Pro | XTV Live | Opplex TV App | Best IPTV Service')
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
