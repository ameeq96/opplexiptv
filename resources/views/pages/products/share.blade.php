@extends('layouts.default')
@section('title', $productName . ' | Opplex IPTV')

@section('content')
    <section class="product-share-page" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="auto-container">
            <div class="product-share-card">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-6">
                        <div class="product-share-media">
                            <img src="{{ $productImage }}" alt="{{ $productName }}" loading="eager">
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
                                    Share Product
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

