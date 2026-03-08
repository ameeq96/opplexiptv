@extends('layouts.default')
@section('title', 'Shop')
@push('styles')
<style>
    .products-filter {
        display: inline-flex;
        background: #ffffff;
        border-radius: 999px;
        padding: 4px;
        gap: 4px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    .products-filter__btn {
        border: 0;
        border-radius: 999px;
        padding: 8px 16px;
        font-weight: 600;
        font-size: 13px;
        color: #4b4b4b;
        text-decoration: none !important;
        transition: all .2s ease;
    }
    .products-filter__btn.is-active {
        color: #fff;
        background: linear-gradient(90deg, #0454f7, #0a67ff);
    }
    .unified-card {
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 18px;
        border: 1px solid #edf0f5;
        overflow: hidden;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.08);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .unified-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 34px rgba(0, 0, 0, 0.13);
    }
    .unified-card__media {
        display: block;
        height: 270px;
        background: #f2f4f8;
    }
    .unified-card__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }
    .unified-card__body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 14px;
    }
    .unified-card__title {
        font-size: 18px;
        line-height: 1.3;
        margin-bottom: 6px;
        min-height: 70px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .unified-card__title a {
        color: #141414;
        text-decoration: none;
    }
    .unified-card__title a:hover {
        color: #0454f7;
    }
    .unified-card__price {
        font-size: 18px;
        font-weight: 700;
        color: #101828;
        margin-bottom: 10px;
    }
    .unified-badge {
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .2px;
    }
    .unified-badge--digital {
        color: #065f46;
        background: #d1fae5;
    }
    .unified-badge--affiliate {
        color: #374151;
        background: #e5e7eb;
    }
    .unified-action {
        width: 100%;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 12px;
    }
    .unified-action-wrap {
        margin-top: auto;
    }
</style>
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

    <section class="shop-section mt-5" style="background-image: url('{{ asset('images/background/4.webp') }}'); direction: {{ $isRtl ? 'rtl' : 'ltr' }};">
        <div class="auto-container">
            <div class="products-shell">
                <div class="sec-title mb-4" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <div class="separator"></div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <h1 class="h3 mb-0">Products</h1>
                        <div class="products-filter">
                            <a href="{{ route('shop', ['type' => 'all']) }}" class="products-filter__btn {{ ($selectedType ?? 'all') === 'all' ? 'is-active' : '' }}">All</a>
                            <a href="{{ route('shop', ['type' => 'affiliate']) }}" class="products-filter__btn {{ ($selectedType ?? 'all') === 'affiliate' ? 'is-active' : '' }}">Affiliate</a>
                            <a href="{{ route('shop', ['type' => 'digital']) }}" class="products-filter__btn {{ ($selectedType ?? 'all') === 'digital' ? 'is-active' : '' }}">Digital</a>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    @forelse($products as $p)
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <article class="unified-card h-100">
                                <a class="unified-card__media" href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                    @if(!empty($p['image']))
                                        <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}">
                                    @endif
                                </a>
                                <div class="unified-card__body">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <h3 class="unified-card__title">
                                            <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif>
                                                {{ $p['name'] }}
                                            </a>
                                        </h3>
                                        <span class="unified-badge {{ $p['type'] === 'digital' ? 'unified-badge--digital' : 'unified-badge--affiliate' }}">
                                            {{ ucfirst($p['type']) }}
                                        </span>
                                    </div>
                                    @if(!empty($p['price']))
                                        <div class="unified-card__price">{{ $p['currency'] }} {{ number_format((float) $p['price'], 2) }}</div>
                                    @endif
                                    <div class="unified-action-wrap">
                                        @if($p['type'] === 'digital' && !empty($p['buy_now_url']))
                                            <a href="{{ $p['buy_now_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary unified-action">Buy Now</a>
                                        @else
                                            <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif class="btn btn-outline-primary unified-action">Open Link</a>
                                        @endif
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
@endsection
