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
        ['url' => '/', 'label' => __('messages.breadcrumb.home')],
        ['label' => __('messages.breadcrumb.current')],
    ]" background="images/background/7.webp" :rtl="$isRtl"
        aria-label="Generic Page" />
    <!-- End Page Title -->

    <!-- Pricing Section -->
    @include('includes._best-packages')
    <!-- End Pricing Section -->

    <!-- Internet Section Three -->
    <section class="internet-section-three" style="background-image: url('{{ asset('images/background/1.webp') }}')">
        <div class="auto-container">
            <div class="row clearfix">

                <!-- Image Column -->
                <div class="image-column col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">

                </div>

                <!-- Content Column -->
                <div class="content-column col-lg-6 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="sec-title light">
                            @if (!$isRtl)
                                <div class="separator"></div>
                            @endif
                            <h3>{{ __('messages.sub_heading') }}</h3>
                        </div>
                        <div class="text">{{ __('messages.description') }}</div>
                        <div class="price">{!! __('messages.price') !!}</div>
                        <a href="{{ route('about') }}" class="theme-btn btn-style-two"
                            aria-label="{{ __('messages.read_more') }}">
                            <span class="txt">
                                {{ __('messages.read_more') }}
                                <i class="lnr {{ $isRtl ? 'lnr-arrow-left' : 'lnr-arrow-right' }}" aria-hidden="true"></i>
                            </span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- End Internet Section Three -->


@stop
