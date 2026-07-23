@extends('layouts.default')
@section('title', __('messages.site_title'))

@if (app()->getLocale() === 'en')
    @push('styles')
        @php
            $homeDocumentCss = @file_get_contents(public_path('css/home-document.css'));
        @endphp
        @if ($homeDocumentCss !== false)
            <style id="home-document-styles">{!! $homeDocumentCss !!}</style>
        @else
            <link rel="stylesheet" href="{{ asset('css/home-document.css') }}">
        @endif
    @endpush
@endif

@section('content')
    @php
        $waTrial = 'https://wa.me/16393903194?text=' . urlencode(__('messages.whatsapp_trial'));
        $currency = config('services.app.default_currency', 'USD');
        $useNativeHomeCarousel = true;
        $isDocumentEnglish = app()->getLocale() === 'en';
        $documentHome = $isDocumentEnglish ? __('messages.home_document') : [];
        $documentTestimonials = [];

        if ($isDocumentEnglish) {
            $testimonialImages = [
                'images/img-test-2.webp',
                'images/img-test-3.webp',
                'images/resource/author-1.webp',
                'images/resource/author-2.webp',
                'images/img-test.webp',
                'images/resource/author-3.webp',
                'images/resource/author-5.webp',
                'images/resource/author-6.webp',
            ];

            foreach ($documentHome['testimonials']['reviews'] as $index => $review) {
                $documentTestimonials[] = [
                    'text' => $review['text'],
                    'author_name' => $review['author'],
                    'image' => $testimonialImages[$index] ?? null,
                ];
            }
        }
    @endphp

    @include('includes._slider', ['useNativeCarousel' => $useNativeHomeCarousel])

    @if ($isDocumentEnglish)
        {{-- Document section 1: image + introduction, followed by section 2 pricing. --}}
        @include('includes._home-feature-split')
        @include('includes._best-packages')
    @else
        @include('includes._best-packages')
        @include('includes._home-feature-split')
    @endif

    {{-- @if(!empty($homeProducts) && count($homeProducts) > 0)
        <section class="shop-section shop-section-2"
            style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
            <div class="auto-container">
                <div class="home-products-shell">
                    <div class="sec-title mb-4" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                        <span class="home-products-headline" aria-hidden="true"></span>
                        <div style="display:flex; align-items:center; justify-content: space-between; gap:12px; flex-wrap:wrap;">
                            <div>
                                <h2 class="h3">{{ __('messages.home_products_digital_title') }}</h2>
                                <p class="text-muted mb-0" style="font-size:14px;">{{ __('messages.home_products_digital_desc') }}</p>
                            </div>
                            <span></span>
                        </div>
                    </div>

                    <div class="home-products-carousel native-carousel native-carousel--cards"
                        data-native-carousel
                        data-items-desktop="4"
                        data-items-tablet="2"
                        data-items-mobile="1"
                        data-gap="30"
                        data-autoplay="5000">
                        <div class="native-carousel__viewport">
                            <div class="native-carousel__track">
                        @foreach($homeProducts as $p)
                            <div class="native-carousel__slide px-2">
                                <article class="home-product-card h-100">
                                    <a class="home-product-media" href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                        @if(!empty($p['image']))
                                            <img src="{{ $p['image'] }}"
                                                 alt="{{ $p['name'] }}"
                                                 loading="lazy"
                                                 decoding="async">
                                        @endif
                                    </a>
                                    <div class="home-product-body">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                            <h3 class="home-product-title">
                                                <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                                    {{ $p['name'] }}
                                                </a>
                                            </h3>
                                            <span class="home-product-badge {{ $p['type'] === 'digital' ? 'home-product-badge--digital' : 'home-product-badge--affiliate' }}">
                                                {{ $p['type'] === 'digital' ? __('messages.home_products_type_digital') : __('messages.home_products_type_affiliate') }}
                                            </span>
                                        </div>
                                        @if(!empty($p['price']))
                                            <div class="home-product-price">{{ $p['currency'] }} {{ number_format((float) $p['price'], 2) }}</div>
                                        @endif
                                        <div class="home-product-actions">
                                            @if($p['type'] === 'digital' && !empty($p['buy_now_url']))
                                                <a href="{{ $p['buy_now_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary home-product-action">{{ __('messages.buy_now') }}</a>
                                            @else
                                                <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif class="btn btn-primary home-product-action">{{ __('messages.home_products_open_link') }}</a>
                                            @endif
                                            <button type="button"
                                                class="home-product-share"
                                                aria-label="{{ __('messages.home_products_share_label', ['name' => $p['name']]) }}"
                                                data-share-url="{{ $p['share_url'] ?? $p['url'] }}"
                                                data-share-title="{{ $p['name'] }}"
                                                data-share-text="{{ $p['share_text'] ?? __('messages.home_products_share_text', ['name' => $p['name']]) }}">
                                                <i class="fa fa-share-alt" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif --}}

    @if($isDocumentEnglish || (!empty($homeAffiliateProducts) && count($homeAffiliateProducts) > 0))
        <section class="shop-section shop-section-2 mt-5"
            style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
            <div class="auto-container">
                <div class="home-products-shell">
                    <div class="sec-title mb-4" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                        <span class="home-products-headline" aria-hidden="true"></span>
                        <div style="display:flex; align-items:center; justify-content: space-between; gap:12px; flex-wrap:wrap;">
                            <div>
                                <h2 class="h3">{{ $isDocumentEnglish ? $documentHome['affiliate']['heading'] : __('messages.home_products_affiliate_title') }}</h2>
                                <p class="text-muted mb-0" style="font-size:14px;">{{ $isDocumentEnglish ? $documentHome['affiliate']['text'] : __('messages.home_products_affiliate_desc') }}</p>
                            </div>
                            <span></span>
                        </div>
                    </div>

                    @if(!empty($homeAffiliateProducts) && count($homeAffiliateProducts) > 0)
                        <div class="home-products-carousel native-carousel native-carousel--cards"
                            data-native-carousel
                            data-items-desktop="4"
                            data-items-tablet="2"
                            data-items-mobile="1"
                            data-gap="30"
                            data-autoplay="5000">
                            <div class="native-carousel__viewport">
                                <div class="native-carousel__track">
                            @foreach($homeAffiliateProducts as $p)
                                <div class="native-carousel__slide px-2">
                                    <article class="home-product-card h-100">
                                        <a class="home-product-media" href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                            @if(!empty($p['image']))
                                                <img src="{{ $p['image'] }}"
                                                     alt="{{ $p['name'] }}"
                                                     loading="lazy"
                                                     decoding="async">
                                            @endif
                                        </a>
                                        <div class="home-product-body">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                                <h3 class="home-product-title">
                                                    <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                                        {{ $p['name'] }}
                                                    </a>
                                                </h3>
                                                <span class="home-product-badge {{ $p['type'] === 'digital' ? 'home-product-badge--digital' : 'home-product-badge--affiliate' }}">
                                                    {{ $p['type'] === 'digital' ? __('messages.home_products_type_digital') : __('messages.home_products_type_affiliate') }}
                                                </span>
                                            </div>
                                            @if(!empty($p['price']))
                                                <div class="home-product-price">{{ $p['currency'] }} {{ number_format((float) $p['price'], 2) }}</div>
                                            @endif
                                            <div class="home-product-actions">
                                                @if($p['type'] === 'digital' && !empty($p['buy_now_url']))
                                                    <a href="{{ $p['buy_now_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary home-product-action">{{ __('messages.buy_now') }}</a>
                                                @else
                                                    <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif class="btn btn-primary home-product-action">{{ __('messages.home_products_open_link') }}</a>
                                                @endif
                                                <button type="button"
                                                    class="home-product-share"
                                                    aria-label="{{ __('messages.home_products_share_label', ['name' => $p['name']]) }}"
                                                    data-share-url="{{ $p['share_url'] ?? $p['url'] }}"
                                                    data-share-title="{{ $p['name'] }}"
                                                    data-share-text="{{ $p['share_text'] ?? __('messages.home_products_share_text', ['name' => $p['name']]) }}">
                                                    <i class="fa fa-share-alt" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @include('includes._we-provide-unlimited')

    @include('includes._services', ['useNativeCarousel' => $useNativeHomeCarousel])

    @if ($isDocumentEnglish)
        @include('includes._home-devices')
        @include('includes._testimonials', [
            'testimonials' => $documentTestimonials,
        ])
    @else
        @include('includes._testimonials')
        @unless ($isMobile)
            @include('includes._channels-carousel', ['useNativeCarousel' => $useNativeHomeCarousel])
        @endunless
    @endif

    {{-- FAQ Section --}}
    @if ($isDocumentEnglish)
        @include('includes._faq-section', [
            'faqItems' => $documentHome['faq']['items'],
            'faqTitle' => $documentHome['faq']['heading'],
        ])
        @include('includes._home-stats')
    @else
        @include('includes._faq-section')
    @endif

    {{-- Map embed (last section) --}}
    @include('includes._home-map')

    @include('includes._check-trail')


@stop

@push('schema')
    {{-- Organization + WebSite are emitted site-wide from includes/head.blade.php. --}}
    {!! jsonld(seo()->service(
        app()->getLocale() === 'en' ? __('messages.home_document.hero.heading') : 'IPTV Subscription Service',
        app()->getLocale() === 'en' ? __('messages.home_document.hero.text') : 'Premium IPTV with 12,000+ live channels, sports, movies and VOD in HD & 4K, compatible with every device, plus a free trial and 24/7 support.',
        url('/'),
    )) !!}
@endpush
