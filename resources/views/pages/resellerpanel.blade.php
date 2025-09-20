@extends('layouts.default')
@section('title', __('messages.reseller.panel.title'))

@section('content')
    <x-page-title
        :title="__('messages.reseller.panel.title')"
        :breadcrumbs="[
            ['url' => '/', 'label' => __('messages.app.breadcrumb.home'), 'aria' => 'Go to Home'],
            ['label' => __('messages.reseller.panel.title')],
        ]"
        background="images/background/7.webp"
        :rtl="$isRtl"
        aria-label="Opplex IPTV Reseller Panel Page Title"
    />

    {{-- Pricing --}}
    @include('includes._best-packages')

    {{-- We provide unlimited --}}
    @include('includes._we-provide-unlimited')

    {{-- Trial --}}
    @include('includes._check-trail')

    {{-- Why choose us / services --}}
    <section class="services-section-three"
             style="background-image:url('{{ asset('images/background/pattern-6.webp') }}')"
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
                            <i class="lnr {{ $isRtl ? 'lnr-arrow-left' : 'lnr-arrow-right' }}" aria-hidden="true"></i>
                        </span>
                    </a>
                </div>
            </div>

            <div class="row clearfix" role="list">
                @foreach ($seoServices as $service)
                    <div class="facility-block col-lg-3 col-md-6 col-sm-12" role="listitem">
                        <div class="inner-box" aria-label="{{ $service['title'] ?? '' }}">
                            <div class="pattern-layer"
                                 style="background-image:url('{{ asset('images/background/pattern-14.webp') }}')"></div>

                            {{-- icon class from translations, e.g. "flaticon-8k" --}}
                            <div @class(['icon-box', $service['icon'] ?? '']) aria-hidden="true"></div>

                            <h5>
                                <a href="{{ route('packages') }}" aria-label="{{ $service['title'] ?? '' }}">
                                    {{ $service['title'] ?? '' }}
                                </a>
                            </h5>
                            <div class="text">{{ $service['description'] ?? '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    @include('includes._testimonials')

    {{-- Channels (desktop only) --}}
    @unless ($isMobile)
        @include('includes._channels-carousel')
    @endunless
@stop
