@if ($agent->isMobile())
    <section class="hero-section-mobile" style="font-family: system-ui, sans-serif;">
        <div class="container">
            <p style="font-weight: 500;">Stream Smarter in Europe!</p>

            <h2 style="font-size: 1.5em; font-weight: bold; margin: 0.5em 0;">
                Experience Premium IPTV Services in Italy & Beyond
            </h2>

            <p>
                Welcome to <strong>Opplex IPTV</strong> — your ultimate destination for premium IPTV streaming across
                Italy and all of Europe. Enjoy HD quality, 24/7 access, and hundreds of international channels without
                interruptions.
            </p>

            <div style="margin-top: 1em;">
                <a href="{{ route('contact') }}"
                    style="display: inline-block; background: #005eff; color: #fff; padding: 0.5em 1em; text-decoration: none; border-radius: 4px; font-weight: bold;">
                    START FREE TRIAL ↗
                </a>
                <a href="#pricing-section"
                    style="display: inline-block; margin-left: 0.5em; border: 2px solid #005eff; color: #005eff; padding: 0.5em 1em; text-decoration: none; border-radius: 4px; font-weight: bold;">
                    SEE PRICING PLANS ➤
                </a>
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
