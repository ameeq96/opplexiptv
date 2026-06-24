@extends('layouts.default')

@section('title', __('messages.iptv_subscription_service.title'))

@push('schema')
    {!! jsonld(seo()->service(
        'IPTV Subscription Service',
        'Premium IPTV subscription service with live channels, sports, movies, series and 4K streaming, fast setup and a free trial.',
        route('iptv-subscription-service'),
        seo()->packageOffers('iptv'),
    )) !!}
@endpush

@section('content')
    @php
        $page = __('messages.iptv_subscription_service');
        $benefits = is_array($page['benefits'] ?? null) ? $page['benefits'] : [];
        $setupPoints = is_array($page['setup_points'] ?? null) ? $page['setup_points'] : [];
    @endphp

    <x-page-title
        :title="$page['title'] ?? __('messages.iptv_subscription_service.title')"
        :breadcrumbs="[
            ['url' => route('home'), 'label' => $page['breadcrumb']['home'] ?? __('messages.nav_home'), 'aria' => 'Go to Home'],
            ['label' => $page['breadcrumb']['current'] ?? __('messages.nav_iptv_subscription_service')],
        ]"
        background="images/background/9.webp"
        :rtl="$isRtl"
        aria-label="IPTV Subscription Service Page Title"
    />

    <section class="services-section-three" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
        aria-labelledby="iptv-subscription-service-title"
        style="background-image:url('{{ asset('images/background/pattern-6.webp') }}')">
        <div class="auto-container">
            <div class="sec-title centered">
                <div class="separator"></div>
                <span class="theme_color">{{ $page['intro_eyebrow'] ?? '' }}</span>
                <h1 id="iptv-subscription-service-title" class="h2">{{ $page['heading'] ?? '' }}</h1>
                <p>{{ $page['intro_text'] ?? '' }}</p>
            </div>

            <div class="row clearfix" role="list">
                @foreach ($benefits as $benefit)
                    <div class="facility-block col-lg-3 col-md-6 col-sm-12" role="listitem">
                        <div class="inner-box" aria-label="{{ $benefit['title'] ?? '' }}">
                            <div class="pattern-layer"
                                style="background-image:url('{{ asset('images/background/pattern-14.webp') }}')"></div>
                            <div class="icon-box {{ $benefit['icon'] ?? 'flaticon-8k' }}" aria-hidden="true"></div>
                            <h5>{{ $benefit['title'] ?? '' }}</h5>
                            <div class="text">{{ $benefit['description'] ?? '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('configure') }}" class="theme-btn btn-style-four">
                    <span class="txt">
                        {{ $page['primary_cta'] ?? __('messages.buy_now') }}
                        <i class="lnr lnr-arrow-right {{ $isRtl ? 'rtl-rotate' : '' }}" aria-hidden="true"></i>
                    </span>
                </a>
                <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                    target="_blank"
                    rel="noopener"
                    class="theme-btn btn-style-two"
                    data-trial
                    data-wa-href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}">
                    <span class="txt">{{ $page['secondary_cta'] ?? __('messages.trial_button') }}</span>
                </a>
            </div>
        </div>
    </section>

    @include('includes._best-packages')

    <section class="internet-section" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
        style="background-image: url('{{ asset('images/background/1.webp') }}')"
        aria-labelledby="iptv-subscription-setup-title">
        <div class="auto-container">
            <div class="clearfix">
                <div class="content-column">
                    <h2 id="iptv-subscription-setup-title" class="h3">{{ $page['setup_title'] ?? '' }}</h2>
                    <div class="text text-dark">{{ $page['setup_text'] ?? '' }}</div>

                    @if ($setupPoints)
                        <ul class="price-list mt-3">
                            @foreach ($setupPoints as $point)
                                <li>{{ $point }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="price">{!! $page['support_note'] ?? '' !!}</div>
                    <a href="{{ route('iptv-applications') }}" class="theme-btn btn-style-four">
                        <span class="txt">
                            {{ $page['apps_cta'] ?? __('messages.nav_iptv_apps') }}
                            <i class="lnr lnr-arrow-right {{ $isRtl ? 'rtl-rotate' : '' }}" aria-hidden="true"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    @include('includes._choose-us')
    @include('includes._testimonials')

    @unless ($isMobile)
        @include('includes._channels-carousel')
    @endunless

    {{-- FAQ Section --}}
    @include('includes._faq-section')

    @include('includes._check-trail')
@stop
