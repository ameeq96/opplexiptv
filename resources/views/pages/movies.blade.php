@extends('layouts.default')
@php
    $isDocumentEnglish = true;
    $documentPage = __('document_product.movies');
@endphp
@section('title', $isDocumentEnglish ? $documentPage['hero']['heading'] : __('messages.movies_title'))

@push('schema')
    {!! jsonld(seo()->collectionPage(
        $documentPage['hero']['heading'],
        $documentPage['hero']['text'],
        route('movies'),
    )) !!}
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/movies.css') }}?v={{ @filemtime(public_path('css/movies.css')) ?: 1 }}">
    @if ($isDocumentEnglish)
        <link rel="stylesheet" href="{{ asset('css/document-product.css') }}?v={{ @filemtime(public_path('css/document-product.css')) ?: 1 }}">
    @endif
@endpush

@section('content')
    <section class="movie-page-section mvx {{ $isDocumentEnglish ? 'document-product-page document-product-movies' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="auto-container">
            @if ($isDocumentEnglish)
                <header class="document-product-hero document-product-hero--movies">
                    <span class="document-product-eyebrow">{{ __('document_ui.movies.eyebrow') }}</span>
                    <h1>{{ $documentPage['hero']['heading'] }}</h1>
                    <p>{{ $documentPage['hero']['text'] }}</p>
                    <div class="document-product-actions">
                        <a class="document-product-button" href="{{ route('packages') }}">
                            {{ $documentPage['hero']['plans'] }}
                        </a>
                        <a class="document-product-button document-product-button--secondary"
                           href="https://wa.me/{{ config('services.whatsapp.number') }}?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                           target="_blank" rel="noopener" data-trial
                           data-wa-href="https://wa.me/{{ config('services.whatsapp.number') }}?text={{ urlencode(__('messages.whatsapp_trial')) }}">
                            {{ $documentPage['hero']['trial'] }}
                        </a>
                    </div>
                </header>
            @else
                <h1 class="h3 text-center my-3">{{ __('messages.movies_title') }}</h1>

                <h2 class="h4 text-center text-muted mb-4">
                    {{ __('messages.movies_subheading') }}
                </h2>
            @endif

        </div>
    </section>

    @if ($isDocumentEnglish)
        <section class="document-product-section document-product-section--light" aria-labelledby="document-library-title">
            <div class="auto-container">
                <header class="document-product-section__heading">
                    <h2 id="document-library-title">{{ $documentPage['library']['heading'] }}</h2>
                </header>
                <div class="document-product-card-grid document-product-card-grid--three">
                    @foreach ($documentPage['library']['items'] as $item)
                        <article class="document-product-card">
                            <span class="document-product-card__number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="document-product-section" aria-labelledby="document-how-watch-title">
            <div class="auto-container">
                <div class="document-product-split">
                    <div>
                        <span class="document-product-eyebrow">{{ __('document_ui.movies.included_eyebrow') }}</span>
                        <h2 id="document-how-watch-title">{{ $documentPage['how_to_watch']['heading'] }}</h2>
                        <p>{{ $documentPage['how_to_watch']['intro'] }}</p>
                        <ol class="document-product-step-list">
                            @foreach ($documentPage['how_to_watch']['steps'] as $step)
                                <li><span>{{ $loop->iteration }}</span><p>{{ $step }}</p></li>
                            @endforeach
                        </ol>
                    </div>
                    <aside class="document-product-callout">
                        <p>{{ $documentPage['how_to_watch']['devices'] }}</p>
                        <p>{{ $documentPage['how_to_watch']['support'] }}</p>
                    </aside>
                </div>
            </div>
        </section>

        <section class="document-product-section document-product-section--navy" aria-labelledby="document-movie-devices-title">
            <div class="auto-container">
                <div class="document-product-split document-product-split--devices">
                    <div>
                        <span class="document-product-eyebrow document-product-eyebrow--inverse">{{ __('document_ui.shared.device_compatibility') }}</span>
                        <h2 id="document-movie-devices-title">{{ $documentPage['devices']['heading'] }}</h2>
                        <p>{{ $documentPage['devices']['intro'] }}</p>
                        <ul class="document-product-check-grid">
                            @foreach ($documentPage['devices']['items'] as $device)
                                <li>{{ $device }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="document-product-callout document-product-callout--inverse">
                        <p>{{ $documentPage['devices']['quality'] }}</p>
                        <div class="document-product-actions">
                            <a class="document-product-button" href="{{ route('iptv-applications') }}">
                                {{ $documentPage['devices']['apps'] }}
                            </a>
                            <a class="document-product-button document-product-button--ghost" href="{{ route('packages') }}">
                                {{ $documentPage['devices']['plans'] }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @include('includes._faq-section', [
            'faqItems' => $documentPage['faq']['items'],
            'faqTitle' => $documentPage['faq']['heading'],
        ])
    @else
        {{-- FAQ Section --}}
        @include('includes._faq-section')
    @endif
@stop
