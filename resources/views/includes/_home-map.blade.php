{{-- Home map embed section (last section): Google Maps, no API key required, responsive 16:9 --}}
<section class="home-map-section py-5" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" aria-labelledby="home-map-heading">
    <div class="auto-container">
        <div class="sec-title mb-4" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
            <div class="separator"></div>
            <h3 id="home-map-heading" class="h3">{{ __('messages.home_map_heading') }}</h3>
            <p class="mb-0">{{ __('messages.home_map_text') }}</p>
        </div>
        <div class="home-map-section__embed" role="region" aria-label="{{ __('messages.home_map_aria') }}"
            style="position: relative; width: 100%; height: 0; padding-bottom: 56.25%; overflow: hidden;">
            <iframe
                src="https://www.google.com/maps?q=Saskatoon%20SK%2C%20Canada&output=embed"
                title="{{ __('messages.home_map_title') }}"
                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen></iframe>
        </div>
    </div>
</section>
