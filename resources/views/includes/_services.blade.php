<section class="services-section-two" style="background-image: url(images/background/3.webp)"
         aria-label="Explore IPTV Services like Packages, Sports, VOD and Multi-Device Access">
    <div class="auto-container">
        <div class="sec-title light centered">
            <div class="separator"></div>
            <h2 aria-label="Explore Opplex IPTV Services">{{ __('messages.explore_services') }}</h2>
        </div>

        <div class="four-item-carousel owl-carousel owl-theme" role="region" aria-label="IPTV Services Carousel">
            {{-- IPTV Packages --}}
            <div class="service-block-two" aria-label="IPTV Packages - Flexible & Affordable Plans">
                <div class="inner-box">
                    <div class="color-layer" aria-hidden="true"></div>
                    <div class="icon-layer-one" style="background-image: url('{{ asset('images/background/pattern-19.webp') }}')" aria-hidden="true"></div>
                    <div class="icon-layer-two" style="background-image: url('{{ asset('images/background/pattern-20.webp') }}')" aria-hidden="true"></div>

                    <div class="icon">
                        <img class="mx-width" src="{{ asset('images/icons/service-4.webp') }}"
                             alt="IPTV Packages Icon - Best IPTV Plans for Europe" loading="lazy" />
                    </div>
                    <h4><a href="{{ route('packages') }}" aria-label="View IPTV Packages">{{ __('messages.iptv_packages') }}</a></h4>
                    <div class="text">{{ __('messages.iptv_packages_desc') }}</div>
                    <a class="learn-more" href="{{ route('packages') }}" aria-label="Learn more about IPTV packages">{{ __('messages.learn_more') }}</a>
                </div>
            </div>

            {{-- Reseller Panel --}}
            <div class="service-block-two" aria-label="IPTV Reseller Panel - Start Selling Subscriptions">
                <div class="inner-box">
                    <div class="color-layer" aria-hidden="true"></div>
                    <div class="icon-layer-one" style="background-image: url('{{ asset('images/background/pattern-19.webp') }}')" aria-hidden="true"></div>
                    <div class="icon-layer-two" style="background-image: url('{{ asset('images/background/pattern-20.webp') }}')" aria-hidden="true"></div>

                    <div class="icon">
                        <img class="mx-width" src="{{ asset('images/icons/service-5.webp') }}"
                             alt="Reseller Panel Icon - IPTV Reseller Platform" loading="lazy" />
                    </div>
                    <h4><a href="{{ route('packages') }}" aria-label="View IPTV Reseller Panel">{{ __('messages.reseller_panel') }}</a></h4>
                    <div class="text">{{ __('messages.reseller_panel_desc') }}</div>
                    <a class="learn-more" href="{{ route('packages') }}" aria-label="Learn more about reseller program">{{ __('messages.learn_more') }}</a>
                </div>
            </div>

            {{-- Sports & Live TV --}}
            <div class="service-block-two" aria-label="IPTV Sports and Live TV - Watch in HD/4K">
                <div class="inner-box">
                    <div class="color-layer" aria-hidden="true"></div>
                    <div class="icon-layer-one" style="background-image: url('{{ asset('images/background/pattern-19.webp') }}')" aria-hidden="true"></div>
                    <div class="icon-layer-two" style="background-image: url('{{ asset('images/background/pattern-20.webp') }}')" aria-hidden="true"></div>

                    <div class="icon">
                        <img class="mx-width" src="{{ asset('images/icons/service-4.webp') }}"
                             alt="IPTV Sports Icon - Live Sports & Channels" loading="lazy" />
                    </div>
                    <h4><a href="{{ route('packages') }}" aria-label="Watch IPTV Sports">{{ __('messages.iptv_sports') }}</a></h4>
                    <div class="text">{{ __('messages.iptv_sports_desc') }}</div>
                    <a class="learn-more" href="{{ route('packages') }}" aria-label="Learn more about live sports">{{ __('messages.learn_more') }}</a>
                </div>
            </div>

            {{-- VOD Library --}}
            <div class="service-block-two" aria-label="VOD Library - 50,000+ Movies and Series">
                <div class="inner-box">
                    <div class="color-layer" aria-hidden="true"></div>
                    <div class="icon-layer-one" style="background-image: url('{{ asset('images/background/pattern-19.webp') }}')" aria-hidden="true"></div>
                    <div class="icon-layer-two" style="background-image: url('{{ asset('images/background/pattern-20.webp') }}')" aria-hidden="true"></div>

                    <div class="icon">
                        <img class="mx-width" src="{{ asset('images/icons/service-4.webp') }}"
                             alt="VOD Icon - Access Movies and TV Shows" loading="lazy" />
                    </div>
                    <h4><a href="{{ route('movies') }}" aria-label="Explore IPTV VOD Library">{{ __('messages.iptv_vod') }}</a></h4>
                    <div class="text">{{ __('messages.iptv_vod_desc') }}</div>
                    <a class="learn-more" href="{{ route('movies') }}" aria-label="Learn more about VOD">{{ __('messages.learn_more') }}</a>
                </div>
            </div>

            {{-- Multi-Device IPTV --}}
            <div class="service-block-two" aria-label="Multi-Device IPTV - Compatible with Smart TV, Android, iOS">
                <div class="inner-box">
                    <div class="color-layer" aria-hidden="true"></div>
                    <div class="icon-layer-one" style="background-image: url('{{ asset('images/background/pattern-19.webp') }}')" aria-hidden="true"></div>
                    <div class="icon-layer-two" style="background-image: url('{{ asset('images/background/pattern-20.webp') }}')" aria-hidden="true"></div>

                    <div class="icon">
                        <img class="mx-width" src="{{ asset('images/icons/service-4.webp') }}"
                             alt="Multi-Device IPTV Icon - Stream on all devices" loading="lazy" />
                    </div>
                    <h4><a href="{{ route('packages') }}" aria-label="Multi-Device IPTV Access">{{ __('messages.iptv_devices') }}</a></h4>
                    <div class="text">{{ __('messages.iptv_devices_desc') }}</div>
                    <a class="learn-more" href="{{ route('packages') }}" aria-label="Learn more about multi-device support">{{ __('messages.learn_more') }}</a>
                </div>
            </div>

        </div>
    </div>
</section>
