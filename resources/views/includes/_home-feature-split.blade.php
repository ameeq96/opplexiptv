{{-- Home two-column section: image | heading + paragraph (stacks on mobile, RTL-aware) --}}
@php
    $isDocumentEnglish = app()->getLocale() === 'en';
    $documentSplit = $isDocumentEnglish ? __('messages.home_document.split') : [];
@endphp
<section class="home-split-section py-5" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" aria-labelledby="home-split-heading">
    <div class="auto-container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                {{-- Below-fold image; keep it lazy so the hero stays fast. --}}
                <img src="{{ asset('images/resource/movie-night-tv.webp') }}"
                    alt="{{ $isDocumentEnglish ? $documentSplit['image_alt'] : __('messages.home_split_image_alt') }}" loading="lazy" fetchpriority="low" decoding="async"
                    width="1600" height="900" class="img-fluid" style="width: 100%; height: auto;" />
            </div>
            <div class="col-lg-6" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                <div class="home-split-section__eyebrow mb-2">{{ $isDocumentEnglish ? $documentSplit['eyebrow'] : __('messages.home_split_eyebrow') }}</div>
                <h3 id="home-split-heading" class="mb-3">{{ $isDocumentEnglish ? $documentSplit['heading'] : __('messages.home_split_heading') }}</h3>
                <p class="home-split-section__text mb-0">{{ $isDocumentEnglish ? $documentSplit['text'] : __('messages.home_split_text') }}</p>
            </div>
        </div>
    </div>
</section>
