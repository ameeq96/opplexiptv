@extends('layouts.default')
@section('title', __('messages.movies_title'))

@section('content')
<section class="movie-page-section">
    <div class="auto-container">
        <!-- MixitUp Gallery -->
        <div class="mixitup-gallery">

            <!-- Filter -->
            <div class="filters clearfix">
                <ul class="filter-tabs filter-btns text-center clearfix" role="tablist" aria-label="Content filters">
                    <li class="active filter" data-role="button" data-filter="all">{{ __('messages.all') }}</li>
                    <li class="filter" data-role="button" data-filter=".movies">{{ __('messages.movies') }}</li>
                    <li class="filter" data-role="button" data-filter=".series">{{ __('messages.series') }}</li>
                    <li class="filter" data-role="button" data-filter=".cartoons">{{ __('messages.cartoons') }}</li>
                </ul>
            </div>

            <!-- Search -->
            <div class="search-bar mb-4 d-flex justify-content-center">
                <form method="GET" action="{{ route('movies') }}" aria-label="Search Movies and Series"
                      class="d-flex align-items-center w-100" style="max-width: 800px;">
                    <input type="text" name="search" class="form-control mx-2"
                           placeholder="{{ __('messages.search_placeholder') }}"
                           value="{{ $query ?? '' }}"
                           style="height: 50px; font-size: 1.2rem; flex: 1;"
                           aria-label="Search by title or keyword">
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

            @unless($hasAny)
                <p class="text-center text-muted my-5">{{ __('messages.no_results') }}</p>
            @endunless

            <div class="filter-list row clearfix">
                {{-- Movies --}}
                @foreach ($filteredMovies['movies'] as $movie)
                    <div class="feature-block style-two mix all movies">
                        <div class="inner-box">
                            <div class="image">
                                <a href="{{ $movie['trailer_url'] }}" class="lightbox-image video-box"
                                   aria-label="Watch trailer of {{ $movie['title'] }}">
                                    <span class="flaticon-play-arrow"><i class="ripple"></i></span>
                                </a>
                                <img src="{{ $movie['poster_url'] }}"
                                     alt="Poster of {{ $movie['title'] }}"
                                     loading="lazy" width="300" height="450" />
                                <div class="overlay-box">
                                    <ul class="post-meta" aria-label="Movie rating">
                                        <li><span class="icon fa fa-star"></span>{{ $movie['vote'] }}</li>
                                        <li><span class="icon fa fa-comment"></span>25</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="lower-content">
                                <div class="clearfix">
                                    <div class="pull-left">
                                        <h6>
                                            <a href="{{ route('packages') }}"
                                               aria-label="Subscribe to watch {{ $movie['title'] }}">
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
                                <a href="{{ $series['trailer_url'] }}" class="lightbox-image video-box"
                                   aria-label="Watch trailer of {{ $series['title'] }}">
                                    <span class="flaticon-play-arrow"><i class="ripple"></i></span>
                                </a>
                                <img src="{{ $series['poster_url'] }}"
                                     alt="Poster of {{ $series['title'] }}"
                                     loading="lazy" width="300" height="450" />
                                <div class="overlay-box">
                                    <ul class="post-meta" aria-label="Series rating">
                                        <li><span class="icon fa fa-star"></span>{{ $series['vote'] }}</li>
                                        <li><span class="icon fa fa-comment"></span>25</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="lower-content">
                                <div class="clearfix">
                                    <div class="pull-left">
                                        <h6>
                                            <a href="{{ route('packages') }}">{{ $series['title'] }}</a>
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
                                <a href="{{ $cartoon['trailer_url'] }}" class="lightbox-image video-box"
                                   aria-label="Watch trailer of {{ $cartoon['title'] }}">
                                    <span class="flaticon-play-arrow"><i class="ripple"></i></span>
                                </a>
                                <img src="{{ $cartoon['poster_url'] }}"
                                     alt="Poster of {{ $cartoon['title'] }}"
                                     loading="lazy" width="300" height="450" />
                                <div class="overlay-box">
                                    <ul class="post-meta" aria-label="Cartoon rating">
                                        <li><span class="icon fa fa-star"></span>{{ $cartoon['vote'] }}</li>
                                        <li><span class="icon fa fa-comment"></span>25</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="lower-content">
                                <div class="clearfix">
                                    <div class="pull-left">
                                        <h6>
                                            <a href="{{ route('packages') }}">{{ $cartoon['title'] }}</a>
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
<div class="pagination-wrapper mb-4">
    <ul class="pagination justify-content-center flex-nowrap" style="overflow-x:auto; padding:0 10px;">
        {{-- Prev --}}
        @if ($page > 1)
            <li class="page-item me-1">
                <a class="page-link"
                   href="{{ route('movies', ['page' => $page - 1, 'search' => $query ?: null]) }}"
                   aria-label="Go to previous page">&laquo;</a>
            </li>
        @endif

        {{-- Window --}}
        @for ($i = $pageStart; $i <= $pageEnd; $i++)
            <li class="page-item {{ $i == $page ? 'active' : '' }} me-1">
                <a class="page-link"
                   href="{{ route('movies', ['page' => $i, 'search' => $query ?: null]) }}"
                   aria-label="Go to page {{ $i }}">
                    {{ $i }}
                </a>
            </li>
        @endfor

        {{-- Next --}}
        @if ($page < $totalPages)
            <li class="page-item ms-1">
                <a class="page-link"
                   href="{{ route('movies', ['page' => $page + 1, 'search' => $query ?: null]) }}"
                   aria-label="Go to next page">{{ __('messages.next') }} &raquo;</a>
            </li>
        @endif
    </ul>
</div>
@stop
