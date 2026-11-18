{{-- Home two-column section: image | heading + paragraph (stacks on mobile, RTL-aware) --}}
@php
    $isDocumentEnglish = true;
    $documentSplit = __('document_home.split');
@endphp
<section class="home-split-section py-5" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" aria-labelledby="home-split-heading">
    <div class="auto-container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <svg viewBox="0 0 640 640" width="640" height="640" class="img-fluid"
                    aria-hidden="true" focusable="false" style="width: 100%; height: auto;">
                    <rect width="640" height="640" rx="40" fill="#e8f6f5" />
                    <rect x="48" y="144" width="544" height="328" rx="24" fill="#132e44" />
                    <rect x="72" y="168" width="496" height="280" rx="10" fill="#1e5162" />
                    <path d="M288 240 392 308 288 376Z" fill="#86d8cd" />
                    <path d="M320 472V520M240 520H400" fill="none" stroke="#132e44" stroke-width="20" stroke-linecap="round" />
                </svg>
            </div>
            <div class="col-lg-6" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                <div class="home-split-section__eyebrow mb-2">{{ $isDocumentEnglish ? $documentSplit['eyebrow'] : __('messages.home_split_eyebrow') }}</div>
                <h2 id="home-split-heading" class="mb-3">{{ $isDocumentEnglish ? $documentSplit['heading'] : __('messages.home_split_heading') }}</h2>
                <p class="home-split-section__text mb-0">{{ $isDocumentEnglish ? $documentSplit['text'] : __('messages.home_split_text') }}</p>
            </div>
        </div>
    </div>
</section>
