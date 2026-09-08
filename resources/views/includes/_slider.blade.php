@php
    $isDocumentEnglish = true;
    $documentHero = __('document_home.hero');
@endphp

@if ($isMobile)
    <section class="hero-section-mobile"
        aria-label="{{ __('document_ui.home.hero_aria') }}">
        <div class="container text-center">
            @if ($isDocumentEnglish)
                <h1 class="heading">{{ $documentHero['heading'] }}</h1>
                <p class="description">{{ $documentHero['text'] }}</p>
                <div class="btn-group d-flex justify-content-center gap-2 flex-wrap">
                    <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                        class="btn btn-primary" target="_blank" rel="noopener" data-trial data-whatsapp-click
                        data-whatsapp-placement="hero" data-whatsapp-intent="trial"
                        data-wa-href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}">
                        {{ $documentHero['primary_cta'] }} <span>&#8599;</span>
                    </a>
                    <a href="#pricing-section" class="btn btn-outline">
                        {{ $documentHero['secondary_cta'] }} <span>&#10148;</span>
                    </a>
                </div>
            @else
                <p class="subtitle">{{ __('messages.subtitle') }}</p>
                <h1 class="heading">{{ __('messages.heading-mobile') }}</h1>
                <p class="description">
                    {{ __('messages.description_prefix') }} <strong>Opplex IPTV</strong>
                    {{ __('messages.description_suffix') }}
                </p>
                <div class="btn-group d-flex justify-content-center gap-2 flex-wrap">
                    <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_pricing')) }}" target="_blank"
                        rel="noopener" class="btn btn-primary" data-whatsapp-click
                        data-whatsapp-placement="hero" data-whatsapp-intent="pricing">
                        {{ __('messages.see_pricing') }} <span>&#10148;</span>
                    </a>

                    <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                        class="btn btn-outline" target="_blank" rel="noopener" data-trial data-whatsapp-click
                        data-whatsapp-placement="hero" data-whatsapp-intent="trial">
                        {{ __('messages.start_trial') }} <span>&#8599;</span>
                    </a>
                </div>
            @endif
        </div>
    </section>
