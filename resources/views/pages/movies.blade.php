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
                        <a class="document-product-button" href="{{ route('iptv-subscription-service') }}">
                            {{ $documentPage['hero']['plans'] }}
                        </a>
                        <a class="document-product-button document-product-button--secondary"
                           href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                           target="_blank" rel="noopener" data-trial
                           data-wa-href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}">
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

            <!-- MixitUp Gallery -->
            <div class="mixitup-gallery">

                <!-- Filter -->
                <div class="filters clearfix">
                    <ul class="filter-tabs filter-btns text-center clearfix" role="group" aria-label="{{ __('document_ui.movies.filters_aria') }}">
                        <li class="active filter" role="button" tabindex="0" data-filter="all">{{ __('messages.all') }}</li>
                        <li class="filter" role="button" tabindex="0" data-filter=".movies">{{ __('messages.movies') }}</li>
                        <li class="filter" role="button" tabindex="0" data-filter=".series">{{ __('messages.series') }}</li>
                        <li class="filter" role="button" tabindex="0" data-filter=".cartoons">{{ __('messages.cartoons') }}</li>
                    </ul>
                </div>

                <!-- Search -->
                <div class="search-bar mb-4 d-flex justify-content-center">
                    <form method="GET" action="{{ route('movies') }}" data-movies-search-form
                        aria-label="{{ __('document_ui.movies.search_aria') }}"
                        class="d-flex align-items-center w-100" style="max-width: 800px;">
                        <input type="text" name="search" class="form-control mx-2"
                            placeholder="{{ __('messages.search_placeholder') }}" value="{{ $query ?? '' }}"
                            style="height: 50px; font-size: 1.2rem; flex: 1;" aria-label="{{ __('document_ui.movies.search_input_aria') }}">
                        <button type="submit" class="btn btn-search px-4" style="height: 50px; font-size: 1.2rem;">
                            {{ __('messages.search_button') }}
                        </button>
                    </form>
                </div>

                @php
                    $hasAny =
                        $filteredMovies['movies']->isNotEmpty() ||
                        $filteredMovies['series']->isNotEmpty() ||
                        $filteredMovies['cartoons']->isNotEmpty();
                @endphp

                @unless ($hasAny)
                    <p class="text-center text-muted my-5">{{ __('messages.no_results') }}</p>
                @endunless

                <div class="filter-list row clearfix">
                    {{-- Movies --}}
                    @foreach ($filteredMovies['movies'] as $movie)
                        <div class="feature-block style-two mix all movies">
                            <div class="inner-box">
                                <div class="image">
                                    <a href="#" data-trailer-endpoint="{{ $movie['trailer_endpoint'] }}" class="lightbox-image video-box"
                                        aria-label="{{ __('document_ui.movies.watch_trailer_aria', ['title' => $movie['title']]) }}">
                                        <span class="flaticon-play-arrow"><i class="ripple"></i></span>
                                    </a>
                                    <img src="{{ $movie['poster_url'] }}" alt="{{ __('document_ui.movies.poster_alt', ['title' => $movie['title']]) }}"
                                        loading="lazy" decoding="async" width="300" height="450" />
                                    <div class="overlay-box">
                                        <ul class="post-meta" aria-label="{{ __('document_ui.movies.movie_rating_aria') }}">
                                            <li><span class="icon fa fa-star"></span>{{ $movie['vote'] }}</li>
                                            <li><span class="icon fa fa-comment"></span>25</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="lower-content">
                                    <div class="clearfix">
                                        <div class="pull-left">
                                            <h6>
                                                <a href="{{ route('packages', ['direct' => 1]) }}"
                                                    aria-label="{{ __('document_ui.movies.subscribe_aria', ['title' => $movie['title']]) }}">
                                                    {{ $movie['title'] }}
                                                </a>
                                            </h6>
                                        </div>
                                        <div class="pull-right">
                                            <div class="year">{{ $movie['year'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Series --}}
                    @foreach ($filteredMovies['series'] as $series)
                        <div class="feature-block style-two mix all series">
                            <div class="inner-box">
                                <div class="image">
                                    <a href="#" data-trailer-endpoint="{{ $series['trailer_endpoint'] }}" class="lightbox-image video-box"
                                        aria-label="{{ __('document_ui.movies.watch_trailer_aria', ['title' => $series['title']]) }}">
                                        <span class="flaticon-play-arrow"><i class="ripple"></i></span>
                                    </a>
                                    <img src="{{ $series['poster_url'] }}" alt="{{ __('document_ui.movies.poster_alt', ['title' => $series['title']]) }}"
                                        loading="lazy" decoding="async" width="300" height="450" />
                                    <div class="overlay-box">
                                        <ul class="post-meta" aria-label="{{ __('document_ui.movies.series_rating_aria') }}">
                                            <li><span class="icon fa fa-star"></span>{{ $series['vote'] }}</li>
                                            <li><span class="icon fa fa-comment"></span>25</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="lower-content">
                                    <div class="clearfix">
                                        <div class="pull-left">
                                            <h6>
                                                <a href="{{ route('packages', ['direct' => 1]) }}">{{ $series['title'] }}</a>
                                            </h6>
                                        </div>
                                        <div class="pull-right">
                                            <div class="year">{{ $series['year'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Cartoons (Animation) --}}
                    @foreach ($filteredMovies['cartoons'] as $cartoon)
                        <div class="feature-block style-two mix all cartoons">
                            <div class="inner-box">
                                <div class="image">
                                    <a href="#" data-trailer-endpoint="{{ $cartoon['trailer_endpoint'] }}" class="lightbox-image video-box"
                                        aria-label="{{ __('document_ui.movies.watch_trailer_aria', ['title' => $cartoon['title']]) }}">
                                        <span class="flaticon-play-arrow"><i class="ripple"></i></span>
                                    </a>
                                    <img src="{{ $cartoon['poster_url'] }}" alt="{{ __('document_ui.movies.poster_alt', ['title' => $cartoon['title']]) }}"
                                        loading="lazy" decoding="async" width="300" height="450" />
                                    <div class="overlay-box">
                                        <ul class="post-meta" aria-label="{{ __('document_ui.movies.cartoon_rating_aria') }}">
                                            <li><span class="icon fa fa-star"></span>{{ $cartoon['vote'] }}</li>
                                            <li><span class="icon fa fa-comment"></span>25</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="lower-content">
                                    <div class="clearfix">
                                        <div class="pull-left">
                                            <h6>
                                                <a href="{{ route('packages', ['direct' => 1]) }}">{{ $cartoon['title'] }}</a>
                                            </h6>
                                        </div>
                                        <div class="pull-right">
                                            <div class="year">{{ $cartoon['year'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </section>

    {{-- Pagination --}}
    <div class="pagination-wrapper mvx-pager mb-4">
        <ul class="pagination justify-content-center flex-nowrap" style="overflow-x:auto; padding:0 10px;">
            {{-- Prev --}}
            @if ($page > 1)
                <li class="page-item me-1">
                    <a class="page-link" href="{{ route('movies', ['page' => $page - 1, 'search' => $query ?: null]) }}"
                        aria-label="{{ __('document_ui.movies.previous_page_aria') }}">&laquo;</a>
                </li>
            @endif

            {{-- Window --}}
            @for ($i = $pageStart; $i <= $pageEnd; $i++)
                <li class="page-item {{ $i == $page ? 'active' : '' }} me-1">
                    <a class="page-link" href="{{ route('movies', ['page' => $i, 'search' => $query ?: null]) }}"
                        aria-label="{{ __('document_ui.movies.page_aria', ['page' => $i]) }}">
                        {{ $i }}
                    </a>
                </li>
            @endfor

            {{-- Next --}}
            @if ($page < $totalPages)
                <li class="page-item ms-1">
                    <a class="page-link" href="{{ route('movies', ['page' => $page + 1, 'search' => $query ?: null]) }}"
                        aria-label="{{ __('document_ui.movies.next_page_aria') }}">{{ __('messages.next') }} &raquo;</a>
                </li>
            @endif
        </ul>
    </div>

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
                            <a class="document-product-button document-product-button--ghost" href="{{ route('iptv-subscription-service') }}">
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
