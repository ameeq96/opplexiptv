@extends('layouts.default')
@section('title', __('messages.reseller.panel.title'))

@push('schema')
    {!! jsonld(seo()->service(
        'IPTV Reseller Panel',
        'Become an IPTV reseller with Opplex IPTV: panel access, credit-based plans, instant activation and 24/7 support across Europe and the USA.',
        route('reseller-panel'),
        seo()->packageOffers('reseller'),
        'IPTV Reseller Service',
    )) !!}
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/about.css') }}?v={{ @filemtime(public_path('css/about.css')) ?: 1 }}">
@endpush

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

    {{-- Why choose us / services --}}
    <section class="abtx {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
             aria-label="Why Choose Opplex IPTV - HD Streaming, Flexible Subscriptions, Easy Setup, Reliable Service">
        <div class="auto-container">
            <div class="abtx__head">
                <div class="abtx__titles">
                    <div class="abtx__bar" aria-hidden="true"></div>
                    <h1 class="abtx__title">{{ __('messages.reasons.title') }}</h1>
                </div>

                <a href="{{ route('packages') }}" class="abtx__cta" aria-label="{{ __('messages.view.services') }}">
                    {{ __('messages.view.services') }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>

            <div class="abtx-grid" role="list">
                @foreach ($seoServices as $service)
                    <a class="abtx-card" role="listitem" href="{{ route('packages') }}"
                        aria-label="{{ $service['title'] ?? '' }}">
                        <span @class(['abtx-card__icon', $service['icon'] ?? '']) aria-hidden="true"></span>
                        <h3 class="abtx-card__title">{{ $service['title'] ?? '' }}</h3>
                        <p class="abtx-card__text">{{ $service['description'] ?? '' }}</p>
                        <span class="abtx-card__arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </a>
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

    
    {{-- FAQ Section --}}
    @include('includes._faq-section')

    {{-- Trial --}}
    @include('includes._check-trail')
@stop
