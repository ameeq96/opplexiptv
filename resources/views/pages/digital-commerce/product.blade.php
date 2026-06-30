@extends('layouts.default')
@section('title', $product->title)

@php
    $isRtl = in_array(app()->getLocale(), ['ar', 'ur'], true);

    $priceNum = (float) $product->price;
    $compare = (float) ($product->compare_price ?? 0);
    $hasDiscount = $compare > $priceNum && $priceNum > 0;
    $savePct = $hasDiscount ? (int) round((($compare - $priceNum) / $compare) * 100) : 0;
    $cur = strtoupper((string) ($product->currency ?: 'USD'));
    $isUsd = $cur === 'USD';

    $categoryName = $product->category?->name;
    $deliveryType = $product->delivery_type ? ucfirst($product->delivery_type) : 'Digital item';
    $fullDescription = trim((string) ($product->full_description ?? ''));

    $waText = 'Hi, I want to buy ' . $product->title . ' ($' . number_format($priceNum, 2) . ').';
    $waUrl = 'https://wa.me/16393903194?text=' . rawurlencode($waText);

    $chev = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/digital-product.css') }}">
@endpush

@section('jsonld')
<script type="application/ld+json">
    {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@section('content')
<section class="dproduct {{ $isRtl ? 'rtl' : '' }}">
    <div class="auto-container">

        <nav class="dp-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            {!! $chev !!}
            <a href="{{ route('digital.shop') }}">Digital Shop</a>
            {!! $chev !!}
            <span class="current">{{ $product->title }}</span>
        </nav>

        <div class="dp-grid">

            {{-- Gallery --}}
            <div class="dp-gallery-col">
                <div class="dp-gallery">
                    <div class="dp-badges">
                        @if ($hasDiscount)
                            <span class="dp-badge dp-badge--sale">Save {{ $savePct }}%</span>
                        @endif
                        @if ($categoryName)
                            <span class="dp-badge dp-badge--cat">{{ $categoryName }}</span>
                        @endif
                    </div>
                    <div class="dp-gallery__frame">
                        <img src="{{ $productImage }}" alt="{{ $product->title }}" decoding="async">
                    </div>
                </div>

                <div class="dp-assure-strip">
                    <div class="item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8z"/></svg>
                        Instant delivery
                    </div>
                    <div class="item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg>
                        Global access
                    </div>
                    <div class="item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.5 8.5 0 0 1-12.2 7.7L3 21l1.8-5.8A8.5 8.5 0 1 1 21 11.5z"/></svg>
                        24/7 support
                    </div>
                </div>
            </div>

            {{-- Info --}}
            <div class="dp-info">
                @if ($categoryName)
                    <span class="dp-eyebrow">{{ $categoryName }}</span>
                @endif

                <h1 class="dp-title">{{ $product->title }}</h1>

                <span class="dp-stock"><span class="dot"></span> In stock · Ready for instant delivery</span>

                <div class="dp-price">
                    <span class="dp-price__now">
                        @if ($isUsd)<span class="cur">$</span>{{ number_format($priceNum, 2) }}@else<span class="cur">{{ $cur }}</span> {{ number_format($priceNum, 2) }}@endif
                    </span>
                    @if ($hasDiscount)
                        <span class="dp-price__was">@if ($isUsd)${{ number_format($compare, 2) }}@else{{ $cur }} {{ number_format($compare, 2) }}@endif</span>
                        <span class="dp-price__save">You save {{ $savePct }}%</span>
                    @endif
                </div>

                @if ($productDescription)
                    <p class="dp-desc">{{ $productDescription }}</p>
                @endif

                <ul class="dp-specs">
                    @if ($categoryName)
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 11 3.8a2 2 0 0 0-1.4-.6H4a1 1 0 0 0-1 1v5.6a2 2 0 0 0 .6 1.4l9.6 9.6a2 2 0 0 0 2.8 0l4.6-4.6a2 2 0 0 0 0-2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>
                            <span class="meta-pair"><span class="k">Category</span><span class="v">{{ $categoryName }}</span></span>
                        </li>
                    @endif
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.5 12.5 8-8M16 7l2 2M14 9l2 2"/></svg>
                        <span class="meta-pair"><span class="k">Type</span><span class="v">{{ $deliveryType }}</span></span>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8z"/></svg>
                        <span class="meta-pair"><span class="k">Delivery</span><span class="v">Instant digital</span></span>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg>
                        <span class="meta-pair"><span class="k">Access</span><span class="v">Worldwide</span></span>
                    </li>
                </ul>

                <div class="dp-cta">
                    <a class="dp-btn dp-btn--buy" href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zM12.05 21.785h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884z"/></svg>
                        Buy Now on WhatsApp
                    </a>
                    <a class="dp-btn dp-btn--ghost" href="{{ route('digital.shop') }}">All digital products</a>
                </div>

                <div class="dp-reassure">
                    <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Secure checkout</span>
                    <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> <a href="{{ route('refund-policy') }}">Refund policy</a></span>
                    <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.5 8.5 0 0 1-12.2 7.7L3 21l1.8-5.8A8.5 8.5 0 1 1 21 11.5z"/></svg> 24/7 WhatsApp support</span>
                </div>
            </div>
        </div>

        {{-- Full description --}}
        @if ($fullDescription)
            <div class="dp-section">
                <div class="dp-section__head"><span class="dp-eyebrow">Details</span><h2>About {{ $product->title }}</h2></div>
                <div class="dp-prose">{!! $fullDescription !!}</div>
            </div>
        @endif

        {{-- How delivery works --}}
        <div class="dp-section">
            <div class="dp-section__head"><span class="dp-eyebrow">Simple &amp; fast</span><h2>How delivery works</h2></div>
            <div class="dp-steps">
                <div class="dp-step">
                    <span class="dp-step__n">1</span>
                    <h3>Place your order</h3>
                    <p>Click <strong>Buy Now</strong> and confirm the product on WhatsApp with our team.</p>
                </div>
                <div class="dp-step">
                    <span class="dp-step__n">2</span>
                    <h3>Pay securely</h3>
                    <p>Complete payment safely using your preferred method. No physical shipping involved.</p>
                </div>
                <div class="dp-step">
                    <span class="dp-step__n">3</span>
                    <h3>Get it instantly</h3>
                    <p>Receive your {{ strtolower($deliveryType) }} details by WhatsApp or email — usually within minutes.</p>
                </div>
            </div>
        </div>

        {{-- Related --}}
        @if (isset($related) && $related->count())
            <div class="dp-section">
                <div class="dp-section__head"><span class="dp-eyebrow">More from the shop</span><h2>You may also like</h2></div>
                <div class="dp-related">
                    @foreach ($related as $rel)
                        @php
                            $relImg = $rel->image
                                ? asset('images/digital-products/' . $rel->image)
                                : asset('images/placeholder.webp');
                            $relCur = strtoupper((string) ($rel->currency ?: 'USD'));
                        @endphp
                        <article class="dp-card">
                            <a class="dp-card__media" href="{{ route('digital.product.show', $rel->slug) }}" aria-label="{{ $rel->title }}">
                                <img src="{{ $relImg }}" alt="{{ $rel->title }}" loading="lazy" decoding="async">
                            </a>
                            <div class="dp-card__body">
                                <span class="dp-card__cat">{{ $rel->category?->name ?? 'Digital' }}</span>
                                <h3 class="dp-card__title"><a href="{{ route('digital.product.show', $rel->slug) }}">{{ $rel->title }}</a></h3>
                                <div class="dp-card__foot">
                                    <span class="dp-card__price">
                                        @if ($relCur === 'USD')<span class="cur">$</span>{{ number_format((float) $rel->price, 2) }}@else<span class="cur">{{ $relCur }}</span> {{ number_format((float) $rel->price, 2) }}@endif
                                    </span>
                                    <a class="dp-card__go" href="{{ route('digital.product.show', $rel->slug) }}">
                                        View
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</section>
@endsection
