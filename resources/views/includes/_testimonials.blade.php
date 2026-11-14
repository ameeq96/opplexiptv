@php
    $documentReviewRoutes = ['home', 'packages', 'about', 'reseller-panel', 'iptv-subscription-service'];
    $usesDocumentTestimonials = in_array(optional(request()->route())->getName(), $documentReviewRoutes, true)
        && in_array(app()->getLocale(), config('app.locales', ['en']), true);
    $documentTestimonials = $usesDocumentTestimonials ? __('document_home.testimonials') : [];
    $reviewItems = $usesDocumentTestimonials
        ? collect($documentTestimonials['reviews'])
            ->values()
            ->map(fn (array $review) => [
                'author_name' => $review['author'],
                'text' => $review['text'],
            ])
        : collect($testimonials ?? [])
            ->filter(fn ($testimonial) => !empty($testimonial['text']) && !empty($testimonial['author_name']))
            ->take(8)
            ->values();
    $reviewEyebrow = $usesDocumentTestimonials
        ? $documentTestimonials['title']
        : ($reviewEyebrow ?? __('messages.testimonials_title'));
    $reviewHeading = $usesDocumentTestimonials
        ? $documentTestimonials['heading']
        : ($reviewHeading ?? __('messages.testimonials_heading'));
    $reviewIntro = $usesDocumentTestimonials ? $documentTestimonials['intro'] : ($reviewIntro ?? null);
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
                @if (filled($reviewIntro))
                    <p class="review-showcase__intro">{{ $reviewIntro }}</p>
                @endif
            </header>

            <div class="review-showcase__grid" role="list">
                @foreach ($reviewItems as $testimonial)
                    <figure class="testimonial-card review-showcase__card" role="listitem">
                        @if ($usesDocumentTestimonials)
                            <div class="review-showcase__quote">
                                <span class="review-showcase__quote-mark" aria-hidden="true"><i class="fa fa-lightbulb-o"></i></span>
                                <p>{{ $testimonial['text'] }}</p>
                            </div>
                        @else
                            <blockquote class="review-showcase__quote">
                                <span class="review-showcase__quote-mark" aria-hidden="true">&ldquo;</span>
                                <p>{{ $testimonial['text'] }}</p>
                            </blockquote>
                        @endif

                        <figcaption class="review-showcase__author">
                            @if ($usesDocumentTestimonials)
                                <span aria-hidden="true" style="display: inline-flex; align-items: center; justify-content: center; flex: 0 0 56px; width: 56px; height: 56px;"><i class="fa fa-list-alt"></i></span>
                            @else
                                <img
                                    src="{{ asset(($testimonial['image'] ?? null) ?: 'images/placeholder.webp') }}"
                                    alt="{{ __('document_ui.home.review_photo_alt', ['name' => $testimonial['author_name']]) }}"
                                    width="56"
                                    height="56"
                                    loading="lazy" decoding="async">
                            @endif
                            <span class="review-showcase__author-copy">
                                <strong>{{ $testimonial['author_name'] }}</strong>
                                @if (!$usesDocumentTestimonials && !empty($testimonial['is_publicly_verified']))
                                    <small class="testimonial-card__author-role">{{ $verifiedLabel }}</small>
                                @endif
                            </span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif
