{{-- Home two-column section: image | heading + paragraph (stacks on mobile, RTL-aware) --}}
@php
    $isDocumentEnglish = true;
    $documentSplit = __('document_home.split');
@endphp
<section class="home-split-section py-5" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" aria-labelledby="home-split-heading">
    <div class="auto-container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="{{ asset('images/resource/movie-night-tv-1024.webp') }}"
                    srcset="{{ asset('images/resource/movie-night-tv-480.webp') }} 480w,
                        {{ asset('images/resource/movie-night-tv-720.webp') }} 720w,
                        {{ asset('images/resource/movie-night-tv-1024.webp') }} 1024w,
                        {{ asset('images/resource/movie-night-tv-1280.webp') }} 1280w"
                    sizes="(min-width: 1340px) 640px, (min-width: 992px) calc(50vw - 30px), calc(100vw - 30px)"
                    alt="{{ $isDocumentEnglish ? $documentSplit['image_alt'] : __('messages.home_split_image_alt') }}"
                    loading="lazy"
                    fetchpriority="low"
                    decoding="async" width="1024" height="1024" class="img-fluid"
                    style="width: 100%; height: auto;" />
            </div>
            <div class="col-lg-6" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                <div class="home-split-section__eyebrow mb-2">{{ $isDocumentEnglish ? $documentSplit['eyebrow'] : __('messages.home_split_eyebrow') }}</div>
                <h3 id="home-split-heading" class="mb-3">{{ $isDocumentEnglish ? $documentSplit['heading'] : __('messages.home_split_heading') }}</h3>
                <p class="home-split-section__text mb-0">{{ $isDocumentEnglish ? $documentSplit['text'] : __('messages.home_split_text') }}</p>
            </div>
        </div>
    </div>
</section>
