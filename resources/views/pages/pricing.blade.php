@extends('layouts.default')
@section('title', __('messages.title'))

@push('schema')
    {!! jsonld(seo()->service(
        'IPTV Subscription Plans',
        'Affordable IPTV subscription plans with 12,000+ live channels, sports, movies and VOD in HD & 4K, on every device.',
        route('pricing'),
        seo()->packageOffers('iptv'),
    )) !!}
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pricing.css') }}?v={{ @filemtime(public_path('css/pricing.css')) ?: 1 }}">
@endpush

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

    <!-- Pricing promo band -->
    <section class="prcx {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" aria-labelledby="pricing-promo-title">
        <div class="auto-container">
            <div class="prcx__panel">
                <div class="prcx__content">
                    <div class="prcx__bar" aria-hidden="true"></div>
                    <h1 id="pricing-promo-title" class="prcx__title">{{ __('messages.sub_heading') }}</h1>
                    <p class="prcx__text">{{ __('messages.description') }}</p>
                    @if (trim((string) __('messages.price')) !== '')
                        <div class="prcx__price">{!! __('messages.price') !!}</div>
                    @endif
                    <div>
                        <a href="{{ route('about') }}" class="prcx__cta" aria-label="{{ __('messages.read_more') }}">
                            {{ __('messages.read_more') }}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                </div>

                <div class="prcx__visual" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3.5" width="20" height="13" rx="2.5"/>
                        <path d="M8 20.5h8M12 16.5v4"/>
                        <path d="M10 7.2v5.6l5-2.8-5-2.8z" fill="currentColor" stroke="none"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>
    <!-- End Pricing promo band -->

    {{-- FAQ Section --}}
    @include('includes._faq-section')

@stop
