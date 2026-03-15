@extends('layouts.default')
@section('title', $productName . ' | Opplex IPTV')

@push('styles')
<style>
    .product-share-page {
        padding: 60px 0;
        background: linear-gradient(180deg, #f6f8fc 0%, #eef3fb 100%);
    }

    .product-share-card {
        max-width: 1080px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 28px;
        overflow: hidden;
        border: 1px solid #e8eef7;
        box-shadow: 0 28px 60px rgba(15, 23, 42, .10);
    }

    .product-share-media {
        background: linear-gradient(135deg, #f4f7fb 0%, #ecf1f8 100%);
        height: 100%;
        min-height: 420px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 28px;
    }

    .product-share-media img {
        width: 100%;
        max-width: 460px;
        border-radius: 24px;
        object-fit: cover;
        box-shadow: 0 20px 44px rgba(15, 23, 42, .14);
    }

    .product-share-body {
        padding: 42px 38px;
    }

    .product-share-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .2px;
        margin-bottom: 14px;
    }

    .product-share-title {
        font-size: 42px;
        line-height: 1.08;
        margin-bottom: 16px;
        color: #0f172a;
    }

    .product-share-text {
        font-size: 17px;
        line-height: 1.7;
        color: #475569;
        margin-bottom: 18px;
    }

    .product-share-price {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 24px;
    }

    .product-share-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .product-share-actions .btn {
        min-width: 180px;
        border-radius: 14px;
        padding: 12px 18px;
        font-weight: 700;
    }

    @media (max-width: 991px) {
        .product-share-title {
            font-size: 32px;
        }

        .product-share-media {
            min-height: 320px;
        }

        .product-share-body {
            padding: 28px 22px;
        }
    }
</style>
@endpush

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
                                    @if ($productType === 'affiliate') target="_blank" rel="nofollow sponsored noopener" @endif>
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
