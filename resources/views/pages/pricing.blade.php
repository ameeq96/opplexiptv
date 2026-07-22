@extends('layouts.default')
@section('title', app()->getLocale() === 'en' ? __('document_commerce.pricing.page_title') : __('messages.title'))

@push('schema')
    {!! jsonld(seo()->service(
        'IPTV Subscription Plans',
        'Affordable IPTV subscription plans with 12,000+ live channels, sports, movies and VOD in HD & 4K, on every device.',
        route('pricing'),
        seo()->packageOffers('iptv'),
    )) !!}
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pricing.css') }}?v={{ @filemtime(public_path('css/pricing.css')) ?: 1 }}">
    @if (app()->getLocale() === 'en')
        <link rel="stylesheet" href="{{ asset('css/document-commerce.css') }}?v={{ @filemtime(public_path('css/document-commerce.css')) ?: 1 }}">
    @endif
@endpush

@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
        $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';
    @endphp

    @if (app()->getLocale() === 'en')
        @php
            $page = __('document_commerce.pricing');
            $trialUrl = 'https://wa.me/16393903194?text=' . urlencode(__('messages.whatsapp_trial'));
        @endphp

        <x-page-title :title="$page['page_title']" :breadcrumbs="[
            ['url' => route('home'), 'label' => __('messages.nav_home')],
            ['label' => $page['page_title']],
        ]" background="images/background/7.webp" :rtl="$isRtl"
            aria-label="IPTV subscription pricing page" />

        <main class="doc-commerce doc-commerce--pricing">
            <section class="dc-hero dc-hero--compact" aria-labelledby="pricing-document-title">
                <div class="auto-container">
                    <div class="dc-hero__content">
                        <span class="dc-eyebrow">{{ $page['hero']['eyebrow'] }}</span>
                        <h1 id="pricing-document-title" class="dc-title">{{ $page['hero']['heading'] }}</h1>
                        <div class="dc-prose dc-prose--lead">
                            @foreach ($page['hero']['paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            @include('includes._best-packages')

            <section class="dc-section dc-section--dark" aria-labelledby="pricing-included-title">
                <div class="auto-container dc-split">
                    <div class="dc-split__intro">
                        <span class="dc-eyebrow">Included in Every Plan</span>
                        <h2 id="pricing-included-title">{{ $page['included']['heading'] }}</h2>
                        <p>{{ $page['included']['intro'] }}</p>
                    </div>
                    <ul class="dc-check-list">
                        @foreach ($page['included']['items'] as $item)
                            <li><span class="fa fa-check" aria-hidden="true"></span><span>{{ $item }}</span></li>
                        @endforeach
                    </ul>
                </div>
            </section>

            <section class="prcx" aria-labelledby="pricing-promo-title">
                <div class="auto-container">
                    <div class="prcx__panel">
                        <div class="prcx__content">
                            <div class="prcx__bar" aria-hidden="true"></div>
                            <h2 id="pricing-promo-title" class="prcx__title">{{ __('messages.sub_heading') }}</h2>
                            <p class="prcx__text">{{ __('messages.description') }}</p>
                            @if (trim((string) __('messages.price')) !== '')
                                <div class="prcx__price">{!! __('messages.price') !!}</div>
                            @endif
                            <div>
                                <a href="{{ route('about') }}" class="prcx__cta" aria-label="{{ __('messages.read_more') }}">
                                    {{ __('messages.read_more') }}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </a>
                            </div>
                        </div>

                        <div class="prcx__visual" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3.5" width="20" height="13" rx="2.5"/>
                                <path d="M8 20.5h8M12 16.5v4"/>
                                <path d="M10 7.2v5.6l5-2.8-5-2.8z" fill="currentColor" stroke="none"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </section>

            <section class="dc-section dc-section--soft" aria-labelledby="pricing-guide-title">
                <div class="auto-container">
                    <div class="dc-section__header">
                        <span class="dc-eyebrow">Plan Guide</span>
                        <h2 id="pricing-guide-title">{{ $page['guide']['heading'] }}</h2>
                    </div>
                    <div class="dc-plan-grid" role="list">
                        @foreach ($page['guide']['items'] as $item)
                            <article class="dc-plan-card" role="listitem">
                                <span class="dc-plan-card__number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                    <aside class="dc-note" aria-label="Try Opplex before choosing a plan">
                        <div>
                            <h3>{{ $page['guide']['trial_heading'] }}</h3>
                            <p>{{ $page['guide']['trial_text'] }}</p>
                        </div>
                        <a class="dc-button dc-button--primary" href="{{ $trialUrl }}" target="_blank" rel="noopener"
                            data-trial data-wa-href="{{ $trialUrl }}">Start Free Trial</a>
                    </aside>
                </div>
            </section>

            <section class="dc-section" aria-labelledby="pricing-payment-title">
                <div class="auto-container">
                    <div class="dc-payment-panel">
                        <div class="dc-payment-panel__icon fa fa-lock" aria-hidden="true"></div>
                        <div>
                            <span class="dc-eyebrow">Payment and Security</span>
                            <h2 id="pricing-payment-title">{{ $page['payment']['heading'] }}</h2>
                            <div class="dc-prose">
                                @foreach ($page['payment']['paragraphs'] as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            @include('includes._faq-section', [
                'faqItems' => $page['faq']['items'],
                'faqTitle' => $page['faq']['heading'],
            ])
        </main>
    @else
        <!-- Page Title -->
        <x-page-title :title="__('messages.heading')" :breadcrumbs="[
            ['url' => '/', 'label' => __('messages.breadcrumb.home')],
            ['label' => __('messages.breadcrumb.current')],
        ]" background="images/background/7.webp" :rtl="$isRtl"
            aria-label="Generic Page" />
        <!-- End Page Title -->

        <!-- Pricing Section -->
        @include('includes._best-packages')
        <!-- End Pricing Section -->

        <!-- Pricing promo band -->
        <section class="prcx {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" aria-labelledby="pricing-promo-title">
            <div class="auto-container">
                <div class="prcx__panel">
                    <div class="prcx__content">
                        <div class="prcx__bar" aria-hidden="true"></div>
                        <h1 id="pricing-promo-title" class="prcx__title">{{ __('messages.sub_heading') }}</h1>
                        <p class="prcx__text">{{ __('messages.description') }}</p>
                        @if (trim((string) __('messages.price')) !== '')
                            <div class="prcx__price">{!! __('messages.price') !!}</div>
                        @endif
                        <div>
                            <a href="{{ route('about') }}" class="prcx__cta" aria-label="{{ __('messages.read_more') }}">
                                {{ __('messages.read_more') }}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </a>
                        </div>
                    </div>

                    <div class="prcx__visual" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3.5" width="20" height="13" rx="2.5"/>
                            <path d="M8 20.5h8M12 16.5v4"/>
                            <path d="M10 7.2v5.6l5-2.8-5-2.8z" fill="currentColor" stroke="none"/>
                        </svg>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Pricing promo band -->

        {{-- FAQ Section --}}
        @include('includes._faq-section')
    @endif

@stop
