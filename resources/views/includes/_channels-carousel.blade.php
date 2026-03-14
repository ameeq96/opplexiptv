<section class="clients-section {{ !empty($useSectionSkeletons) ? 'skeleton-section skeleton-section--logos' : '' }}"
    @if (!empty($useSectionSkeletons)) data-skeleton-section @endif
    aria-label="Trusted Brands Using Opplex IPTV">
    @if (!empty($useSectionSkeletons))
        <div class="section-skeleton__overlay" aria-hidden="true">
            <div class="section-skeleton__content">
                <span class="section-skeleton__pill"></span>
                <span class="section-skeleton__line section-skeleton__line--lg"></span>
                <div class="section-skeleton__cards">
                    <span class="section-skeleton__card"></span>
                    <span class="section-skeleton__card"></span>
                    <span class="section-skeleton__card"></span>
                    <span class="section-skeleton__card"></span>
                    <span class="section-skeleton__card"></span>
                </div>
            </div>
        </div>
    @endif
    <div class="auto-container">
        @if (!empty($useNativeCarousel))
            <div class="native-carousel native-carousel--cards native-carousel--logos"
                data-native-carousel
                data-items-desktop="5"
                data-items-tablet="3"
                data-items-mobile="2"
                data-gap="0"
                data-autoplay="3500"
                role="region"
                aria-label="Client logos carousel">
                <div class="native-carousel__viewport">
                    <div class="native-carousel__track">
                        @foreach ($logos as $logo)
                            @php
                                $logoPath = is_array($logo) ? ($logo['image'] ?? '') : $logo;
                                $altText = is_array($logo) ? ($logo['alt'] ?? '') : '';
                                if (!$altText) {
                                    $brandName = pathinfo($logoPath, PATHINFO_FILENAME);
                                    $altText = ucfirst(str_replace(['-', '_'], ' ', $brandName)) . ' logo';
                                }
                            @endphp
                            <div class="native-carousel__slide">
                                <div role="group" aria-label="Client logo: {{ $altText }}">
                                    <div class="image-box">
                                        <div class="wrapper-circle">
                                            <img src="{{ asset($logoPath) }}" alt="{{ $altText }}" width="100" height="100" loading="lazy" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <ul class="sponsors-carousel owl-carousel owl-theme" role="region" aria-label="Client logos carousel">
                @foreach ($logos as $logo)
                    @php
                        $logoPath = is_array($logo) ? ($logo['image'] ?? '') : $logo;
                        $altText = is_array($logo) ? ($logo['alt'] ?? '') : '';
                        if (!$altText) {
                            $brandName = pathinfo($logoPath, PATHINFO_FILENAME);
                            $altText = ucfirst(str_replace(['-', '_'], ' ', $brandName)) . ' logo';
                        }
                    @endphp
                    <li role="group" aria-label="Client logo: {{ $altText }}">
                        <div class="image-box">
                            <div class="wrapper-circle">
                                <img src="{{ asset($logoPath) }}" alt="{{ $altText }}" width="100" height="100" loading="lazy" />
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
