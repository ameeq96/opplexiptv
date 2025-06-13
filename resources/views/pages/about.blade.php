@extends('layouts.default')
@section('title',
    'About Us | Opplex IPTV - Your Trusted IPTV Service Provider with IPTV Smarters Pro, XTV Live, and
    Opplex TV App')
@section('content')

    @php
        $features = [
            [
                'icon' => 'flaticon-swimming-pool',
                'title' => 'HD Quality Streaming',
                'description' =>
                    'Enjoy crystal-clear visuals and immersive audio for an unparalleled viewing experience.',
                'link' => route('packages'),
            ],
            [
                'icon' => 'flaticon-5g',
                'title' => 'Flexible Packages',
                'description' => 'Choose from a variety of packages tailored to your preferences and budget.',
                'link' => route('packages'),
            ],
            [
                'icon' => 'flaticon-8k',
                'title' => 'Reliable Service',
                'description' => 'Experience seamless streaming with minimal downtime.',
                'link' => route('packages'),
            ],
            [
                'icon' => 'flaticon-customer-service',
                'title' => 'Easy Setup',
                'description' =>
                    'Get started quickly and effortlessly with our user-friendly setup process. Choose us for top-notch entertainment at your fingertips!',
                'link' => route('contact'),
            ],
        ];
    @endphp

    <section class="page-title" style="background-image: url(images/background/7.webp)">
        <div class="auto-container">
            <h2>About Us</h2>
            <ul class="bread-crumb clearfix">
                <li><a href="/">Home</a></li>
                <li>About Us</li>
            </ul>
        </div>
    </section>

    <!-- Start We provide unlimited Section -->
    @include('includes._we-provide-unlimited')
    <!-- END We provide unlimited Section -->

    <!-- Start Check Trail Section -->
    @include('includes._check-trail')
    <!-- Start Check Trail Section -->

    <!-- Services Section Three -->
    <section class="services-section-three" style="background-image: url(images/background/pattern-6.webp)">
        <div class="auto-container">
            <div class="sec-title clearfix">
                <div class="pull-left">
                    <div class="separator"></div>
                    <h2>Few Great Reasons Make <br> You Choose us</h2>
                </div>
                <div class="pull-right">
                    <a href="{{ route('packages') }}" class="theme-btn btn-style-four"><span class="txt">View Services <i
                                class="lnr lnr-arrow-right"></i></span></a>
                </div>
            </div>
            <div class="row clearfix">
                @foreach ($features as $feature)
                    <div class="facility-block col-lg-3 col-md-6 col-sm-12">
                        <div class="inner-box">
                            <div class="pattern-layer" style="background-image: url(images/background/pattern-14.webp)">
                            </div>
                            <div class="icon-box {{ $feature['icon'] }}"></div>
                            <h5><a href="{{ $feature['link'] }}">{{ $feature['title'] }}</a></h5>
                            <div class="text">{{ $feature['description'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- End Services Section Three -->

    <!-- Start Testimonial Section  -->
    @include('includes._testimonials')
    <!-- END Testimonial Section  -->

    <!-- Channels Section -->
    @include('includes._channels-carousel')
    <!-- End Channels Section -->
@stop
