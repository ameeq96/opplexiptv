<section class="clients-section" aria-label="Trusted Brands Using Opplex IPTV">
    <div class="auto-container">
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
    </div>
</section>
