@if ($agent->isMobile())
    <section class="hero-section-mobile" aria-label="Opplex IPTV Hero Section – Start Your IPTV Trial in HD/4K">
        <div class="container">
            <p class="subtitle">{{ __('messages.subtitle') }}</p>

            <h2 class="heading">
                {{ __('messages.heading-mobile') }}
            </h2>

            <p class="description">
                {{ __('messages.description_prefix') }} <strong>Opplex IPTV</strong>
                {{ __('messages.description_suffix') }}
            </p>

            <div class="btn-group">
                <a href="https://wa.me/923121108582" target="_blank" class="btn btn-primary"
                    aria-label="Start Free IPTV Trial on WhatsApp">
                    {{ __('messages.start_trial') }} <span>↗</span>
                </a>
                <a href="https://wa.me/923121108582" target="_blank" class="btn btn-outline"
                    aria-label="View IPTV Pricing Plans">
                    {{ __('messages.see_pricing') }} <span>➤</span>
                </a>
            </div>
        </div>
    </section>
@else
    <section class="main-slider-two" aria-label="Opplex IPTV HD/4K Movie Slider – Discover Our Content">
        <div class="main-slider-carousel owl-carousel owl-theme">
            @foreach ($displayMovies as $index => $movie)
                <div class="slide {{ $index !== 0 ? 'lazy-background' : '' }}"
                    @if ($index !== 0) data-bg="{{ $movie['webp_image_url'] }}" loading="lazy" @endif>
                    @if ($index === 0)
                        <img src="{{ $movie['webp_image_url'] }}"
                            alt="{{ $movie['title'] ?? $movie['name'] }} - IPTV Movie Poster"
                            aria-label="IPTV Movie Poster - {{ $movie['title'] ?? $movie['name'] }}">
                    @endif

                    <div class="auto-container custom-height">
                        <div class="content-boxed">
                            <div class="inner-box slider-font">
                                <h1>{{ $movie['title'] ?? $movie['title'] }}</h1>
                                <div class="text">
                                    <span class="d-none d-sm-inline">{{ $movie['overview'] }}</span>
                                    <span
                                        class="d-inline d-sm-none">{{ \Illuminate\Support\Str::limit($movie['overview'], 100) }}</span>
                                </div>
                                <div class="btns-box">
                                    <a href="{{ route('movies') }}" class="theme-btn btn-style-two">
                                        <span class="txt">{{ __('messages.explore_more') }} <i
                                                class="lnr lnr-arrow-right"></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
