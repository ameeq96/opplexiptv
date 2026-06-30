@extends('layouts.default')

@section('title', __('messages.about.title'))

@push('schema')
    {!! jsonld(seo()->aboutPage(
        __('messages.about.title'),
        'Learn about Opplex IPTV — a premium IPTV provider offering 4K live streaming, 12,000+ channels, a free trial and 24/7 support worldwide.',
        route('about'),
    )) !!}
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/about.css') }}?v={{ @filemtime(public_path('css/about.css')) ?: 1 }}">
@endpush

@section('content')
    <x-page-title :title="__('messages.about.title_short')" :breadcrumbs="[['url' => '/', 'label' => __('messages.nav.home')], ['label' => __('messages.nav.about_us')]]" background="images/background/7.webp" :rtl="$isRtl"
        aria-label="About Us Page" />

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

    {{-- FAQ Section --}}
    @include('includes._faq-section')

    @include('includes._check-trail')

@stop
