@php
    $nativeCarouselRtl = $nativeCarouselRtl ?? in_array(app()->getLocale(), ['ar', 'ur'], true);
@endphp

<section class="clients-section" aria-label="{{ __('document_ui.subscription.brands_aria') }}">
    <div class="auto-container">
        <div class="channel-showcase">
        @if (!empty($useNativeCarousel))
            <div class="native-carousel native-carousel--cards native-carousel--logos"
                data-native-carousel
                data-items-desktop="5"
                data-items-tablet="3"
                data-items-mobile="2"
                data-gap="0"
                data-autoplay="3500"
                data-rtl="{{ $nativeCarouselRtl ? 'true' : 'false' }}"
                role="region"
                aria-label="{{ __('document_ui.subscription.brands_aria') }}">
                <div class="native-carousel__viewport">
                    <div class="native-carousel__track">
                        @foreach ($logos as $logo)
                            @php
                                $logoPath = is_array($logo) ? ($logo['image'] ?? '') : $logo;
                                $altText = is_array($logo) ? ($logo['alt'] ?? '') : '';
                                if (!$altText) {
                                    $brandName = ucfirst(str_replace(['-', '_'], ' ', pathinfo($logoPath, PATHINFO_FILENAME)));
                                    $altText = __('document_ui.subscription.client_logo_aria', ['name' => $brandName]);
                                }
                            @endphp
                            <div class="native-carousel__slide">
                                <div class="channel-showcase__card" role="group" aria-label="{{ $altText }}">
                                    <div class="image-box">
                                        <div class="wrapper-circle">
                                            <img src="{{ asset($logoPath) }}" alt="{{ $altText }}" width="100" height="100" loading="lazy" decoding="async" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <ul class="sponsors-carousel owl-carousel owl-theme" role="region"
                aria-label="{{ __('document_ui.subscription.brands_aria') }}">
                @foreach ($logos as $logo)
                    @php
                        $logoPath = is_array($logo) ? ($logo['image'] ?? '') : $logo;
                        $altText = is_array($logo) ? ($logo['alt'] ?? '') : '';
                        if (!$altText) {
                            $brandName = ucfirst(str_replace(['-', '_'], ' ', pathinfo($logoPath, PATHINFO_FILENAME)));
                            $altText = __('document_ui.subscription.client_logo_aria', ['name' => $brandName]);
                        }
                    @endphp
                    <li role="group" aria-label="{{ $altText }}">
                        <div class="channel-showcase__card">
                            <div class="image-box">
                            <div class="wrapper-circle">
                                <img src="{{ asset($logoPath) }}" alt="{{ $altText }}" width="100" height="100" loading="lazy" decoding="async" />
                            </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
        </div>
    </div>
</section>
