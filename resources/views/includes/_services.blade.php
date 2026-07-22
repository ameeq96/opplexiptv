@php
    $isDocumentEnglishServices = request()->routeIs('home') && app()->getLocale() === 'en';
    $documentServices = $isDocumentEnglishServices ? __('messages.home_document.services') : [];
    $displayServiceCards = $serviceCards ?? [];
    $packagesUrl = route('packages', ['direct' => 1]);

    if ($isDocumentEnglishServices) {
        $serviceRoutes = [$packagesUrl, route('reseller-panel'), $packagesUrl];
        $serviceIcons = ['service-4.webp', 'service-5.webp', 'service-4.webp'];
        $displayServiceCards = [];

        foreach ($documentServices['cards'] as $index => $card) {
            $displayServiceCards[] = $card + [
                'link' => $serviceRoutes[$index],
                'icon' => $serviceIcons[$index],
            ];
        }
    }
@endphp

<section class="services-section-two" style="background-image:url({{ asset('images/background/3.webp') }})"
         aria-label="Explore IPTV Services like Packages, Sports, VOD and Multi-Device Access">
    <div class="auto-container">
        <div class="sec-title light centered services-showcase__heading">
            <div class="services-showcase__eyebrow">{{ $isDocumentEnglishServices ? $documentServices['eyebrow'] : __('messages.home_services_eyebrow') }}</div>
            <h3 class="text-white" aria-label="Explore Opplex IPTV Services">{{ $isDocumentEnglishServices ? $documentServices['heading'] : __('messages.explore_services') }}</h3>
            <p>{{ $isDocumentEnglishServices ? $documentServices['intro'] : __('messages.home_services_intro') }}</p>
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
                        @forelse ($displayServiceCards as $card)
                            @php
                                $icon = $card['icon'] ? asset('images/icons/' . $card['icon']) : asset('images/icons/service-4.webp');
                                $link = $card['link'] ?: $packagesUrl;
                            @endphp
                            <div class="native-carousel__slide">
                                <div class="service-block-two" aria-label="{{ $card['title'] }}">
                                    <div class="inner-box">
                                        <div class="color-layer" aria-hidden="true"></div>
                                        <div class="icon-layer-one" style="background-image:url('{{ asset('images/background/pattern-19.webp') }}')" aria-hidden="true"></div>
                                        <div class="icon-layer-two" style="background-image:url('{{ asset('images/background/pattern-20.webp') }}')" aria-hidden="true"></div>
                                        <div class="icon">
                                            <img class="mx-width" src="{{ $icon }}" alt="{{ $card['title'] }}" loading="lazy" decoding="async" />
                                        </div>
                                        <h4><a href="{{ $link }}">{{ $card['title'] }}</a></h4>
                                        <div class="text">{{ $card['description'] }}</div>
                                        <a class="learn-more" href="{{ $link }}">{{ $card['cta'] ?? __('messages.learn_more') }}</a>
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
                                            <img class="mx-width" src="{{ asset('images/icons/service-4.webp') }}" alt="{{ __('messages.iptv_sports') }}" loading="lazy" decoding="async" />
                                        </div>
                                        <h4><a href="{{ $packagesUrl }}">{{ __('messages.iptv_sports') }}</a></h4>
                                        <div class="text">{{ __('messages.iptv_sports_desc') }}</div>
                                        <a class="learn-more" href="{{ $packagesUrl }}">{{ __('messages.learn_more') }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            <div class="four-item-carousel owl-carousel owl-theme" role="region" aria-label="IPTV Services Carousel">
                @forelse ($displayServiceCards as $card)
                    @php
                        $icon = $card['icon'] ? asset('images/icons/' . $card['icon']) : asset('images/icons/service-4.webp');
                        $link = $card['link'] ?: $packagesUrl;
                    @endphp
                    <div class="service-block-two" aria-label="{{ $card['title'] }}">
                        <div class="inner-box">
                            <div class="color-layer" aria-hidden="true"></div>
                            <div class="icon-layer-one" style="background-image:url('{{ asset('images/background/pattern-19.webp') }}')" aria-hidden="true"></div>
                            <div class="icon-layer-two" style="background-image:url('{{ asset('images/background/pattern-20.webp') }}')" aria-hidden="true"></div>
                            <div class="icon">
                                <img class="mx-width" src="{{ $icon }}" alt="{{ $card['title'] }}" loading="lazy" decoding="async" />
                            </div>
                            <h4><a href="{{ $link }}">{{ $card['title'] }}</a></h4>
                            <div class="text">{{ $card['description'] }}</div>
                            <a class="learn-more" href="{{ $link }}">{{ $card['cta'] ?? __('messages.learn_more') }}</a>
                        </div>
                    </div>
                @empty
                    <div class="service-block-two">
                        <div class="inner-box">
                            <div class="color-layer" aria-hidden="true"></div>
                            <div class="icon-layer-one" style="background-image:url('{{ asset('images/background/pattern-19.webp') }}')" aria-hidden="true"></div>
                            <div class="icon-layer-two" style="background-image:url('{{ asset('images/background/pattern-20.webp') }}')" aria-hidden="true"></div>
                            <div class="icon">
                                <img class="mx-width" src="{{ asset('images/icons/service-4.webp') }}" alt="{{ __('messages.iptv_sports') }}" loading="lazy" decoding="async" />
                            </div>
                            <h4><a href="{{ $packagesUrl }}">{{ __('messages.iptv_sports') }}</a></h4>
                            <div class="text">{{ __('messages.iptv_sports_desc') }}</div>
                            <a class="learn-more" href="{{ $packagesUrl }}">{{ __('messages.learn_more') }}</a>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</section>
