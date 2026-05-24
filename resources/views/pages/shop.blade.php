@extends('layouts.default')
@section('title', 'Shop')

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
                                        <div class="unified-actions">
                                        @if($p['type'] === 'digital' && !empty($p['buy_now_url']))
                                            <a href="{{ $p['buy_now_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary unified-action">Buy Now</a>
                                        @else
                                            <a href="{{ $p['url'] }}" @if(!empty($p['target'])) target="{{ $p['target'] }}" rel="{{ $p['rel'] }}" @endif class="btn btn-outline-primary unified-action">Open Link</a>
                                        @endif
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
@endsection

