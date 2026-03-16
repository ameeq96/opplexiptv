<section class="services-section-two {{ !empty($useSectionSkeletons) ? 'skeleton-section skeleton-section--services' : '' }}"
         @if (!empty($useSectionSkeletons)) data-skeleton-section @endif
         style="background-image:url({{ asset('images/background/3.webp') }})"
         aria-label="Explore IPTV Services like Packages, Sports, VOD and Multi-Device Access">
    @if (!empty($useSectionSkeletons))
        <div class="section-skeleton__overlay" aria-hidden="true">
            <div class="section-skeleton__content">
                <span class="section-skeleton__pill"></span>
                <span class="section-skeleton__line section-skeleton__line--lg"></span>
                <span class="section-skeleton__line section-skeleton__line--md"></span>
                <div class="section-skeleton__cards">
                    <span class="section-skeleton__card"></span>
                    <span class="section-skeleton__card"></span>
                    <span class="section-skeleton__card"></span>
                </div>
            </div>
        </div>
    @endif
    <div class="auto-container">
        <div class="sec-title light centered services-showcase__heading">
            <div class="services-showcase__eyebrow">{{ __('messages.home_services_eyebrow') }}</div>
            <h3 class="text-white" aria-label="Explore Opplex IPTV Services">{{ __('messages.explore_services') }}</h3>
            <p>{{ __('messages.home_services_intro') }}</p>
        </div>

        @if (!empty($useNativeCarousel))
            <div class="native-carousel native-carousel--cards native-carousel--services"
                data-native-carousel
                data-items-desktop="3"
                data-items-tablet="2"
                data-items-mobile="1"
                data-gap="30"
                data-autoplay="4000"
                role="region"
                aria-label="IPTV Services Carousel">
                <div class="native-carousel__viewport">
                    <div class="native-carousel__track">
                        @forelse ($serviceCards ?? [] as $card)
                            @php
                                $icon = $card['icon'] ? asset('images/icons/' . $card['icon']) : asset('images/icons/service-4.webp');
                                $link = $card['link'] ?: route('packages');
                            @endphp
                            <div class="native-carousel__slide">
                                <div class="service-block-two" aria-label="{{ $card['title'] }}">
                                    <div class="inner-box">
                                        <div class="color-layer" aria-hidden="true"></div>
                                        <div class="icon-layer-one" style="background-image:url('{{ asset('images/background/pattern-19.webp') }}')" aria-hidden="true"></div>
                                        <div class="icon-layer-two" style="background-image:url('{{ asset('images/background/pattern-20.webp') }}')" aria-hidden="true"></div>
                                        <div class="icon">
                                            <img class="mx-width" src="{{ $icon }}" alt="{{ $card['title'] }}" loading="lazy" />
                                        </div>
                                        <h4><a href="{{ $link }}">{{ $card['title'] }}</a></h4>
                                        <div class="text">{{ $card['description'] }}</div>
                                        <a class="learn-more" href="{{ $link }}">{{ __('messages.learn_more') }}</a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="native-carousel__slide">
                                <div class="service-block-two">
                                    <div class="inner-box">
                                        <div class="color-layer" aria-hidden="true"></div>
                                        <div class="icon-layer-one" style="background-image:url('{{ asset('images/background/pattern-19.webp') }}')" aria-hidden="true"></div>
                                        <div class="icon-layer-two" style="background-image:url('{{ asset('images/background/pattern-20.webp') }}')" aria-hidden="true"></div>
                                        <div class="icon">
                                            <img class="mx-width" src="{{ asset('images/icons/service-4.webp') }}" alt="{{ __('messages.iptv_sports') }}" loading="lazy" />
                                        </div>
                                        <h4><a href="{{ route('packages') }}">{{ __('messages.iptv_sports') }}</a></h4>
                                        <div class="text">{{ __('messages.iptv_sports_desc') }}</div>
                                        <a class="learn-more" href="{{ route('packages') }}">{{ __('messages.learn_more') }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            <div class="four-item-carousel owl-carousel owl-theme" role="region" aria-label="IPTV Services Carousel">
                @forelse ($serviceCards ?? [] as $card)
                    @php
                        $icon = $card['icon'] ? asset('images/icons/' . $card['icon']) : asset('images/icons/service-4.webp');
                        $link = $card['link'] ?: route('packages');
                    @endphp
                    <div class="service-block-two" aria-label="{{ $card['title'] }}">
                        <div class="inner-box">
                            <div class="color-layer" aria-hidden="true"></div>
                            <div class="icon-layer-one" style="background-image:url('{{ asset('images/background/pattern-19.webp') }}')" aria-hidden="true"></div>
                            <div class="icon-layer-two" style="background-image:url('{{ asset('images/background/pattern-20.webp') }}')" aria-hidden="true"></div>
                            <div class="icon">
                                <img class="mx-width" src="{{ $icon }}" alt="{{ $card['title'] }}" loading="lazy" />
                            </div>
                            <h4><a href="{{ $link }}">{{ $card['title'] }}</a></h4>
                            <div class="text">{{ $card['description'] }}</div>
                            <a class="learn-more" href="{{ $link }}">{{ __('messages.learn_more') }}</a>
                        </div>
                    </div>
                @empty
                    <div class="service-block-two">
                        <div class="inner-box">
                            <div class="color-layer" aria-hidden="true"></div>
                            <div class="icon-layer-one" style="background-image:url('{{ asset('images/background/pattern-19.webp') }}')" aria-hidden="true"></div>
                            <div class="icon-layer-two" style="background-image:url('{{ asset('images/background/pattern-20.webp') }}')" aria-hidden="true"></div>
                            <div class="icon">
                                <img class="mx-width" src="{{ asset('images/icons/service-4.webp') }}" alt="{{ __('messages.iptv_sports') }}" loading="lazy" />
                            </div>
                            <h4><a href="{{ route('packages') }}">{{ __('messages.iptv_sports') }}</a></h4>
                            <div class="text">{{ __('messages.iptv_sports_desc') }}</div>
                            <a class="learn-more" href="{{ route('packages') }}">{{ __('messages.learn_more') }}</a>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</section>
