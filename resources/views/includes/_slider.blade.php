@if ($agent->isMobile())
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-left">
                    <p class="subtitle m-0">Stream Smarter in Europe!</p>
                    <h2>Experience Premium IPTV Services in Italy & Beyond</h2>
                    <p class="hero-description m-0">
                        Welcome to Opplex IPTV — your ultimate destination for premium IPTV streaming across Italy and
                        all of Europe.
                        Enjoy HD quality, 24/7 access, and hundreds of international channels without interruptions.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-start">
                        <a href="{{ route('contact') }}" class="btn btn-dark mr-3 mb-2">
                            START FREE TRIAL <span class="ml-1">↗</span>
                        </a>
                        <a href="#pricing-section" class="btn btn-outline-custom mb-2">
                            SEE PRICING PLANS <span class="ml-1">➤</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>
@else
    <section class="main-slider-two">
        <div class="main-slider-carousel owl-carousel owl-theme">
            @foreach ($displayMovies as $index => $movie)
                <div class="slide {{ $index !== 0 ? 'lazy-background' : '' }}"
                    @if ($index !== 0) data-bg="{{ $movie['webp_image_url'] }}" loading="lazy" @endif>
                    @if ($index === 0)
                        <img src="{{ $movie['webp_image_url'] }}" alt="{{ $movie['title'] ?? $movie['name'] }}"
                            class="d-block w-100" width="1280" height="720" fetchpriority="high" decoding="auto"
                            loading="eager" />
                    @endif

                    <div class="auto-container custom-height">
                        <div class="content-boxed">
                            <div class="inner-box slider-font">
                                <h1>{{ $movie['title'] ?? $movie['name'] }}</h1>
                                <div class="text">
                                    <span class="d-none d-sm-inline">{{ $movie['overview'] }}</span>
                                    <span
                                        class="d-inline d-sm-none">{{ \Illuminate\Support\Str::limit($movie['overview'], 100) }}</span>
                                </div>
                                <div class="btns-box">
                                    <a href="{{ route('movies') }}" class="theme-btn btn-style-two">
                                        <span class="txt">Explore More <i class="lnr lnr-arrow-right"></i></span>
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
