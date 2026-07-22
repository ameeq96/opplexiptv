@extends('layouts.default')
@section('title', app()->getLocale() === 'en' ? __('document_commerce.reseller.page_title') : __('messages.reseller.panel.title'))

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
    @if (app()->getLocale() === 'en')
        <link rel="stylesheet" href="{{ asset('css/document-commerce.css') }}?v={{ @filemtime(public_path('css/document-commerce.css')) ?: 1 }}">
    @endif
@endpush

@section('content')
    @if (app()->getLocale() === 'en')
        @php
            $page = __('document_commerce.reseller');
            $resellerWhatsAppUrl = 'https://wa.me/16393903194?text=' . urlencode($page['hero']['whatsapp_message']);
        @endphp

        <x-page-title
            :title="$page['page_title']"
            :breadcrumbs="[
                ['url' => route('home'), 'label' => __('messages.nav_home'), 'aria' => 'Go to Home'],
                ['label' => $page['page_title']],
            ]"
            background="images/background/7.webp"
            :rtl="$isRtl"
            aria-label="Opplex IPTV reseller panel page"
        />

        <main class="doc-commerce doc-commerce--reseller">
            <section class="dc-hero dc-hero--reseller" aria-labelledby="reseller-document-title">
                <div class="auto-container">
                    <div class="dc-hero__content">
                        <span class="dc-eyebrow">{{ $page['hero']['eyebrow'] }}</span>
                        <h1 id="reseller-document-title" class="dc-title">{{ $page['hero']['heading'] }}</h1>
                        <div class="dc-prose dc-prose--lead">
                            @foreach ($page['hero']['paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                        <div class="dc-actions">
                            <a class="dc-button dc-button--primary" href="#pricing-section">
                                {{ $page['hero']['primary_cta'] }}
                            </a>
                            <a class="dc-button dc-button--secondary" href="{{ $resellerWhatsAppUrl }}"
                                target="_blank" rel="noopener">
                                {{ $page['hero']['secondary_cta'] }}
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="dc-section dc-section--soft" aria-labelledby="reseller-credits-title">
                <div class="auto-container">
                    <div class="dc-section__header">
                        <span class="dc-eyebrow">How Credits Work</span>
                        <h2 id="reseller-credits-title">{{ $page['credits']['heading'] }}</h2>
                    </div>
                    <div class="dc-prose dc-prose--centered">
                        @foreach ($page['credits']['paragraphs'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                    <p class="dc-conversion-label">{{ $page['credits']['label'] }}</p>
                    <dl class="dc-conversion-grid">
                        @foreach ($page['credits']['items'] as $item)
                            <div class="dc-conversion-card">
                                <dt>{{ $item['value'] }}</dt>
                                <dd><span aria-hidden="true">=</span> {{ $item['label'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <p class="dc-section-note">{{ $page['credits']['note'] }}</p>
                </div>
            </section>

            @include('includes._best-packages', ['initialMode' => 'reseller'])

            <section class="dc-section" aria-labelledby="reseller-account-title">
                <div class="auto-container">
                    <div class="dc-section__header">
                        <span class="dc-eyebrow">Your Reseller Account</span>
                        <h2 id="reseller-account-title">{{ $page['account']['heading'] }}</h2>
                    </div>
                    <div class="dc-content-grid dc-content-grid--three" role="list">
                        @foreach ($page['account']['items'] as $item)
                            <article class="dc-content-card" role="listitem">
                                <span class="dc-content-card__check fa fa-check" aria-hidden="true"></span>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            @include('includes._we-provide-unlimited')

            <section class="dc-section dc-section--soft" aria-labelledby="reseller-audience-title">
                <div class="auto-container">
                    <div class="dc-section__header">
                        <span class="dc-eyebrow">Who Should Resell</span>
                        <h2 id="reseller-audience-title">{{ $page['audience']['heading'] }}</h2>
                        <p>{{ $page['audience']['intro'] }}</p>
                    </div>
                    <div class="dc-content-grid dc-content-grid--four" role="list">
                        @foreach ($page['audience']['items'] as $item)
                            <article class="dc-content-card dc-content-card--numbered" role="listitem">
                                <span class="dc-content-card__number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            @include('includes._testimonials')

            @unless ($isMobile)
                @include('includes._channels-carousel')
            @endunless

            @include('includes._faq-section', [
                'faqItems' => $page['faq']['items'],
                'faqTitle' => $page['faq']['heading'],
            ])

            @include('includes._check-trail')
        </main>
    @else
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
    @endif
@stop
