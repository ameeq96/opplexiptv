@if ($agent->isMobile())
    {{-- ✅ Mobile Version --}}
    <section class="hero-section-mobile" aria-label="Opplex IPTV Hero Section – Start Your IPTV Trial in HD/4K">
        <div class="container text-center">
            <p class="subtitle">{{ __('messages.subtitle') }}</p>

            <h2 class="heading">
                {{ __('messages.heading-mobile') }}
            </h2>

            <p class="description">
                {{ __('messages.description_prefix') }} <strong>Opplex IPTV</strong>
                {{ __('messages.description_suffix') }}
            </p>

            <div class="btn-group d-flex justify-content-center gap-2 flex-wrap">
                <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_pricing')) }}" target="_blank"
                    class="btn btn-primary">
                    {{ __('messages.see_pricing') }} <span>➤</span>
                </a>

                <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}" target="_blank"
                    class="btn btn-outline">
                    {{ __('messages.start_trial') }} <span>↗</span>
                </a>
            </div>
        </div>
    </section>
@else
    {{-- ✅ Desktop Slider --}}
    <section class="main-slider-two" aria-label="Opplex IPTV HD/4K Movie Slider – Discover Our Content">
        <div class="main-slider-carousel owl-carousel owl-theme" data-rtl="{{ $isRtl ? 'true' : 'false' }}">
            @foreach ($movies as $index => $movie)
                <div class="slide {{ $index !== 0 ? 'lazy-background' : '' }}"
                    @if ($index !== 0) data-bg="{{ $movie['webp_image_url'] }}" loading="lazy" @endif>

                    @if ($index === 0)
                        <img src="{{ $movie['webp_image_url'] }}"
                            alt="{{ $movie['title'] ?? $movie['name'] }} - IPTV Movie Poster"
                            aria-label="IPTV Movie Poster - {{ $movie['title'] ?? $movie['name'] }}">
                    @endif

                    <div class="auto-container custom-height">
                        <div class="content-boxed">
                            <div class="inner-box slider-font {{ textAlignment($isRtl) }}">
                                @if ($index === 0)
                                    <h1>
                                        @switch(app()->getLocale())
                                            @case('ar')
                                                شاهد {{ $movie['title'] ?? 'محتوى IPTV المميز لدينا' }} مباشرة بجودة HD
                                            @break

                                            @case('es')
                                                Mira {{ $movie['title'] ?? 'Nuestro contenido destacado de IPTV' }} en vivo en
                                                HD
                                            @break

                                            @case('fr')
                                                Regardez {{ $movie['title'] ?? 'Notre contenu IPTV en vedette' }} en direct en
                                                HD
                                            @break

                                            @case('hi')
                                                देखें {{ $movie['title'] ?? 'हमारा विशेष आईपीटीवी कंटेंट' }} लाइव एचडी में
                                            @break

                                            @case('it')
                                                Guarda {{ $movie['title'] ?? 'Il nostro contenuto IPTV in evidenza' }} in
                                                diretta in HD
                                            @break

                                            @case('nl')
                                                Kijk {{ $movie['title'] ?? 'Onze uitgelichte IPTV-inhoud' }} live in HD
                                            @break

                                            @case('pt')
                                                Assista {{ $movie['title'] ?? 'Nosso conteúdo de IPTV em destaque' }} ao vivo
                                                em HD
                                            @break

                                            @case('ru')
                                                Смотрите {{ $movie['title'] ?? 'Наш лучший IPTV-контент' }} в прямом эфире в HD
                                            @break

                                            @case('ur')
                                                دیکھیں {{ $movie['title'] ?? 'ہمارا نمایاں IPTV مواد' }} براہ راست ایچ ڈی میں
                                            @break

                                            @default
                                                Watch {{ $movie['title'] ?? 'Our Featured IPTV Content' }} Live in HD
                                        @endswitch
                                    </h1>
                                @else
                                    <h2 class="text-white">{{ $movie['title'] ?? 'Featured IPTV Content' }}</h2>
                                @endif

                                <div class="text">
                                    {{ isset($movie['overview']) ? Str::limit($movie['overview'], 150) : 'No overview available' }}
                                </div>

                                <div class="btns-box {{ $isRtl ? 'text-right' : 'text-left' }}">
                                    <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_explore')) }}"
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
