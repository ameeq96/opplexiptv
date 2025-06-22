@extends('layouts.default')
@section('title', 'Reseller Panel | Opplex IPTV - Manage Your IPTV Reselling Business')
@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    <section class="page-title" style="background-image: url(images/background/7.webp)">
        <div class="auto-container">
            <h2>Reseller Panel</h2>
            <ul class="bread-crumb clearfix">
                <li><a href="/">Home</a></li>
                <li>Reseller Panel</li>
            </ul>
        </div>
    </section>

    <!-- START Reseller pricing Section -->
    @include('includes._reseller-packages')
    <!-- END Reseller pricing Section -->


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
                @foreach ([['icon' => 'flaticon-swimming-pool', 'title' => 'HD Quality Streaming', 'description' => 'Enjoy crystal-clear visuals and immersive audio for an unparalleled viewing experience.'], ['icon' => 'flaticon-5g', 'title' => 'Flexible Packages', 'description' => 'Choose from a variety of packages tailored to your preferences and budget.'], ['icon' => 'flaticon-8k', 'title' => 'Reliable Service', 'description' => 'Experience seamless streaming with minimal downtime.'], ['icon' => 'flaticon-customer-service', 'title' => 'Easy Setup', 'description' => 'Get started quickly and effortlessly with our user-friendly setup process. Choose us for top-notch entertainment at your fingertips!']] as $service)
                    <div class="facility-block col-lg-3 col-md-6 col-sm-12">
                        <div class="inner-box">
                            <div class="pattern-layer" style="background-image: url(images/background/pattern-14.webp)">
                            </div>
                            <div class="icon-box {{ $service['icon'] }}"></div>
                            <h5><a href="{{ route('packages') }}">{{ $service['title'] }}</a></h5>
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
                        <div class="image-box"><a href="#"><img src="images/resource/1.webp" alt=""></a></div>
                    </li>
                    <li>
                        <div class="image-box"><a href="#"><img src="images/resource/5.webp" alt=""></a></div>
                    </li>
                    <li>
                        <div class="image-box ptv-sports"><a href="#"><img src="images/resource/4.webp"
                                    alt=""></a></div>
                    </li>
                    <li>
                        <div class="image-box ary-digital"><a href="#"><img src="images/resource/3.webp"
                                    alt=""></a></div>
                    </li>
                    <li>
                        <div class="image-box"><a href="#"><img src="images/resource/6.webp" alt=""></a></div>
                    </li>
                    <li>
                        <div class="image-box star-plus"><a href="#"><img src="images/resource/7.webp"
                                    alt=""></a></div>
                    </li>
                    <li>
                        <div class="image-box"><a href="#"><img src="images/resource/8.webp" alt=""></a></div>
                    </li>
                    <li>
                        <div class="image-box"><a href="#"><img src="images/resource/9.webp" alt=""></a></div>
                    </li>
                </ul>
            </div>

        </div>
    </section>
    <!-- End Clients Section / Style Two -->



@stop
