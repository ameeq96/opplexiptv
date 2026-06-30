@extends('layouts.default')
@section('title', __('messages.app.title'))

@php
    $platformLabels = [
        'android' => 'Android',
        'ios'     => 'iOS',
        'windows' => 'Windows',
        'macos'   => 'macOS',
        'linux'   => 'Linux',
    ];
    $platformIcons = [
        'android' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.6 9.48l1.84-3.18a.4.4 0 0 0-.69-.4l-1.86 3.23a11.43 11.43 0 0 0-9.78 0L5.25 5.9a.4.4 0 1 0-.69.4L6.4 9.48A10.81 10.81 0 0 0 1 18h22a10.81 10.81 0 0 0-5.4-8.52M7 15.25A1.25 1.25 0 1 1 8.25 14 1.25 1.25 0 0 1 7 15.25m10 0A1.25 1.25 0 1 1 18.25 14 1.25 1.25 0 0 1 17 15.25"/></svg>',
        'ios'     => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.05 12.04c-.03-2.6 2.13-3.85 2.23-3.91-1.21-1.78-3.1-2.02-3.77-2.05-1.6-.16-3.13.94-3.94.94-.81 0-1.72-.92-2.83-.9-1.46.02-2.8.85-3.55 2.16-1.51 2.62-.39 6.5 1.09 8.63.72 1.04 1.58 2.21 2.71 2.17 1.09-.04 1.5-.7 2.82-.7 1.31 0 1.69.7 2.83.68 1.17-.02 1.91-1.06 2.63-2.11.83-1.21 1.17-2.38 1.19-2.44-.03-.01-2.28-.88-2.24-3.18M14.7 4.2c.6-.73 1.01-1.74.9-2.76-.87.04-1.92.58-2.54 1.31-.55.64-1.04 1.67-.91 2.65.97.08 1.96-.49 2.55-1.2"/></svg>',
        'macos'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.05 12.04c-.03-2.6 2.13-3.85 2.23-3.91-1.21-1.78-3.1-2.02-3.77-2.05-1.6-.16-3.13.94-3.94.94-.81 0-1.72-.92-2.83-.9-1.46.02-2.8.85-3.55 2.16-1.51 2.62-.39 6.5 1.09 8.63.72 1.04 1.58 2.21 2.71 2.17 1.09-.04 1.5-.7 2.82-.7 1.31 0 1.69.7 2.83.68 1.17-.02 1.91-1.06 2.63-2.11.83-1.21 1.17-2.38 1.19-2.44-.03-.01-2.28-.88-2.24-3.18M14.7 4.2c.6-.73 1.01-1.74.9-2.76-.87.04-1.92.58-2.54 1.31-.55.64-1.04 1.67-.91 2.65.97.08 1.96-.49 2.55-1.2"/></svg>',
        'windows' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 5.6 10.5 4.6v7H3V5.6M11.5 4.45 21 3v8.6h-9.5V4.45M3 12.4h7.5v7L3 18.4v-6M11.5 12.4H21V21l-9.5-1.35V12.4"/></svg>',
        'linux'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>',
    ];
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/iptv-apps.css') }}">
@endpush

@section('content')
    <x-page-title
        :title="__('messages.app.heading')"
        :breadcrumbs="[
            ['url' => '/', 'label' => __('messages.app.breadcrumb.home')],
            ['label' => __('messages.app.breadcrumb.current')],
        ]"
        background="images/background/10.webp"
        :rtl="$isRtl"
        aria-label="App Download Page"
    />

    <section class="iptva {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" aria-labelledby="iptva-title">
        <div class="auto-container">

            <div class="iptva-hero">
                <span class="iptva-hero__chip" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2.5" y="3.5" width="13" height="10" rx="2"/><path d="M2.5 17h13M9 13.5V17"/>
                        <rect x="17.5" y="8.5" width="4" height="11.5" rx="1"/>
                    </svg>
                </span>
                <h1 id="iptva-title" class="iptva-hero__title">{{ __('messages.app.download_heading') }}</h1>
                @if (__('messages.app.tagline') !== 'messages.app.tagline')
                    <p class="iptva-hero__text">{{ __('messages.app.tagline') }}</p>
                @endif
            </div>

            @foreach ($platforms as $platform => $apps)
                @php $label = $platformLabels[$platform] ?? ucfirst($platform); @endphp
                <section class="iptva-platform" aria-label="{{ __('messages.app.platform', ['platform' => $label]) }}">
                    <div class="iptva-platform__head">
                        <span class="iptva-platform__icon">
                            {!! $platformIcons[$platform] ?? $platformIcons['linux'] !!}
                        </span>
                        <h2 class="iptva-platform__title">{{ __('messages.app.platform', ['platform' => $label]) }}</h2>
                        <span class="iptva-platform__count">{{ count($apps) }}</span>
                        <span class="iptva-platform__rule" aria-hidden="true"></span>
                    </div>

                    <div class="iptva-grid">
                        @foreach ($apps as $app)
                            <a target="_blank" rel="noopener noreferrer"
                               href="{{ $app['href'] }}"
                               class="iptva-app"
                               data-keywords="{{ $app['keywords'] }}"
                               aria-label="{{ __('messages.app.download_button', ['version' => $app['version']]) }} ({{ $label }})">
                                <span class="iptva-app__icon">
                                    <img width="40" height="40" loading="lazy" decoding="async"
                                         src="{{ $app['image_url'] }}" alt="{{ $app['version'] }}">
                                </span>
                                <span class="iptva-app__name">{{ $app['version'] }}</span>
                                <span class="iptva-app__dl" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 11l5 5 5-5M5 21h14"/></svg>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach

            @if (__('messages.app.compatibility_note') !== 'messages.app.compatibility_note')
                <div class="iptva-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/></svg>
                    <em>{{ __('messages.app.compatibility_note') }}</em>
                </div>
            @endif
        </div>
    </section>

    {{-- FAQ Section --}}
    @include('includes._faq-section')
@endsection
