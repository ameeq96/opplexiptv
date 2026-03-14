@if ($isMobile)
    <section class="hero-section-mobile {{ !empty($useSectionSkeletons) ? 'skeleton-section skeleton-section--hero' : '' }}"
        @if (!empty($useSectionSkeletons)) data-skeleton-section @endif
        aria-label="Opplex IPTV Hero Section - Start Your IPTV Trial in HD/4K">
        @if (!empty($useSectionSkeletons))
            <div class="section-skeleton__overlay" aria-hidden="true">
                <div class="section-skeleton__content">
                    <span class="section-skeleton__pill"></span>
                    <span class="section-skeleton__line section-skeleton__line--lg"></span>
                    <span class="section-skeleton__line section-skeleton__line--md"></span>
                    <span class="section-skeleton__button"></span>
                </div>
            </div>
        @endif
        <div class="container text-center">
            <p class="subtitle">{{ __('messages.subtitle') }}</p>
            <h3 class="heading">{{ __('messages.heading-mobile') }}</h3>
            <p class="description">
                {{ __('messages.description_prefix') }} <strong>Opplex IPTV</strong>
                {{ __('messages.description_suffix') }}
            </p>
            <div class="btn-group d-flex justify-content-center gap-2 flex-wrap">
                <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_pricing')) }}" target="_blank"
                    rel="noopener" class="btn btn-primary">
                    {{ __('messages.see_pricing') }} <span>&#10148;</span>
                </a>

                <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                    class="btn btn-outline" target="_blank" rel="noopener" data-trial>
                    {{ __('messages.start_trial') }} <span>&#8599;</span>
                </a>
            </div>
        </div>
    </section>
