@php
    $isDocumentEnglishTestimonials = request()->routeIs('home') && app()->getLocale() === 'en';
    $documentTestimonials = $isDocumentEnglishTestimonials ? __('messages.home_document.testimonials') : [];
    $reviewItems = collect($testimonials ?? [])
        ->filter(fn ($testimonial) => !empty($testimonial['text']) && !empty($testimonial['author_name']))
        ->take(8)
        ->values();
    $reviewEyebrow = $reviewEyebrow
        ?? ($isDocumentEnglishTestimonials ? $documentTestimonials['title'] : __('messages.testimonials_title'));
    $reviewHeading = $reviewHeading
        ?? ($isDocumentEnglishTestimonials ? $documentTestimonials['heading'] : __('messages.testimonials_heading'));
    $verifiedLabel = $verifiedLabel
        ?? ($isDocumentEnglishTestimonials
            ? $documentTestimonials['verified_label']
            : __('messages.home_testimonials_verified_customer'));
    $reviewSectionId = $reviewSectionId ?? 'customer-reviews-title';
    $isReviewRtl = $isRtl ?? in_array(app()->getLocale(), ['ar', 'ur'], true);
@endphp

@if ($reviewItems->isNotEmpty())
    <section class="testimonial-section review-showcase" aria-labelledby="{{ $reviewSectionId }}" dir="{{ $isReviewRtl ? 'rtl' : 'ltr' }}">
        <div class="auto-container">
            <header class="testimonial-showcase__heading review-showcase__heading">
                <div class="review-showcase__eyebrow">
                    <span aria-hidden="true"></span>
                    {{ $reviewEyebrow }}
                </div>
                <h2 id="{{ $reviewSectionId }}">{{ $reviewHeading }}</h2>
            </header>

            <div class="review-showcase__grid" role="list">
                @foreach ($reviewItems as $testimonial)
                    <figure class="testimonial-card review-showcase__card" role="listitem">
                        <blockquote class="review-showcase__quote">
                            <span class="review-showcase__quote-mark" aria-hidden="true">&ldquo;</span>
                            <p>{{ $testimonial['text'] }}</p>
                        </blockquote>

                        <figcaption class="review-showcase__author">
                            <img
                                src="{{ asset(($testimonial['image'] ?? null) ?: 'images/placeholder.webp') }}"
                                alt="Photo of {{ $testimonial['author_name'] }}, IPTV customer"
                                width="56"
                                height="56"
                                loading="lazy" decoding="async">
                            <span class="review-showcase__author-copy">
                                <strong>{{ $testimonial['author_name'] }}</strong>
                                <small class="testimonial-card__author-role">{{ $verifiedLabel }}</small>
                            </span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif
