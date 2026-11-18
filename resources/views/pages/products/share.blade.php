@extends('layouts.default')
@section('title', $productName . ' | Opplex IPTV')

@section('content')
    <section class="product-share-page" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="auto-container">
            <div class="product-share-card">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-6">
                        <div class="product-share-media">
                            @if ($productType === 'affiliate')
                                <svg viewBox="0 0 320 200" width="320" height="200" aria-hidden="true" focusable="false">
                                    <rect x="40" y="20" width="240" height="140" rx="12" fill="#132e44" />
                                    <rect x="52" y="32" width="216" height="116" rx="5" fill="#86d8cd" />
                                    <path d="M160 160V184M120 184H200" stroke="#132e44" stroke-width="10" stroke-linecap="round" />
                                </svg>
                            @else
                                <x-product-picture :image="$productImageSources ?? $productImage"
                                    :alt="$productName"
                                    loading="eager"
                                    fetchpriority="high"
                                    sizes="(max-width: 991px) calc(100vw - 48px), 50vw" />
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="product-share-body" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                            <span class="product-share-badge">{{ $productTypeLabel }}</span>
                            <h1 class="product-share-title">{{ $productName }}</h1>
                            <p class="product-share-text">{{ $productDescription }}</p>
                            @if (!empty($productPrice))
                                <div class="product-share-price">{{ $productPrice }}</div>
                            @endif
                            <div class="product-share-actions">
                                <a href="{{ $productActionUrl }}"
                                    class="btn btn-primary"
                                    target="_blank"
                                    rel="noopener noreferrer">
                                    {{ $productActionLabel }}
                                </a>
                                <button type="button"
                                    class="btn btn-outline-primary"
                                    data-share-url="{{ url()->current() }}"
                                    data-share-title="{{ $productName }}"
                                    data-share-text="{{ $productDescription }}">
                                    {{ __('interface.product.share') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
