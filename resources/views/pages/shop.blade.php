@extends('layouts.default')
@section('title', 'Shop')

@push('schema')
    {!! jsonld(seo()->collectionPage(
        'Shop — Streaming Devices & TV Accessories',
        'Curated streaming and TV gear: Android TV boxes, Fire TV, Roku, wall mounts and accessories.',
        route('shop'),
    )) !!}
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/shop.css') }}?v={{ @filemtime(public_path('css/shop.css')) ?: 1 }}">
@endpush

@section('content')
    <x-page-title
        :title="'Shop'"
        :breadcrumbs="[
            ['url' => '/', 'label' => __('messages.faq.breadcrumb.home') ],
            ['label' => 'Shop'],
        ]"
        background="images/background/10.webp"
        :rtl="$isRtl"
        aria-label="Shop Page"
    />

    <section class="shopx {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="auto-container">
            <div class="products-shell">
                <div class="shopx__head">
                    <div class="shopx__bar" aria-hidden="true"></div>
                    <h1 class="shopx__title">Streaming Devices &amp; TV Accessories</h1>
                    <p class="shopx__sub">Hand-picked Android TV boxes, Fire TV, Roku, mounts and accessories for the best IPTV setup.</p>
                </div>

                <div class="row g-4">
                    @forelse($products as $p)
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <article class="unified-card h-100">
                                <a class="unified-card__media" href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                    @if(!empty($p['image']))
                                        <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}" loading="lazy" decoding="async">
                                    @endif
                                </a>
                                <div class="unified-card__body">
                                    <h3 class="unified-card__title">
                                        <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                            {{ $p['name'] }}
                                        </a>
                                    </h3>
                                    @if(!empty($p['price']))
                                        <div class="unified-card__price">{{ $p['currency'] }} {{ number_format((float) $p['price'], 2) }}</div>
                                    @endif
                                    <div class="unified-action-wrap">
                                        <div class="unified-actions">
                                            <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif
                                                class="unified-action">
                                                View Product
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8"/></svg>
                                            </a>
                                            <button type="button"
                                                class="unified-share"
                                                aria-label="Share {{ $p['name'] }}"
                                                data-share-url="{{ $p['share_url'] ?? $p['url'] }}"
                                                data-share-title="{{ $p['name'] }}"
                                                data-share-text="{{ $p['share_text'] ?? ('Check out ' . $p['name']) }}">
                                                <i class="fa fa-share-alt" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info">No products found.</div>
                        </div>
                    @endforelse
                </div>

                @if (method_exists($products, 'links'))
                    <div class="mt-4 mb-2" style="display:flex; justify-content:center;">
                        @include('includes._pagination', ['paginator' => $products, 'isRtl' => $isRtl])
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    @include('includes._faq-section')
@endsection
