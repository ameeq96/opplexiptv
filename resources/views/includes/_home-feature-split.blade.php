{{-- Home two-column section: image | heading + paragraph (stacks on mobile, RTL-aware) --}}
<section class="home-split-section py-5" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" aria-labelledby="home-split-heading">
    <div class="auto-container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="{{ asset('images/resource/streaming-3.webp') }}"
                    alt="{{ __('messages.home_split_image_alt') }}" loading="lazy" decoding="async"
                    width="600" height="400" class="img-fluid" style="width: 100%; height: auto;" />
            </div>
            <div class="col-lg-6" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                <div class="home-split-section__eyebrow mb-2">{{ __('messages.home_split_eyebrow') }}</div>
                <h2 id="home-split-heading" class="mb-3">{{ __('messages.home_split_heading') }}</h2>
                <p class="home-split-section__text mb-0">{{ __('messages.home_split_text') }}</p>
            </div>
        </div>
    </div>
</section>
