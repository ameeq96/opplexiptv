@extends('layouts.default')
@section('title', __('messages.title'))
@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    <!-- Page Title -->
    <x-page-title :title="__('messages.heading')" :breadcrumbs="[
        ['url' => '/', 'label' => __('messages.breadcrumb.home'), 'aria' => 'Go to Home'],
        ['label' => __('messages.breadcrumb.current')],
    ]" background="images/background/9.webp" :rtl="$isRtl"
        aria-label="Page title section with IPTV background" />


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
                    <div class="text text-dark">{{ __('messages.description') }}</div>
                    <div class="price">{!! __('messages.price') !!}</div>
                    <a href="{{ route('about') }}" class="theme-btn btn-style-four"
                        aria-label="Read more about Opplex IPTV">
                        <span class="txt">{{ __('messages.read_more') }}
                            <i class="rtl-rotate lnr lnr-arrow-right" aria-hidden="true"></i>
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
