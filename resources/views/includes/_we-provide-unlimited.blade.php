

<section class="network-section unlimited-showcase @unless ($isMobile) @else p-0 @endunless"
    aria-label="{{ __('document_ui.home.features_aria') }}">

    @php
        $isDocumentEnglishUnlimited = request()->routeIs('home');
        $documentUnlimited = $isDocumentEnglishUnlimited ? __('document_home.unlimited') : [];
        $displayFeatures = $isDocumentEnglishUnlimited ? $documentUnlimited['features'] : $features;
    @endphp

    <div class="auto-container unlimited-showcase__shell">
        <div class="inner-container unlimited-showcase__panel">
            <div class="unlimited-showcase__grid">
                @unless ($isMobile)
                    <div class="unlimited-showcase__media" aria-hidden="true">
                        <div class="unlimited-showcase__main">
                            <img src="{{ asset('images/resource/network-4.webp') }}" alt="{{ __('document_ui.home.network_main_alt') }}"
                                loading="lazy" decoding="async" width="396" height="527" />
                        </div>
                        <div class="unlimited-showcase__screen">
                            <img src="{{ asset('images/resource/network-5.webp') }}" alt="{{ __('document_ui.home.network_screen_alt') }}"
                                loading="lazy" decoding="async" width="312" height="173" />
                        </div>
                        <div class="unlimited-showcase__person-wrap">
                            <div class="unlimited-showcase__person">
                                <img src="{{ asset('images/resource/network-3.webp') }}" alt="{{ __('document_ui.home.network_person_alt') }}"
                                    loading="lazy" decoding="async" width="345" height="285" />
                            </div>
                        </div>
                        <div class="unlimited-showcase__signal">
                            <img src="{{ asset('images/icons/service-4.webp') }}" alt="" aria-hidden="true"
                                loading="lazy" decoding="async" width="80" height="70" />
                        </div>
                    </div>
                @endunless

                <div class="unlimited-showcase__content" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <div class="unlimited-showcase__eyebrow">{{ $isDocumentEnglishUnlimited ? $documentUnlimited['eyebrow'] : __('messages.home_unlimited_eyebrow') }}</div>
                    <h3 aria-label="{{ __('document_ui.home.network_heading_aria') }}">{{ $isDocumentEnglishUnlimited ? $documentUnlimited['heading'] : __('messages.network_heading') }}</h3>
                    @unless (request()->routeIs('home') || request()->is('/') || request()->routeIs('about') || request()->is('reseller-panel'))
                        <h3 class="h6" aria-label="{{ __('document_ui.home.network_heading_aria') }}">{{ __('messages.subheadingiptv') }}</h3>
                    @endunless
                    <p class="unlimited-showcase__intro">{{ $isDocumentEnglishUnlimited ? $documentUnlimited['intro'] : __('messages.home_unlimited_intro') }}</p>

                    <ul class="unlimited-showcase__list" aria-label="{{ __('document_ui.home.features_list_aria') }}">
                        @foreach ($displayFeatures as $feature)
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
