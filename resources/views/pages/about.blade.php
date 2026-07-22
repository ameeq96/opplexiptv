@extends('layouts.default')

@php
    $isDocumentEnglish = app()->getLocale() === 'en';
    $documentAbout = $isDocumentEnglish ? __('document_support.about') : [];
@endphp

@section('title', $isDocumentEnglish ? $documentAbout['page_title'] : __('messages.about.title'))

@push('schema')
    {!! jsonld(seo()->aboutPage(
        $isDocumentEnglish ? $documentAbout['hero']['heading'] : __('messages.about.title'),
        $isDocumentEnglish
            ? $documentAbout['hero']['paragraphs'][1]
            : 'Learn about Opplex IPTV — a premium IPTV provider offering 4K live streaming, 12,000+ channels, a free trial and 24/7 support worldwide.',
        route('about'),
    )) !!}
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/about.css') }}?v={{ @filemtime(public_path('css/about.css')) ?: 1 }}">
    @if ($isDocumentEnglish)
        <link rel="stylesheet" href="{{ asset('css/document-support.css') }}?v={{ @filemtime(public_path('css/document-support.css')) ?: 1 }}">
    @endif
@endpush

@section('content')
    <x-page-title
        :title="$isDocumentEnglish ? $documentAbout['page_title'] : __('messages.about.title_short')"
        :breadcrumbs="$isDocumentEnglish
            ? [['url' => route('home'), 'label' => 'Home'], ['label' => $documentAbout['page_title']]]
            : [['url' => '/', 'label' => __('messages.nav.home')], ['label' => __('messages.nav.about_us')]]"
        background="images/background/7.webp"
        :rtl="$isRtl"
        aria-label="About Us Page" />

    @if ($isDocumentEnglish)
        <main class="document-support document-support--about">
            <section class="document-support__hero" aria-labelledby="document-about-title">
                <div class="auto-container document-support__hero-inner">
                    <span class="document-support__eyebrow">{{ $documentAbout['hero']['eyebrow'] }}</span>
                    <h1 id="document-about-title">{{ $documentAbout['hero']['heading'] }}</h1>
                    <div class="document-support__hero-copy">
                        @foreach ($documentAbout['hero']['paragraphs'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                    <div class="document-support__actions">
                        <a class="document-support__button" href="{{ route('packages') }}">
                            {{ $documentAbout['hero']['plans_cta'] }}
                        </a>
                        <a class="document-support__button document-support__button--outline"
                            href="https://wa.me/16393903194?text={{ urlencode(__('document_support.whatsapp_messages.trial')) }}"
                            target="_blank" rel="noopener">
                            {{ $documentAbout['hero']['trial_cta'] }}
                        </a>
                    </div>
                </div>
            </section>

            <section class="document-support__stats" aria-label="Opplex IPTV service statistics">
                <div class="auto-container document-support__stats-grid">
                    @foreach ($documentAbout['stats'] as $stat)
                        <article class="document-support__stat">
                            <span class="document-support__stat-icon {{ $stat['icon'] }}" aria-hidden="true"></span>
                            <strong>{{ $stat['value'] }}</strong>
                            <span>{{ $stat['label'] }}</span>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="document-support__section" aria-labelledby="document-about-story-title">
                <div class="auto-container document-support__split">
                    <div class="document-support__copy">
                        <span class="document-support__eyebrow">Our Story</span>
                        <h2 id="document-about-story-title">{{ $documentAbout['story']['heading'] }}</h2>
                        @foreach ($documentAbout['story']['paragraphs'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                    <div class="document-support__network-art">
                        <img class="document-support__network-main"
                            src="{{ asset('images/resource/network-4.webp') }}"
                            alt="{{ $documentAbout['story']['image_alt'] }}"
                            loading="lazy" decoding="async">
                        <img class="document-support__network-screen"
                            src="{{ asset('images/resource/network-5.webp') }}"
                            alt=""
                            aria-hidden="true"
                            loading="lazy" decoding="async">
                    </div>
                </div>
            </section>

            <section class="document-support__section document-support__section--tint" aria-labelledby="document-about-values-title">
                <div class="auto-container">
                    <div class="document-support__section-heading">
                        <span class="document-support__eyebrow">What We Stand For</span>
                        <h2 id="document-about-values-title">{{ $documentAbout['values']['heading'] }}</h2>
                    </div>
                    <div class="document-support__card-grid document-support__card-grid--three">
                        @foreach ($documentAbout['values']['items'] as $item)
                            <article class="document-support__card">
                                <span class="document-support__card-icon {{ $item['icon'] }}" aria-hidden="true"></span>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['text'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="document-support__section" aria-labelledby="document-about-regions-title">
                <div class="auto-container">
                    <div class="document-support__section-heading">
                        <span class="document-support__eyebrow">Where We Serve</span>
                        <h2 id="document-about-regions-title">{{ $documentAbout['regions']['heading'] }}</h2>
                        <p>{{ $documentAbout['regions']['intro'] }}</p>
                    </div>
                    <ul class="document-support__region-grid">
                        @foreach ($documentAbout['regions']['items'] as $region)
                            <li>
                                <span class="fa fa-map-marker" aria-hidden="true"></span>
                                <span>{{ $region }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="document-support__outro">{{ $documentAbout['regions']['outro'] }}</p>
                </div>
            </section>

            <section class="document-support__section document-support__section--dark" aria-labelledby="document-about-team-title">
                <div class="auto-container document-support__narrow">
                    <span class="document-support__eyebrow">The Team</span>
                    <h2 id="document-about-team-title">{{ $documentAbout['team']['heading'] }}</h2>
                    @foreach ($documentAbout['team']['paragraphs'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            </section>

            <section class="document-support__section" aria-labelledby="document-about-timeline-title">
                <div class="auto-container">
                    <div class="document-support__section-heading">
                        <span class="document-support__eyebrow">Milestones</span>
                        <h2 id="document-about-timeline-title">{{ $documentAbout['timeline']['heading'] }}</h2>
                    </div>
                    <ol class="document-support__timeline">
                        @foreach ($documentAbout['timeline']['items'] as $milestone)
                            <li>
                                <time datetime="{{ $milestone['year'] }}">{{ $milestone['year'] }}</time>
                                <p>{{ $milestone['text'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            @include('includes._faq-section', [
                'faqItems' => $documentAbout['faq']['items'],
                'faqTitle' => $documentAbout['faq']['heading'],
            ])
        </main>
    @else
        @include('includes._we-provide-unlimited')

        <section class="abtx {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" aria-labelledby="choose-us-title">
            <div class="auto-container">
                <div class="abtx__head">
                    <div class="abtx__titles">
                        <div class="abtx__bar" aria-hidden="true"></div>
                        <h1 id="choose-us-title" class="abtx__title">{!! __('messages.choose_us.title') !!}</h1>
                    </div>

                    <a href="{{ route('packages') }}" class="abtx__cta">
                        {{ __('messages.choose_us.button') }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>

                <div class="abtx-grid" role="list">
                    @foreach ($features as $feature)
                        <a class="abtx-card" role="listitem" href="{{ $feature['link'] }}"
                            aria-label="{{ __('Read more about :title', ['title' => $feature['title']]) }}">
                            <span class="abtx-card__icon {{ $feature['icon'] }}" aria-hidden="true"></span>
                            <h3 class="abtx-card__title">{{ $feature['title'] }}</h3>
                            <p class="abtx-card__text">{{ $feature['description'] }}</p>
                            <span class="abtx-card__arrow" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                    stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        @include('includes._testimonials')

        @unless ($isMobile)
            @include('includes._channels-carousel')
        @endunless

        @include('includes._faq-section')
        @include('includes._check-trail')
    @endif
@stop
