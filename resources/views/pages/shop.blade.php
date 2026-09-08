@extends('layouts.default')
@php
    $isDocumentEnglish = true;
    $usesSynchronizedDocumentContent = in_array(app()->getLocale(), config('app.locales', ['en']), true);
    $documentPage = __('document_product.shop');
@endphp
@section('title', $isDocumentEnglish ? $documentPage['hero']['heading'] : 'Shop')

@push('schema')
    {!! jsonld(seo()->collectionPage(
        $isDocumentEnglish ? $documentPage['hero']['heading'] : 'Shop — Streaming Devices & TV Accessories',
        $isDocumentEnglish ? $documentPage['hero']['text'] : 'Curated streaming and TV gear: Android TV boxes, Fire TV, Roku, wall mounts and accessories.',
        route('shop'),
    )) !!}
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/shop.css') }}?v={{ @filemtime(public_path('css/shop.css')) ?: 1 }}">
    @if ($isDocumentEnglish)
        <link rel="stylesheet" href="{{ asset('css/document-product.css') }}?v={{ @filemtime(public_path('css/document-product.css')) ?: 1 }}">
    @endif
@endpush

@section('content')
    @if ($isDocumentEnglish)
        @php
            $productItems = collect(method_exists($products, 'items') ? $products->items() : $products);
            $deviceProducts = isset($affiliateProducts)
                ? collect($affiliateProducts)->values()
                : $productItems
                    ->filter(static fn ($product) => strtolower((string) data_get($product, 'type', 'affiliate')) === 'affiliate')
                    ->values();
            $digitalProducts = isset($digitalProducts)
                ? collect($digitalProducts)->values()
                : $productItems
                    ->filter(static fn ($product) => strtolower((string) data_get($product, 'type', 'affiliate')) === 'digital')
                    ->values();
            $shouldAddDocumentFallbacks = $usesSynchronizedDocumentContent
                && (!method_exists($products, 'currentPage') || $products->currentPage() === 1);

            $resolveAffiliateKey = static function ($product): ?string {
                $asin = strtoupper(trim((string) data_get($product, 'asin', '')));
                if ($asin !== '') {
                    return $asin;
                }

                $name = strtolower((string) data_get($product, 'name', ''));
                return match (true) {
                    str_contains($name, 'fire tv stick 4k max') => 'B0BP9SNVH9',
                    str_contains($name, 'roku streaming stick hd 2025') => 'B0DXXYS4BJ',
                    str_contains($name, 'mounting dream') && str_contains($name, 'md2380') => 'B00SFSU53G',
                    str_contains($name, 'android tv box') && str_contains($name, '4gb') => 'B08CRV62C4',
                    default => null,
                };
            };

            $resolveDigitalKey = static function ($product): string {
                $slug = trim((string) data_get($product, 'slug', ''));
                return $slug !== '' ? $slug : \Illuminate\Support\Str::slug((string) data_get($product, 'name', ''));
            };

            $affiliateFallbacks = collect($documentPage['product_descriptions']['affiliate'])
                ->map(static function (array $copy, string $asin): array {
                    return [
                        'id' => 'document-' . $asin,
                        'type' => 'affiliate',
                        'identifier' => $asin,
                        'asin' => $asin,
                        'name' => $copy['label'],
                        'image' => asset('images/shop/' . $asin . '.webp'),
                        'url' => 'https://www.amazon.com/dp/' . $asin,
                        'target' => '_blank',
                        'rel' => 'nofollow sponsored noopener',
                    ];
                });
            $existingAffiliateKeys = $deviceProducts
                ->map($resolveAffiliateKey)
                ->filter()
                ->values();
            if ($shouldAddDocumentFallbacks) {
                $deviceProducts = $deviceProducts
                    ->concat($affiliateFallbacks->reject(
                        static fn (array $product): bool => $existingAffiliateKeys->contains($product['asin'])
                    ))
                    ->values();
            }

            $digitalNames = [
                'netflix' => 'Netflix',
                'prime-video' => 'Prime Video',
                'hbo-max-premium' => 'HBO Max Premium',
                'nordvpn' => 'NordVPN',
            ];
            $digitalFallbacks = collect($documentPage['product_descriptions']['digital'])
                ->map(static function (array $copy, string $slug) use ($digitalNames): array {
                    $name = $digitalNames[$slug] ?? \Illuminate\Support\Str::headline($slug);
                    $price = trim((string) ($copy['price_label'] ?? ''), "(): \t\n\r\0\x0B");
                    preg_match('/\d+(?:\.\d+)?/', $price, $priceMatch);
                    $url = 'https://wa.me/16393903194?text=' . rawurlencode(
                        __('document_ui.shop.purchase_message', [
                            'product' => $name,
                            'price' => $price,
                        ])
                    );

                    return [
                        'id' => 'document-' . $slug,
                        'type' => 'digital',
                        'identifier' => $slug,
                        'slug' => $slug,
                        'name' => $name,
                        'price' => isset($priceMatch[0]) ? (float) $priceMatch[0] : 0,
                        'currency' => 'USD',
                        'image' => asset('images/digital-products/' . $slug . '.webp'),
                        'url' => $url,
                        'buy_now_url' => $url,
                        'target' => '_blank',
                        'rel' => 'noopener noreferrer',
                    ];
                });
            $existingDigitalKeys = $digitalProducts
                ->map($resolveDigitalKey)
                ->filter()
                ->values();
            if ($shouldAddDocumentFallbacks) {
                $digitalProducts = $digitalProducts
                    ->concat($digitalFallbacks->reject(
                        static fn (array $product): bool => $existingDigitalKeys->contains($product['slug'])
                    ))
                    ->values();
            }
        @endphp

        <x-page-title
            :title="$documentPage['page_title']"
            :breadcrumbs="[
                ['url' => route('home'), 'label' => __('messages.nav_home')],
                ['label' => $documentPage['page_title']],
            ]"
            background="images/background/10.webp"
            :rtl="$isRtl"
            :aria-label="__('document_ui.shop.page_aria')"
        />

        <main class="document-product-page document-product-shop" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
            <section class="document-product-hero document-product-shop-hero" aria-labelledby="document-shop-title">
                <div class="auto-container">
                    <span class="document-product-eyebrow">{{ $documentPage['hero']['eyebrow'] }}</span>
                    <h1 id="document-shop-title">{{ $documentPage['hero']['heading'] }}</h1>
                    <p>{{ $documentPage['hero']['text'] }}</p>
                    <p class="document-product-support">
                        <a href="{{ route('iptv-subscription-service') }}">{{ $documentPage['hero']['note'] }}</a>
                    </p>
                    <div class="document-product-actions">
                        <a class="document-product-button" href="#devices">{{ $documentPage['hero']['devices'] }}</a>
                        <a class="document-product-button document-product-button--secondary" href="#digital-products">{{ $documentPage['hero']['digital'] }}</a>
                        <a class="document-product-button document-product-button--outline" href="{{ route('iptv-subscription-service') }}">{{ $documentPage['hero']['plans'] }}</a>
                    </div>
                </div>
            </section>

            @foreach ([
                ['id' => 'devices', 'type' => 'affiliate', 'items' => $deviceProducts],
                ['id' => 'digital-products', 'type' => 'digital', 'items' => $digitalProducts],
            ] as $group)
                @php
                    $isDigitalGroup = $group['type'] === 'digital';
                    $headingKey = $isDigitalGroup ? 'digital' : 'devices';
                @endphp
                <section id="{{ $group['id'] }}" class="shopx document-product-shop-products {{ $isDigitalGroup ? 'document-product-shop-products--digital' : '' }}"
                    aria-labelledby="document-shop-{{ $group['id'] }}-title">
                    <div class="auto-container">
                        <header class="document-product-section__heading">
                            <span class="document-product-eyebrow">{{ $isDigitalGroup ? __('document_ui.shop.delivered_directly') : __('document_ui.shop.affiliate_picks') }}</span>
                            <h2 id="document-shop-{{ $group['id'] }}-title">{{ $documentPage['section_headings'][$headingKey] }}</h2>
                            <p>{{ $documentPage['section_headings'][$headingKey . '_intro'] }}</p>
                        </header>

                        <div class="row g-4">
                            @forelse ($group['items'] as $product)
                                @php
                                    $productType = strtolower((string) data_get($product, 'type', 'affiliate'));
                                    $name = (string) data_get($product, 'name', '');
                                    $productUrl = (string) data_get($product, 'url', '#');
                                    $actionUrl = $productType === 'digital'
                                        ? ((string) data_get($product, 'buy_now_url', '') ?: $productUrl)
                                        : $productUrl;
                                    $target = (string) data_get($product, 'target', '');
                                    $rel = (string) data_get($product, 'rel', '');
                                    $copyKey = $productType === 'digital'
                                        ? $resolveDigitalKey($product)
                                        : $resolveAffiliateKey($product);
                                    $productCopy = $copyKey
                                        ? data_get($documentPage, "product_descriptions.{$productType}.{$copyKey}", [])
                                        : [];
                                    $displayName = $usesSynchronizedDocumentContent
                                        ? ($productType === 'digital'
                                            ? ($digitalNames[$copyKey] ?? $name)
                                            : ((string) data_get($productCopy, 'label', $name)))
                                        : $name;
                                @endphp
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                    <article class="unified-card document-product-shop-card h-100"
                                        @if ($productType === 'digital')
                                            data-whatsapp-package="{{ $displayName }}"
                                            data-whatsapp-value="{{ number_format((float) data_get($product, 'price', 0), 2, '.', '') }}"
                                            data-whatsapp-currency="{{ data_get($product, 'currency', 'USD') ?: 'USD' }}"
                                            data-whatsapp-intent="purchase" data-whatsapp-placement="shop_digital_product"
                                        @endif>
                                        <a class="unified-card__media" href="{{ $productUrl }}"
                                            @if ($target !== '') target="{{ $target }}" @endif
                                            @if ($rel !== '') rel="{{ $rel }}" @endif>
                                            @if (data_get($product, 'image'))
                                                <img src="{{ data_get($product, 'image') }}" alt="{{ $displayName }}" loading="lazy" decoding="async">
                                            @endif
                                            <span class="document-product-shop-card__badge">{{ $productType === 'digital' ? __('document_ui.shop.digital_badge') : __('document_ui.shop.amazon_badge') }}</span>
                                        </a>
                                        <div class="unified-card__body">
                                            <h3 class="unified-card__title">
                                                <a href="{{ $productUrl }}"
                                                    @if ($target !== '') target="{{ $target }}" @endif
                                                    @if ($rel !== '') rel="{{ $rel }}" @endif>{{ $displayName }}</a>
                                            </h3>

                                            @if (!$usesSynchronizedDocumentContent && !empty($productCopy['label']))
                                                <p class="document-product-shop-card__label">{{ $productCopy['label'] }}</p>
                                            @endif

                                            @if (!empty($productCopy['price_label']))
                                                <div class="unified-card__price">{{ $productCopy['price_label'] }}</div>
                                            @elseif (data_get($product, 'price') !== null)
                                                <div class="unified-card__price">{{ data_get($product, 'currency') }} {{ number_format((float) data_get($product, 'price'), 2) }}</div>
                                            @endif

                                            @if (!empty($productCopy['text']))
                                                <p class="document-product-shop-card__description">{{ $productCopy['text'] }}</p>
                                            @endif

                                            <div class="unified-action-wrap">
                                                <div class="unified-actions">
                                                    <a href="{{ $actionUrl }}"
                                                        @if ($target !== '' || $productType === 'digital') target="_blank" @endif
                                                        @if ($rel !== '') rel="{{ $rel }}" @elseif ($productType === 'digital') rel="noopener" @endif
                                                        class="unified-action">
                                                        {{ $productType === 'digital' ? __('document_ui.shop.buy_now') : __('document_ui.shop.view_amazon') }}
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8"/></svg>
                                                    </a>
                                                    <button type="button"
                                                        class="unified-share"
                                                        aria-label="{{ __('document_ui.shared.share', ['name' => $displayName]) }}"
                                                        data-share-url="{{ data_get($product, 'share_url', $productUrl) }}"
                                                        data-share-title="{{ $displayName }}"
                                                        data-share-text="{{ data_get($product, 'share_text', __('document_ui.shop.share_message', ['name' => $displayName])) }}">
                                                        <i class="fa fa-share-alt" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="document-product-empty">{{ $isDigitalGroup ? __('document_ui.shop.empty_digital') : __('document_ui.shop.empty_devices') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            @endforeach

            @if (method_exists($products, 'links'))
                <div class="document-product-pagination">
                    @include('includes._pagination', ['paginator' => $products, 'isRtl' => $isRtl])
                </div>
            @endif

            <section class="document-product-section document-product-section--light" aria-labelledby="document-shop-guide-title">
                <div class="auto-container">
                    <header class="document-product-section__heading">
                        <span class="document-product-eyebrow">{{ __('document_ui.shop.guide_eyebrow') }}</span>
                        <h2 id="document-shop-guide-title">{{ $documentPage['guide']['heading'] }}</h2>
                    </header>
                    <div class="document-product-card-grid document-product-card-grid--guide">
                        @foreach ($documentPage['guide']['items'] as $item)
                            <article class="document-product-card">
                                <span class="document-product-card__number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['text'] }}</p>
                            </article>
                        @endforeach
                    </div>
                    <p class="document-product-support">{{ $documentPage['guide']['support'] }}</p>
                </div>
            </section>

            <section class="document-product-section document-product-section--navy" aria-labelledby="document-shop-digital-orders-title">
                <div class="auto-container">
                    <div class="document-product-split">
                        <div>
                            <span class="document-product-eyebrow document-product-eyebrow--inverse">{{ __('document_ui.shop.ordering_eyebrow') }}</span>
                            <h2 id="document-shop-digital-orders-title">{{ $documentPage['digital_orders']['heading'] }}</h2>
                            <p>{{ $documentPage['digital_orders']['intro'] }}</p>
                            <ol class="document-product-step-list">
                                @foreach ($documentPage['digital_orders']['steps'] as $step)
                                    <li><span>{{ $loop->iteration }}</span><p>{{ $step }}</p></li>
                                @endforeach
                            </ol>
                        </div>
                        <aside class="document-product-callout document-product-callout--inverse">
                            <p>{{ $documentPage['digital_orders']['timing'] }}</p>
                            <p>{{ $documentPage['digital_orders']['delivery'] }}</p>
                        </aside>
                    </div>
                </div>
            </section>
        </main>

        @include('includes._faq-section', [
            'faqItems' => $documentPage['faq']['items'],
            'faqTitle' => $documentPage['faq']['heading'],
        ])
    @else
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
    @endif
@endsection
