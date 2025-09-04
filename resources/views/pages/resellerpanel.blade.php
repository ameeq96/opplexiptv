@extends('layouts.default')
@section('title', __('messages.reseller.panel.title'))
@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    <x-page-title :title="__('messages.reseller.panel.title')" :breadcrumbs="[
        ['url' => '/', 'label' => __('messages.app.breadcrumb.home'), 'aria' => 'Go to Home'],
        ['label' => __('messages.reseller.panel.title')],
    ]" background="images/background/7.webp" :rtl="$isRtl"
        aria-label="Opplex IPTV Reseller Panel Page Title" />

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
                    <a href="{{ route('packages') }}" class="theme-btn btn-style-four"
                        aria-label="{{ __('messages.view.services') }}">
                        <span class="txt">
                            {{ __('messages.view.services') }}
                            <i class="lnr {{ $isRtl ? 'lnr-arrow-left' : 'lnr-arrow-right' }}"></i>
                        </span>
                    </a>

                </div>
            </div>
            <div class="row clearfix">
                @php
                    $seoServices = __('messages.seo_services');
                @endphp

                @foreach ($seoServices as $service)
                    <div class="facility-block col-lg-3 col-md-6 col-sm-12">
                        <div class="inner-box" aria-label="{{ $service['title'] }}">
                            <div class="pattern-layer"
                                style="background-image: url('{{ asset('images/background/pattern-14.webp') }}')"></div>
                            <div class="icon-box {{ $service['icon'] }}"></div>
                            <h5>
                                <a href="{{ route('packages') }}" aria-label="{{ $service['title'] }}">
                                    {{ $service['title'] }}
                                </a>
                            </h5>
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


    <!-- Channels Section -->
    @if (!$agent->isMobile())
        @include('includes._channels-carousel')
    @endif
    <!-- End Channels Section -->



@stop
