@extends('layouts.default')
@section('title', __('messages.about.title'))
@section('content')

    @php
        $features = [
            [
                'icon' => 'flaticon-swimming-pool',
                'title' => __('messages.features.hd_quality.title'),
                'description' => __('messages.features.hd_quality.description'),
                'link' => route('packages'),
            ],
            [
                'icon' => 'flaticon-5g',
                'title' => __('messages.features.flexible_packages.title'),
                'description' => __('messages.features.flexible_packages.description'),
                'link' => route('packages'),
            ],
            [
                'icon' => 'flaticon-8k',
                'title' => __('messages.features.reliable_service.title'),
                'description' => __('messages.features.reliable_service.description'),
                'link' => route('packages'),
            ],
            [
                'icon' => 'flaticon-customer-service',
                'title' => __('messages.features.easy_setup.title'),
                'description' => __('messages.features.easy_setup.description'),
                'link' => route('contact'),
            ],
        ];
    @endphp

    <section class="page-title" style="background-image: url('{{ asset('images/background/7.webp') }}')">
        <div class="auto-container">
            <h2>{{ __('messages.about.title_short') }}</h2>
            <ul class="bread-crumb clearfix">
                <li><a href="/">{{ __('messages.nav.home') }}</a></li>
                <li>{{ __('messages.nav.about_us') }}</li>
            </ul>
        </div>
    </section>

    @include('includes._we-provide-unlimited')
    @include('includes._check-trail')

    <section class="services-section-three" style="background-image: url('{{ asset('images/background/pattern-6.webp') }}')">
        <div class="auto-container">
            <div class="sec-title clearfix">
                <div class="pull-left">
                    <div class="separator"></div>
                    <h2>{!! __('messages.choose_us.title') !!}</h2>
                </div>
                <div class="pull-right">
                    <a href="{{ route('packages') }}" class="theme-btn btn-style-four">
                        <span class="txt">{{ __('messages.choose_us.button') }} <i
                                class="lnr lnr-arrow-right"></i></span>
                    </a>
                </div>
            </div>
            <div class="row clearfix">
                @foreach ($features as $feature)
                    <div class="facility-block col-lg-3 col-md-6 col-sm-12" role="listitem">
                        <div class="inner-box" aria-label="{{ $feature['title'] }}">
                            <div class="pattern-layer"
                                style="background-image: url('{{ asset('images/background/pattern-14.webp') }}')">
                            </div>
                            <div class="icon-box {{ $feature['icon'] }}" role="img"
                                aria-label="{{ $feature['title'] }} icon"></div>
                            <h5>
                                <a href="{{ $feature['link'] }}" aria-label="Read more about {{ $feature['title'] }}">
                                    {{ $feature['title'] }}
                                </a>
                            </h5>
                            <div class="text">{{ $feature['description'] }}</div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>

    @include('includes._testimonials')
    @include('includes._channels-carousel')
@stop

@section('jsonld')
    <!-- About Page JSON-LD -->
    <script type="application/ld+json">
        {
        "@context": "https://schema.org",
        "@type": "AboutPage",
        "name": "About Opplex IPTV",
        "url": "{{ route('about') }}",
        "description": "Learn about Opplex IPTV - Europe's trusted IPTV provider with flexible packages, 4K streaming, and multi-language support."
        }
    </script>
@endsection
