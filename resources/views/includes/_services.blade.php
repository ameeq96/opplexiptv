<section class="services-section-two" style="background-image:url({{ asset('images/background/3.webp') }})"
         aria-label="Explore IPTV Services like Packages, Sports, VOD and Multi-Device Access">
    <div class="auto-container">
        <div class="sec-title light centered">
            <div class="separator"></div>
            <h3 class="text-white" aria-label="Explore Opplex IPTV Services">{{ __('messages.explore_services') }}</h3>
        </div>

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
                {{-- fallback to translations if no DB data --}}
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
    </div>
</section>
