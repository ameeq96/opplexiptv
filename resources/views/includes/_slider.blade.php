@if ($agent->isMobile())
  <section class="hero-section-mobile text-center" style="padding: 2rem 1rem; background-color: #fff;">
  <div class="auto-container">
    <p class="subtitle" style="font-weight: 600; font-size: 1.2rem; color: #555;">Stream Smarter in Europe!</p>

    <h2 class="heading mb-2" style="font-size: 1.6rem; font-weight: bold; color: #111;">
      Experience Premium IPTV Services in Italy & Beyond
    </h2>

    <p class="description" style="margin-top: 1rem; color: #333; font-size: 1rem; line-height: 1.6;">
      Welcome to <strong>Opplex IPTV</strong> — your ultimate destination for premium IPTV streaming across Italy and all of Europe.
      Enjoy HD quality, 24/7 access, and hundreds of international channels without interruptions.
    </p>

    <div class="btn-group mt-4" style="margin-top: 2rem; display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
      <a href="{{ route('contact') }}" class="btn btn-primary" style="padding: 0.75rem 1.5rem; background-color: #007bff; color: white; border-radius: 5px; font-weight: 600; text-decoration: none;">
        START FREE TRIAL <span style="margin-left: 0.5rem;">↗</span>
      </a>
      <a href="#pricing-section" class="btn btn-outline" style="padding: 0.75rem 1.5rem; border: 2px solid #007bff; color: #007bff; border-radius: 5px; font-weight: 600; text-decoration: none;">
        SEE PRICING PLANS <span style="margin-left: 0.5rem;">➤</span>
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