@else
    @php
        $locale = app()->getLocale();
    @endphp

    @if (!empty($useNativeCarousel))
        <section class="main-slider-two native-home-hero {{ !empty($useSectionSkeletons) ? 'skeleton-section skeleton-section--hero' : '' }}"
            @if (!empty($useSectionSkeletons)) data-skeleton-section @endif
            aria-label="Opplex IPTV HD/4K Movie Slider - Discover Our Content">
            @if (!empty($useSectionSkeletons))
                <div class="section-skeleton__overlay" aria-hidden="true">
                    <div class="section-skeleton__content">
                        <span class="section-skeleton__pill"></span>
                        <span class="section-skeleton__line section-skeleton__line--lg"></span>
                        <span class="section-skeleton__line section-skeleton__line--md"></span>
                        <span class="section-skeleton__button"></span>
                    </div>
                </div>
            @endif
            <div class="native-carousel native-carousel--hero"
                data-native-carousel
                data-carousel-type="hero"
                data-autoplay="6000"
                data-rtl="{{ $isRtl ? 'true' : 'false' }}">
                <div class="native-carousel__viewport">
                    <div class="native-carousel__track">
                        @foreach ($movies as $index => $movie)
                            <div class="native-carousel__slide slide {{ $index !== 0 ? 'lazy-background' : '' }} {{ $index === 0 ? 'is-active' : '' }}"
                                data-native-slide
                                aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                                @if ($index !== 0) data-bg="{{ $movie['webp_image_url'] }}" @endif>

                                @if ($index === 0)
                                    <img src="{{ $movie['webp_image_url'] }}" alt="{{ $movie['safe_title'] }} - IPTV Movie Poster"
                                        aria-label="IPTV Movie Poster - {{ $movie['safe_title'] }}">
                                @endif

                                <div class="auto-container custom-height">
                                    <div class="content-boxed">
                                        <div class="inner-box slider-font {{ textAlignment($isRtl) }}">
                                            @if ($index === 0)
                                                <h1>
                                                    @switch($locale)
                                                        @case('ar')
                                                            شاهد {{ $movie['safe_title'] }} مباشرة بجودة HD
                                                        @break
                                                        @case('es')
                                                            Mira {{ $movie['safe_title'] }} en vivo en HD
                                                        @break
                                                        @case('fr')
                                                            Regardez {{ $movie['safe_title'] }} en direct en HD
                                                        @break
                                                        @case('hi')
                                                            देखें {{ $movie['safe_title'] }} लाइव एचडी में
                                                        @break
                                                        @case('it')
                                                            Guarda {{ $movie['safe_title'] }} in diretta in HD
                                                        @break
                                                        @case('nl')
                                                            Kijk {{ $movie['safe_title'] }} live in HD
                                                        @break
                                                        @case('pt')
                                                            Assista {{ $movie['safe_title'] }} ao vivo em HD
                                                        @break
                                                        @case('ru')
                                                            Смотрите {{ $movie['safe_title'] }} в прямом эфире в HD
                                                        @break
                                                        @case('ur')
                                                            دیکھیں {{ $movie['safe_title'] }} براہ راست ایچ ڈی میں
                                                        @break
                                                        @default
                                                            Watch {{ $movie['safe_title'] }} Live in HD
                                                    @endswitch
                                                </h1>
                                            @else
                                                <h3 class="text-white">{{ $movie['safe_title'] }}</h3>
                                            @endif

                                            <div class="text">{{ $movie['safe_overview'] }}</div>

                                            <div class="btns-box {{ $isRtl ? 'text-right' : 'text-left' }}">
                                                <a target="__blank" href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_explore')) }}"
                                                    class="theme-btn btn-style-two {{ $isRtl ? 'rtl-btn' : 'ltr-btn' }}">
                                                    <span class="txt">
                                                        {{ __('messages.explore_more') }}
                                                        <i class="lnr {{ arrowDirection($isRtl) }}"></i>
                                                    </span>
                                                </a>
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
    @else
        <section class="main-slider-two {{ !empty($useSectionSkeletons) ? 'skeleton-section skeleton-section--hero' : '' }}"
            @if (!empty($useSectionSkeletons)) data-skeleton-section @endif
            aria-label="Opplex IPTV HD/4K Movie Slider - Discover Our Content">
            @if (!empty($useSectionSkeletons))
                <div class="section-skeleton__overlay" aria-hidden="true">
                    <div class="section-skeleton__content">
                        <span class="section-skeleton__pill"></span>
                        <span class="section-skeleton__line section-skeleton__line--lg"></span>
                        <span class="section-skeleton__line section-skeleton__line--md"></span>
                        <span class="section-skeleton__button"></span>
                    </div>
                </div>
            @endif
            <div class="main-slider-carousel owl-carousel owl-theme" data-rtl="{{ $isRtl ? 'true' : 'false' }}">
                @foreach ($movies as $index => $movie)
                    <div class="slide {{ $index !== 0 ? 'lazy-background' : '' }}"
                        @if ($index !== 0) data-bg="{{ $movie['webp_image_url'] }}" @endif>

                        @if ($index === 0)
                            <img src="{{ $movie['webp_image_url'] }}" alt="{{ $movie['safe_title'] }} - IPTV Movie Poster"
                                aria-label="IPTV Movie Poster - {{ $movie['safe_title'] }}">
                        @endif

                        <div class="auto-container custom-height">
                            <div class="content-boxed">
                                <div class="inner-box slider-font {{ textAlignment($isRtl) }}">
                                    @if ($index === 0)
                                        <h1>
                                            @switch($locale)
                                                @case('ar')
                                                    شاهد {{ $movie['safe_title'] }} مباشرة بجودة HD
                                                @break
                                                @case('es')
                                                    Mira {{ $movie['safe_title'] }} en vivo en HD
                                                @break
                                                @case('fr')
                                                    Regardez {{ $movie['safe_title'] }} en direct en HD
                                                @break
                                                @case('hi')
                                                    देखें {{ $movie['safe_title'] }} लाइव एचडी में
                                                @break
                                                @case('it')
                                                    Guarda {{ $movie['safe_title'] }} in diretta in HD
                                                @break
                                                @case('nl')
                                                    Kijk {{ $movie['safe_title'] }} live in HD
                                                @break
                                                @case('pt')
                                                    Assista {{ $movie['safe_title'] }} ao vivo em HD
                                                @break
                                                @case('ru')
                                                    Смотрите {{ $movie['safe_title'] }} в прямом эфире в HD
                                                @break
                                                @case('ur')
                                                    دیکھیں {{ $movie['safe_title'] }} براہ راست ایچ ڈی میں
                                                @break
                                                @default
                                                    Watch {{ $movie['safe_title'] }} Live in HD
                                            @endswitch
                                        </h1>
                                    @else
                                        <h3 class="text-white">{{ $movie['safe_title'] }}</h3>
                                    @endif

                                    <div class="text">{{ $movie['safe_overview'] }}</div>

                                    <div class="btns-box {{ $isRtl ? 'text-right' : 'text-left' }}">
                                        <a target="__blank" href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_explore')) }}"
                                            class="theme-btn btn-style-two {{ $isRtl ? 'rtl-btn' : 'ltr-btn' }}">
                                            <span class="txt">
                                                {{ __('messages.explore_more') }}
                                                <i class="lnr {{ arrowDirection($isRtl) }}"></i>
                                            </span>
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
@endif