@else
    @php
        $locale = app()->getLocale();
        $sliderMovies = collect($movies ?? [])
            ->filter(static fn ($movie) => !empty($movie['webp_image_url']))
            ->values();

        if ($isDocumentEnglish) {
            $sliderMovies = $sliderMovies->take(1)->values();
        }
    @endphp

    @if (!empty($useNativeCarousel))
    <section class="main-slider-two native-home-hero"
        aria-label="{{ __('document_ui.home.slider_aria') }}">
            <div class="native-carousel native-carousel--hero"
                data-native-carousel
                data-carousel-type="hero"
                data-autoplay="6000"
                data-rtl="{{ $isRtl ? 'true' : 'false' }}">
                <div class="native-carousel__viewport">
                    <div class="native-carousel__track">
                        @foreach ($sliderMovies as $index => $movie)
                            @php
                                $imageUrl = $movie['webp_image_url'];
                            @endphp
                            <div class="native-carousel__slide slide {{ $index !== 0 ? 'lazy-background' : '' }} {{ $index === 0 ? 'is-active' : '' }}"
                                data-native-slide
                                data-bg="{{ $imageUrl }}"
                                aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                                @if ($index === 0) style="background-image: url('{{ $imageUrl }}');" @endif>

                                @if ($index === 0)
                                    <img src="{{ $imageUrl }}" alt="{{ __('document_ui.home.poster_aria', ['title' => $movie['safe_title']]) }}"
                                            aria-label="{{ __('document_ui.home.poster_aria', ['title' => $movie['safe_title']]) }}"
                                        width="960" height="540" loading="eager" decoding="async" fetchpriority="high">
                                @endif

                                <div class="auto-container custom-height">
                                    <div class="content-boxed">
                                        <div class="inner-box slider-font {{ textAlignment($isRtl) }}">
                                            @if ($index === 0)
                                                <h1>{{ $isDocumentEnglish ? $documentHero['heading'] : __('messages.home_hero_watch_live', ['title' => $movie['safe_title']]) }}</h1>
                                                <span class="d-none">
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
                                                </span>
                                            @else
                                                <h3 class="text-white">{{ $isDocumentEnglish ? $documentHero['heading'] : $movie['safe_title'] }}</h3>
                                            @endif

                                            <div class="text">{{ $isDocumentEnglish ? $documentHero['text'] : $movie['safe_overview'] }}</div>

                                            <div class="btns-box {{ $isRtl ? 'text-right' : 'text-left' }} {{ $isDocumentEnglish ? 'home-document-hero__actions' : '' }}">
                                                @if ($isDocumentEnglish)
                                                    <a target="_blank" rel="noopener" data-trial
                                                        data-whatsapp-click data-whatsapp-placement="hero" data-whatsapp-intent="trial"
                                                        data-wa-href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                                                        href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                                                        class="theme-btn btn-style-two {{ $isRtl ? 'rtl-btn' : 'ltr-btn' }}">
                                                        <span class="txt">{{ $documentHero['primary_cta'] }} <i class="lnr {{ arrowDirection($isRtl) }}"></i></span>
                                                    </a>
                                                    <a href="#pricing-section"
                                                        class="theme-btn btn-style-two home-document-hero__secondary {{ $isRtl ? 'rtl-btn' : 'ltr-btn' }}">
                                                        <span class="txt">{{ $documentHero['secondary_cta'] }} <i class="lnr {{ arrowDirection($isRtl) }}"></i></span>
                                                    </a>
                                                @else
                                                    <a target="__blank" href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_explore')) }}"
                                                        data-whatsapp-click data-whatsapp-placement="hero" data-whatsapp-intent="explore"
                                                        class="theme-btn btn-style-two {{ $isRtl ? 'rtl-btn' : 'ltr-btn' }}">
                                                        <span class="txt">
                                                            {{ __('messages.explore_more') }}
                                                            <i class="lnr {{ arrowDirection($isRtl) }}"></i>
                                                        </span>
                                                    </a>
                                                @endif
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
        <section class="main-slider-two" aria-label="{{ __('document_ui.home.slider_aria') }}">
            <div class="main-slider-carousel owl-carousel owl-theme" data-rtl="{{ $isRtl ? 'true' : 'false' }}">
                @foreach ($sliderMovies as $index => $movie)
                    @php
                        $imageUrl = $movie['webp_image_url'];
                    @endphp
                    <div class="slide {{ $index !== 0 ? 'lazy-background' : '' }}"
                        data-bg="{{ $imageUrl }}"
                        @if ($index === 0) style="background-image: url('{{ $imageUrl }}');" @endif>

                        @if ($index === 0)
                            <img src="{{ $imageUrl }}" alt="{{ __('document_ui.home.poster_aria', ['title' => $movie['safe_title']]) }}"
                                    aria-label="{{ __('document_ui.home.poster_aria', ['title' => $movie['safe_title']]) }}"
                                width="960" height="540" loading="eager" decoding="async" fetchpriority="high">
                        @endif

                        <div class="auto-container custom-height">
                            <div class="content-boxed">
                                <div class="inner-box slider-font {{ textAlignment($isRtl) }}">
                                    @if ($index === 0)
                                        <h1>{{ $isDocumentEnglish ? $documentHero['heading'] : __('messages.home_hero_watch_live', ['title' => $movie['safe_title']]) }}</h1>
                                        <span class="d-none">
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
                                        </span>
                                    @else
                                        <h3 class="text-white">{{ $isDocumentEnglish ? $documentHero['heading'] : $movie['safe_title'] }}</h3>
                                    @endif

                                    <div class="text">{{ $isDocumentEnglish ? $documentHero['text'] : $movie['safe_overview'] }}</div>

                                    <div class="btns-box {{ $isRtl ? 'text-right' : 'text-left' }} {{ $isDocumentEnglish ? 'home-document-hero__actions' : '' }}">
                                        @if ($isDocumentEnglish)
                                            <a target="_blank" rel="noopener" data-trial
                                                data-whatsapp-click data-whatsapp-placement="hero" data-whatsapp-intent="trial"
                                                data-wa-href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                                                href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                                                class="theme-btn btn-style-two {{ $isRtl ? 'rtl-btn' : 'ltr-btn' }}">
                                                <span class="txt">{{ $documentHero['primary_cta'] }} <i class="lnr {{ arrowDirection($isRtl) }}"></i></span>
                                            </a>
                                            <a href="#pricing-section"
                                                class="theme-btn btn-style-two home-document-hero__secondary {{ $isRtl ? 'rtl-btn' : 'ltr-btn' }}">
                                                <span class="txt">{{ $documentHero['secondary_cta'] }} <i class="lnr {{ arrowDirection($isRtl) }}"></i></span>
                                            </a>
                                        @else
                                            <a target="__blank" href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_explore')) }}"
                                                data-whatsapp-click data-whatsapp-placement="hero" data-whatsapp-intent="explore"
                                                class="theme-btn btn-style-two {{ $isRtl ? 'rtl-btn' : 'ltr-btn' }}">
                                                <span class="txt">
                                                    {{ __('messages.explore_more') }}
                                                    <i class="lnr {{ arrowDirection($isRtl) }}"></i>
                                                </span>
                                            </a>
                                        @endif
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
