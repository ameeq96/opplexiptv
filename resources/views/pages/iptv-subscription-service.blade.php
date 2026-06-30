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

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/iptv-service.css') }}">
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

    @php
        $waTrial = 'https://wa.me/16393903194?text=' . urlencode(__('messages.whatsapp_trial'));
    @endphp

    <section class="ipts-hero {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
        aria-labelledby="iptv-subscription-service-title">
        <div class="auto-container">
            <div class="ipts-hero__head">
                @if (!empty($page['intro_eyebrow']))
                    <span class="ipts-eyebrow">{{ $page['intro_eyebrow'] }}</span>
                @endif
                <h1 id="iptv-subscription-service-title" class="ipts-hero__title">{{ $page['heading'] ?? '' }}</h1>
                <p class="ipts-hero__text">{{ $page['intro_text'] ?? '' }}</p>

                <div class="ipts-cta">
                    <a href="{{ route('configure') }}" class="ipts-btn ipts-btn--primary">
                        {{ $page['primary_cta'] ?? __('messages.buy_now') }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <a href="{{ $waTrial }}" target="_blank" rel="noopener"
                        class="ipts-btn ipts-btn--wa" data-trial data-wa-href="{{ $waTrial }}">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zM12.05 21.785h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884z"/></svg>
                        {{ $page['secondary_cta'] ?? __('messages.trial_button') }}
                    </a>
                </div>
            </div>

            @if ($benefits)
                <div class="ipts-benefits" role="list">
                    @foreach ($benefits as $benefit)
                        <article class="ipts-card" role="listitem">
                            <span class="ipts-card__icon {{ $benefit['icon'] ?? 'flaticon-8k' }}" aria-hidden="true"></span>
                            <h3>{{ $benefit['title'] ?? '' }}</h3>
                            <p>{{ $benefit['description'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @include('includes._best-packages')

    <section class="ipts-setup {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
        aria-labelledby="iptv-subscription-setup-title">
        <div class="auto-container">
            <div class="ipts-setup__panel">
                <div class="ipts-setup__intro">
                    <h2 id="iptv-subscription-setup-title" class="ipts-setup__title">{{ $page['setup_title'] ?? '' }}</h2>
                    <p class="ipts-setup__text">{{ $page['setup_text'] ?? '' }}</p>

                    @if (!empty($page['support_note']))
                        <div class="ipts-support">{!! $page['support_note'] !!}</div>
                    @endif

                    <div class="ipts-setup__cta">
                        <a href="{{ route('iptv-applications') }}" class="ipts-btn ipts-btn--primary">
                            {{ $page['apps_cta'] ?? __('messages.nav_iptv_apps') }}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                </div>

                @if ($setupPoints)
                    <ul class="ipts-checklist">
                        @foreach ($setupPoints as $point)
                            <li>
                                <span class="tick" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                        stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                <span>{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
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
