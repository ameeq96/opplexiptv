@if ($agent->isMobile())
    <section class="hero-section-mobile text-center">
        <div class="auto-container">
            <div class="hero-row">
                <div class="hero-content">
                    <p class="subtitle">Stream Smarter in Europe!</p>
                    <h2 class="heading mb-2">Experience Premium IPTV Services in Italy & Beyond</h2>
                    <span class="description">
                        Welcome to Opplex IPTV — your ultimate destination for premium IPTV streaming across Italy and
                        all of Europe. Enjoy HD quality, 24/7 access, and hundreds of international channels without
                        interruptions.
                    </span>
                    <div class="btn-group mt-4">
                        <a href="{{ route('contact') }}" class="btn btn-primary">
                            START FREE TRIAL <span>↗</span>
                        </a>
                        <a href="#pricing-section" class="btn btn-outline">
                            SEE PRICING PLANS <span>➤</span>
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
