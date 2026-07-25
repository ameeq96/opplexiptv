@php
    $documentReviewRoutes = ['home', 'packages', 'about', 'reseller-panel', 'iptv-subscription-service'];
    $usesDocumentTestimonials = in_array(optional(request()->route())->getName(), $documentReviewRoutes, true)
        && in_array(app()->getLocale(), config('app.locales', ['en']), true);
    $documentTestimonials = $usesDocumentTestimonials ? __('document_home.testimonials') : [];
    $documentReviewImages = [
        'images/img-test-2.webp',
        'images/img-test-3.webp',
        'images/resource/author-1.webp',
        'images/resource/author-2.webp',
        'images/img-test.webp',
        'images/resource/author-3.webp',
        'images/resource/author-5.webp',
        'images/resource/author-6.webp',
    ];
    $reviewItems = $usesDocumentTestimonials
        ? collect($documentTestimonials['reviews'])
            ->values()
            ->map(fn (array $review, int $index) => [
                'author_name' => $review['author'],
                'text' => $review['text'],
                'image' => $documentReviewImages[$index] ?? null,
            ])
        : collect($testimonials ?? [])
            ->filter(fn ($testimonial) => !empty($testimonial['text']) && !empty($testimonial['author_name']))
            ->take(8)
            ->values();
    $reviewEyebrow = $reviewEyebrow
        ?? ($usesDocumentTestimonials ? $documentTestimonials['title'] : __('messages.testimonials_title'));
    $reviewHeading = $reviewHeading
        ?? ($usesDocumentTestimonials ? $documentTestimonials['heading'] : __('messages.testimonials_heading'));
    $verifiedLabel = $verifiedLabel
        ?? ($usesDocumentTestimonials
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
                                alt="{{ __('document_ui.home.review_photo_alt', ['name' => $testimonial['author_name']]) }}"
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
