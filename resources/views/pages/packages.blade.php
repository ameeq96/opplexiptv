@extends('layouts.default')
@section('title', __('messages.title'))
@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    <!-- Page Title -->
    <section class="page-title" style="background-image: url('{{ asset('images/background/9.webp') }}')"
        aria-label="Page title section with IPTV background">
        <div class="auto-container">
            <h2>{{ __('messages.heading') }}</h2>
            <ul class="bread-crumb clearfix" aria-label="Breadcrumb navigation">
                <li><a href="/" aria-label="Go to Home">{{ __('messages.breadcrumb.home') }}</a></li>
                <li aria-current="page">{{ __('messages.breadcrumb.current') }}</li>
            </ul>
        </div>
    </section>

    <!-- End Page Title -->

    <!-- Pricing Section -->
    @include('includes._best-packages')
    <!-- End Pricing Section -->

    <!-- Internet Section -->
    <section class="internet-section" style="background-image: url('{{ asset('images/background/1.webp') }}')"
        aria-label="Opplex IPTV package overview section">
        <div class="auto-container">
            <div class="clearfix">
                <div class="content-column">
                    <h2>{{ __('messages.sub_heading') }}</h2>
                    <div class="text">{{ __('messages.description') }}</div>
                    <div class="price">{!! __('messages.price') !!}</div>
                    <a href="{{ route('about') }}" class="theme-btn btn-style-four"
                        aria-label="Read more about Opplex IPTV">
                        <span class="txt">{{ __('messages.read_more') }}
                            <i class="lnr lnr-arrow-right" aria-hidden="true"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- End Internet Section -->

    <!-- Check Trial Section -->
    @include('includes._check-trail')

    <!-- Choose Us Section -->
    @include('includes._choose-us')

@stop
