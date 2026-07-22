@extends('layouts.default')
@section('title', app()->getLocale() === 'en' ? __('document_commerce.packages.page_title') : __('messages.title'))

@if (app()->getLocale() === 'en')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/document-commerce.css') }}?v={{ @filemtime(public_path('css/document-commerce.css')) ?: 1 }}">
    @endpush
@endif

@section('content')
    @if (app()->getLocale() === 'en')
        @php
            $page = __('document_commerce.packages');
            $trialUrl = 'https://wa.me/16393903194?text=' . urlencode(__('messages.whatsapp_trial'));
            $reviewImages = [
                'images/img-test-2.webp',
                'images/img-test-3.webp',
                'images/resource/author-1.webp',
                'images/resource/author-2.webp',
                'images/img-test.webp',
                'images/resource/author-3.webp',
                'images/resource/author-5.webp',
                'images/resource/author-6.webp',
            ];
            $packageTestimonials = collect($testimonials ?? [])->values();

            if ($packageTestimonials->isEmpty()) {
                $packageTestimonials = collect(__('messages.home_document.testimonials.reviews'))
                    ->values()
                    ->map(fn (array $review, int $index) => [
                        'author_name' => $review['author'] ?? '',
                        'text' => $review['text'] ?? '',
                        'image' => $reviewImages[$index] ?? null,
                    ]);
            }
        @endphp

        <x-page-title
            :title="$page['page_title']"
            :breadcrumbs="[
                ['url' => route('home'), 'label' => __('messages.nav_home'), 'aria' => 'Go to Home'],
                ['label' => $page['page_title']],
            ]"
            background="images/background/9.webp"
            :rtl="$isRtl"
            aria-label="Our IPTV packages page"
        />

        <main class="doc-commerce doc-commerce--packages">
            <section class="dc-hero" aria-labelledby="packages-document-title">
                <div class="auto-container">
                    <div class="dc-hero__content">
                        <span class="dc-eyebrow">{{ $page['page_title'] }}</span>
                        <h1 id="packages-document-title" class="dc-title">{{ $page['hero']['heading'] }}</h1>
                        <p class="dc-lead">{{ $page['hero']['text'] }}</p>

                        <div class="dc-feature-grid" role="list">
                            @foreach ($page['hero']['features'] as $feature)
                                <article class="dc-feature-card" role="listitem">
                                    <span class="dc-feature-card__icon {{ $feature['icon'] }}" aria-hidden="true"></span>
                                    <h2>{{ $feature['title'] }}</h2>
                                    <p>{{ $feature['description'] }}</p>
                                </article>
                            @endforeach
                        </div>

                        <div class="dc-actions">
                            <a class="dc-button dc-button--primary" href="#pricing-section">
                                {{ $page['hero']['primary_cta'] }}
                            </a>
                            <a class="dc-button dc-button--secondary" href="{{ $trialUrl }}" target="_blank"
                                rel="noopener" data-trial data-wa-href="{{ $trialUrl }}">
                                {{ $page['hero']['secondary_cta'] }}
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            @include('includes._best-packages', ['documentPricing' => $page['pricing']])

            <section class="dc-section dc-section--soft" aria-labelledby="packages-how-title">
                <div class="auto-container">
                    <div class="dc-section__header">
                        <span class="dc-eyebrow">How It Works</span>
                        <h2 id="packages-how-title">{{ $page['how_it_works']['heading'] }}</h2>
                    </div>

                    <ol class="dc-step-grid">
                        @foreach ($page['how_it_works']['steps'] as $step)
                            <li class="dc-step-card">
                                <span class="dc-step-card__number" aria-hidden="true">{{ $loop->iteration }}</span>
                                <div>
                                    <h3>{{ $step['title'] }}</h3>
                                    <p>{{ $step['description'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <p class="dc-device-line">{{ $page['how_it_works']['devices'] }}</p>
                    <div class="dc-actions dc-actions--centered">
                        <a class="dc-button dc-button--primary" href="{{ route('iptv-applications') }}">
                            {{ $page['how_it_works']['cta'] }}
                        </a>
                    </div>
                </div>
            </section>

            <section class="dc-section" aria-labelledby="packages-why-title">
                <div class="auto-container">
                    <div class="dc-section__header">
                        <span class="dc-eyebrow">Why Choose Opplex</span>
                        <h2 id="packages-why-title">{{ $page['why_choose']['heading'] }}</h2>
                    </div>
                    <div class="dc-content-grid dc-content-grid--four" role="list">
                        @foreach ($page['why_choose']['items'] as $item)
                            <article class="dc-content-card" role="listitem">
                                <span class="dc-content-card__check fa fa-check" aria-hidden="true"></span>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="dc-section dc-section--dark" aria-labelledby="packages-included-title">
                <div class="auto-container dc-split">
                    <div class="dc-split__intro">
                        <span class="dc-eyebrow">Included in Every Plan</span>
                        <h2 id="packages-included-title">{{ $page['included']['heading'] }}</h2>
                        <p>{{ $page['included']['intro'] }}</p>
                    </div>
                    <ul class="dc-check-list">
                        @foreach ($page['included']['items'] as $item)
                            <li><span class="fa fa-check" aria-hidden="true"></span><span>{{ $item }}</span></li>
                        @endforeach
                    </ul>
                </div>
            </section>

            <section class="dc-section dc-section--soft" aria-labelledby="packages-reviews-title">
                <div class="auto-container">
                    <div class="dc-section__header">
                        <span class="dc-eyebrow">Customer Reviews</span>
                        <h2 id="packages-reviews-title">{{ $page['reviews']['heading'] }}</h2>
                    </div>
                    @if ($packageTestimonials->isNotEmpty())
                        <div class="dc-review-grid" role="list">
                            @foreach ($packageTestimonials->take(8) as $testimonial)
                                <figure class="dc-review-card" role="listitem">
                                    <blockquote>
                                        <span class="dc-review-card__quote" aria-hidden="true">“</span>
                                        <p>{{ $testimonial['text'] ?? '' }}</p>
                                    </blockquote>
                                    <figcaption>
                                        @if (!empty($testimonial['image']))
                                            <img src="{{ asset($testimonial['image']) }}"
                                                alt="Photo of {{ $testimonial['author_name'] ?? '' }}, IPTV customer"
                                                width="56" height="56" loading="lazy" decoding="async">
                                        @endif
                                        <span>
                                            <strong>{{ $testimonial['author_name'] ?? '' }}</strong>
                                            <small>{{ $page['reviews']['verified'] }}</small>
                                        </span>
                                    </figcaption>
                                </figure>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            @include('includes._faq-section', [
                'faqItems' => $page['faq']['items'],
                'faqTitle' => $page['faq']['heading'],
            ])

            <section class="dc-trial" aria-labelledby="packages-trial-title">
                <div class="auto-container">
                    <div class="dc-trial__panel">
                        <div>
                            <span class="dc-eyebrow">Free IPTV Trial</span>
                            <h2 id="packages-trial-title">{{ $page['trial']['heading'] }}</h2>
                            <p>{{ $page['trial']['text'] }}</p>
                            <ul class="dc-chip-list" aria-label="Free trial benefits">
                                @foreach ($page['trial']['items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <a class="dc-button dc-button--light" href="{{ $trialUrl }}" target="_blank" rel="noopener"
                            data-trial data-wa-href="{{ $trialUrl }}">
                            {{ $page['trial']['cta'] }}
                        </a>
                    </div>
                </div>
            </section>
        </main>
    @else
        <!-- Page Title -->
        <x-page-title
            :title="__('messages.heading')"
            :breadcrumbs="[
                ['url' => '/', 'label' => __('messages.breadcrumb.home'), 'aria' => 'Go to Home'],
                ['label' => __('messages.breadcrumb.current')],
            ]"
            background="images/background/9.webp"
            :rtl="$isRtl"
            aria-label="Page title section with IPTV background"
        />
        <!-- End Page Title -->

        <!-- Pricing Section -->
        @include('includes._best-packages')
        <!-- End Pricing Section -->

        <!-- Internet Section -->
        <section class="internet-section"
                 style="background-image: url('{{ asset('images/background/1.webp') }}')"
                 aria-label="Opplex IPTV package overview section">
            <div class="auto-container">
                <div class="clearfix">
                    <div class="content-column">
                        <h3>{{ __('messages.sub_heading') }}</h3>
                        <div class="text text-dark">{{ __('messages.description') }}</div>
                        <div class="price">{!! __('messages.price') !!}</div>
                        <a href="{{ route('about') }}" class="theme-btn btn-style-four"
                           aria-label="Read more about Opplex IPTV">
                            <span class="txt">
                                {{ __('messages.read_more') }}
                                <i class="rtl-rotate lnr lnr-arrow-right" aria-hidden="true"></i>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Internet Section -->

        <!-- Choose Us Section -->
        @include('includes._choose-us')

            <!-- FAQ Section -->
        @include('includes._faq-section')

            <!-- Check Trial Section -->
        @include('includes._check-trail')
    @endif
@stop
