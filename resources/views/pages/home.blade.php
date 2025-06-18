@extends('layouts.default')
@section('title', 'Opplex IPTV | IPTV Smarters Pro | XTV Live | Opplex TV App | Best IPTV Service')
@section('content')
    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $displayMovies = $agent->isMobile() ? $movies->take(3) : $movies;
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    <section class="main-slider-two">
        <div class="main-slider-carousel owl-carousel owl-theme">
            @foreach ($displayMovies as $index => $movie)
                <div class="slide {{ $index !== 0 ? 'lazy-background' : '' }}"
                    @if ($index !== 0) data-bg="{{ $movie['webp_image_url'] }}" loading="lazy" @endif>
                    @if ($index === 0)
                        <img src="{{ $movie['webp_image_url'] }}" alt="{{ $movie['title'] ?? $movie['name'] }}"
                            class="d-block w-100" width="1280" height="720" fetchpriority="high" decoding="auto"
                            loading="eager" />
                    @endif

                    <div class="auto-container custom-height">
                        <div class="content-boxed">
                            <div class="inner-box slider-font">
                                <h1>{{ $movie['title'] ?? $movie['name'] }}</h1>
                                <div class="text">
                                    <span class="d-none d-sm-inline">{{ $movie['overview'] }}</span>
                                    <span
                                        class="d-inline d-sm-none">{{ \Illuminate\Support\Str::limit($movie['overview'], 100) }}</span>
                                </div>
                                <div class="btns-box">
                                    <a href="{{ route('movies') }}" class="theme-btn btn-style-two">
                                        <span class="txt">Explore More <i class="lnr lnr-arrow-right"></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Pricing Section -->
    @include('includes._best-packages')
    <!-- END Pricing Section -->

    <!-- We Provide Unlimited Section -->
    @include('includes._we-provide-unlimited')
    <!-- END We Provide Unlimited Section -->

    <!-- Services Section -->
    <section class="services-section-two" style="background-image: url(images/background/3.webp)">
        <div class="auto-container">
            <div class="sec-title light centered">
                <div class="separator"></div>
                <h2>Explore Our Services</h2>
            </div>

            <div class="four-item-carousel owl-carousel owl-theme">
                <div class="service-block-two">
                    <div class="inner-box">
                        <div class="color-layer"></div>
                        <div class="icon-layer-one" style="background-image: url(images/background/pattern-19.webp)"></div>
                        <div class="icon-layer-two" style="background-image: url(images/background/pattern-20.webp)"></div>
                        <div class="icon"><img class="mx-width" src="images/icons/service-4.webp" alt=""
                                loading="lazy" /></div>
                        <h4><a href="{{ route('packages') }}">IPTV Packages</a></h4>
                        <div class="text">Explore our IPTV Packages for a world of entertainment at your fingertips.</div>
                        <a class="learn-more" href="{{ route('packages') }}">Learn More</a>
                    </div>
                </div>
                <div class="service-block-two">
                    <div class="inner-box">
                        <div class="color-layer"></div>
                        <div class="icon-layer-one" style="background-image: url(images/background/pattern-19.webp)"></div>
                        <div class="icon-layer-two" style="background-image: url(images/background/pattern-20.webp)"></div>
                        <div class="icon"><img class="mx-width" src="images/icons/service-5.webp" alt=""
                                loading="lazy" /></div>
                        <h4><a href="{{ route('packages') }}">Reseller Panel</a></h4>
                        <div class="text">Become a reseller and unlock new opportunities with our Reseller Panel</div>
                        <a class="learn-more" href="{{ route('packages') }}">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
