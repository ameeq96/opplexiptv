

<section class="network-section unlimited-showcase @unless ($isMobile) @else p-0 @endunless {{ !empty($useSectionSkeletons) ? 'skeleton-section' : '' }}"
    @if (!empty($useSectionSkeletons)) data-skeleton-section @endif
    aria-label="Opplex IPTV Features Section">
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

    <div class="auto-container unlimited-showcase__shell">
        <div class="inner-container unlimited-showcase__panel">
            <div class="unlimited-showcase__grid">
                @unless ($isMobile)
                    <div class="unlimited-showcase__media" aria-hidden="true">
                        <div class="unlimited-showcase__main">
                            <img src="{{ asset('images/resource/network-4.webp') }}" alt="IPTV streaming setup image" />
                        </div>
                        <div class="unlimited-showcase__screen">
                            <img src="{{ asset('images/resource/network-5.webp') }}" alt="IPTV content preview screen" />
                        </div>
                        <div class="unlimited-showcase__person-wrap">
                            <div class="unlimited-showcase__person">
                                <img src="{{ asset('images/resource/network-3.webp') }}" alt="High-quality IPTV connection graphic" />
                            </div>
                        </div>
                        <div class="unlimited-showcase__signal">
                            <img src="{{ asset('images/icons/service-4.webp') }}" alt="" aria-hidden="true" />
                        </div>
                    </div>
                @endunless

                <div class="unlimited-showcase__content" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <div class="unlimited-showcase__eyebrow">{{ __('messages.home_unlimited_eyebrow') }}</div>
                    <h3 aria-label="IPTV Network Features Heading">{{ __('messages.network_heading') }}</h3>
                    @unless (request()->routeIs('home') || request()->is('/') || request()->routeIs('about') || request()->is('reseller-panel'))
                        <h3 class="h6" aria-label="IPTV Network Features Heading">{{ __('messages.subheadingiptv') }}</h3>
                    @endunless
                    <p class="unlimited-showcase__intro">{{ __('messages.home_unlimited_intro') }}</p>

                    <ul class="unlimited-showcase__list" aria-label="List of IPTV Features">
                        @foreach ($features as $feature)
                            <li class="unlimited-showcase__item" aria-label="{{ $feature['title'] }}" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                                <span class="unlimited-showcase__icon flaticon-tick-1" aria-hidden="true"></span>
                                <div class="unlimited-showcase__copy">
                                    <strong>{{ $feature['title'] }}</strong>
                                    <span>{{ $feature['description'] }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

