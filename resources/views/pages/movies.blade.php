@extends('layouts.default')
@section('title', 'Movies | Opplex IPTV - Stream the Latest Movies and Classic Hits')
@section('content')

@php
    use Jenssegers\Agent\Agent;
    $agent = new Agent();
    $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
@endphp

    <!-- Page Title -->
    <section class="movie-page-section">

		

        <div class="auto-container">
            <!-- MixitUp Gallery -->
            <div class="mixitup-gallery">

                <!-- Filter -->
                <div class="filters clearfix">
                    <ul class="filter-tabs filter-btns text-center clearfix">
                        <li class="active filter" data-role="button" data-filter="all">All</li>
                        <li class="filter" data-role="button" data-filter=".movies">Movies</li>
                        <li class="filter" data-role="button" data-filter=".series">Series</li>
                        <li class="filter" data-role="button" data-filter=".cartoons">Cartoons</li>
                    </ul>
                </div>

				<div class="search-bar mb-4 d-flex justify-content-center">
					<form method="GET" action="{{ route('movies') }}" class="d-flex align-items-center w-100" style="max-width: 800px;">
						<input 
							type="text" 
							name="search" 
							class="form-control mx-2" 
							placeholder="Search for movies or series..." 
							value="{{ $query ?? '' }}" 
							style="height: 50px; font-size: 1.2rem; flex: 1;">
						<button 
							type="submit" 
							class="btn btn-search px-4" 
							style="height: 50px; font-size: 1.2rem;">
							Search
						</button>
					</form>
				</div>
				

                <div class="filter-list row clearfix">
                    @foreach ($filteredMovies['movies'] as $movie)
                        <div class="feature-block style-two mix all movies">
                            <div class="inner-box">
                                <div class="image">
                                    <a href="{{ $movie['trailer_url'] }}" class="lightbox-image video-box">
                                        <span class="flaticon-play-arrow"><i class="ripple"></i></span>
                                    </a>
                                    <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}"
                                        alt="{{ $movie['title'] ?? $movie['name'] }}" />
                                    <div class="overlay-box">
                                        <ul class="post-meta">
                                            <li><span class="icon fa fa-star"></span>{{ $movie['vote_average'] }}</li>
                                            <li><span class="icon fa fa-comment"></span>25</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="lower-content">
                                    <div class="clearfix">
                                        <div class="pull-left">
                                            <h6><a
                                                    href="{{ route('packages') }}">{{ $movie['title'] ?? $movie['name'] }}</a>
                                            </h6>
                                        </div>
                                        <div class="pull-right">
                                            <div class="year">
                                                {{ substr($movie['release_date'] ?? $movie['first_air_date'], 0, 4) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @foreach ($filteredMovies['series'] as $series)
                        <div class="feature-block style-two mix all series">
                            <div class="inner-box">
                                <div class="image">
                                    <a href="{{ $series['trailer_url'] }}" class="lightbox-image video-box">
                                        <span class="flaticon-play-arrow"><i class="ripple"></i></span>
                                    </a>
                                    <img src="https://image.tmdb.org/t/p/w500{{ $series['poster_path'] }}"
                                        alt="{{ $series['title'] ?? $series['name'] }}" />
                                    <div class="overlay-box">
                                        <ul class="post-meta">
                                            <li><span class="icon fa fa-star"></span>{{ $series['vote_average'] }}</li>
                                            <li><span class="icon fa fa-comment"></span>25</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="lower-content">
                                    <div class="clearfix">
                                        <div class="pull-left">
                                            <h6><a
                                                    href="{{ route('packages') }}">{{ $series['title'] ?? $series['name'] }}</a>
                                            </h6>
                                        </div>
                                        <div class="pull-right">
                                            <div class="year">
                                                {{ substr($series['release_date'] ?? $series['first_air_date'], 0, 4) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($filteredMovies['cartoons'] as $cartoon)
                        <div class="feature-block style-two mix all cartoons">
                            <div class="inner-box">
                                <div class="image">
                                    <a href="{{ $cartoon['trailer_url'] }}" class="lightbox-image video-box">
                                        <span class="flaticon-play-arrow"><i class="ripple"></i></span>
                                    </a>
                                    <img src="https://image.tmdb.org/t/p/w500{{ $cartoon['poster_path'] }}"
                                        alt="{{ $cartoon['title'] ?? $cartoon['name'] }}" />
                                    <div class="overlay-box">
                                        <ul class="post-meta">
                                            <li><span class="icon fa fa-star"></span>{{ $cartoon['vote_average'] }}</li>
                                            <li><span class="icon fa fa-comment"></span>25</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="lower-content">
                                    <div class="clearfix">
                                        <div class="pull-left">
                                            <h6><a
                                                    href="{{ route('packages') }}">{{ $cartoon['title'] ?? $cartoon['name'] }}</a>
                                            </h6>
                                        </div>
                                        <div class="pull-right">
                                            <div class="year">
                                                {{ substr($cartoon['release_date'] ?? $cartoon['first_air_date'], 0, 4) }}
                                            </div>
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

    <!-- End Page Title -->

    <div class="pagination-wrapper mb-4">
        <ul class="pagination justify-content-center">
            <!-- Previous Arrow -->
            @if ($page > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ route('movies', ['page' => $page - 1, 'search' => $query]) }}">
                </li>
            @endif

            <!-- Dynamic Pagination -->
            @php
                $start = max(1, $page - 5);
                $end = min($start + 9, $totalPages);
                if ($end - $start < 9) {
                    $start = max(1, $end - 9);
                }
            @endphp

            @for ($i = $start; $i <= $end; $i++)
                <li class="page-item {{ $i == $page ? 'active' : '' }}">
                    <a class="page-link" href="{{ route('movies', ['page' => $i]) }}">{{ $i }}</a>
                </li>
            @endfor

            <!-- Next Arrow -->
            @if ($page < $totalPages)
                <li class="page-item">
                    <a class="page-link" href="{{ route('movies', ['page' => $page + 1]) }}">
                        Next &raquo;
                    </a>
                </li>
            @endif
        </ul>
    </div>



@stop
