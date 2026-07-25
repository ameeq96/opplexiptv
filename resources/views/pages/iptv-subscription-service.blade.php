@extends('layouts.default')

@php
    $isDocumentEnglish = true;
    $isExactEnglishContent = app()->getLocale() === 'en';
    $documentPage = __('document_product.subscription');
@endphp

@section('title', $isDocumentEnglish ? $documentPage['hero']['heading'] : __('messages.iptv_subscription_service.title'))

@push('schema')
    {!! jsonld(seo()->service(
        $isDocumentEnglish ? $documentPage['hero']['heading'] : 'IPTV Subscription Service',
        $isDocumentEnglish ? implode(' ', $documentPage['hero']['paragraphs']) : 'Premium IPTV subscription service with live channels, sports, movies, series and 4K streaming, fast setup and a free trial.',
        route('iptv-subscription-service'),
        seo()->packageOffers('iptv'),
    )) !!}
@endpush

@push('styles')
    @php
        $iptvSubscriptionCss = @file_get_contents(public_path('css/iptv-service.css'));
        $iptvSubscriptionDocumentCss = $isDocumentEnglish
            ? @file_get_contents(public_path('css/document-product.css'))
            : false;
    @endphp
    @if ($iptvSubscriptionCss !== false)
        <style id="iptv-subscription-page-styles">{!! $iptvSubscriptionCss !!}</style>
    @else
        <link rel="stylesheet" href="{{ asset('css/iptv-service.css') }}">
    @endif
    @if ($isDocumentEnglish)
        @if ($iptvSubscriptionDocumentCss !== false)
            <style id="iptv-subscription-document-styles">{!! $iptvSubscriptionDocumentCss !!}</style>
        @else
            <link rel="stylesheet" href="{{ asset('css/document-product.css') }}?v={{ @filemtime(public_path('css/document-product.css')) ?: 1 }}">
        @endif
    @endif
@endpush

@section('content')
    @php
        $page = __('messages.iptv_subscription_service');
        $benefits = is_array($page['benefits'] ?? null) ? $page['benefits'] : [];
        $setupPoints = is_array($page['setup_points'] ?? null) ? $page['setup_points'] : [];
    @endphp

    <x-page-title
        :title="$isDocumentEnglish ? $documentPage['page_title'] : ($page['title'] ?? __('messages.iptv_subscription_service.title'))"
        :breadcrumbs="[
            ['url' => route('home'), 'label' => $page['breadcrumb']['home'] ?? __('messages.nav_home'), 'aria' => __('document_ui.shared.go_home')],
            ['label' => $page['breadcrumb']['current'] ?? __('messages.nav_iptv_subscription_service')],
        ]"
        background="images/background/9.webp"
        :rtl="$isRtl"
        aria-label="{{ __('document_ui.subscription.page_aria') }}"
    />

    @php
        $waTrial = 'https://wa.me/16393903194?text=' . urlencode(__('messages.whatsapp_trial'));
    @endphp

    @if ($isDocumentEnglish)
        <section class="ipts-hero document-product-page document-product-subscription" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
            aria-labelledby="iptv-subscription-service-title">
            <div class="auto-container">
                <div class="ipts-hero__head document-product-hero document-product-hero--compact">
                    <span class="ipts-eyebrow">{{ $documentPage['hero']['eyebrow'] }}</span>
                    <h1 id="iptv-subscription-service-title" class="ipts-hero__title">{{ $documentPage['hero']['heading'] }}</h1>
                    @foreach ($documentPage['hero']['paragraphs'] as $paragraph)
                        <p class="ipts-hero__text">{{ $paragraph }}</p>
                    @endforeach

                    <div class="ipts-cta">
                        <a href="{{ route('configure') }}" class="ipts-btn ipts-btn--primary">
                            {{ $documentPage['hero']['primary_cta'] }}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                        <a href="{{ $waTrial }}" target="_blank" rel="noopener"
                            class="ipts-btn ipts-btn--wa" data-trial data-wa-href="{{ $waTrial }}">
                            {{ $documentPage['hero']['secondary_cta'] }}
                        </a>
                    </div>
                </div>

                <div class="ipts-benefits" role="list">
                    @foreach ($documentPage['hero']['benefits'] as $benefit)
                        <article class="ipts-card" role="listitem">
                            <span class="ipts-card__icon {{ $benefit['icon'] }}" aria-hidden="true"></span>
                            <h3>{{ $benefit['title'] }}</h3>
                            <p>{{ $benefit['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @else
        <section class="ipts-hero {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
            aria-labelledby="iptv-subscription-service-title">
            <div class="auto-container">
                <div class="ipts-hero__head">
                    @if (!empty($page['intro_eyebrow']))
                        <span class="ipts-eyebrow">{{ $page['intro_eyebrow'] }}</span>
                    @endif
                    <h1 id="iptv-subscription-service-title" class="ipts-hero__title">{{ $page['heading'] ?? '' }}</h1>
                    <p class="ipts-hero__text">{{ $page['intro_text'] ?? '' }}</p>

                    <div class="ipts-cta">
                        <a href="{{ route('configure') }}" class="ipts-btn ipts-btn--primary">
                            {{ $page['primary_cta'] ?? __('messages.buy_now') }}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                        <a href="{{ $waTrial }}" target="_blank" rel="noopener"
                            class="ipts-btn ipts-btn--wa" data-trial data-wa-href="{{ $waTrial }}">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zM12.05 21.785h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884z"/></svg>
                            {{ $page['secondary_cta'] ?? __('messages.trial_button') }}
                        </a>
                    </div>
                </div>

                @if ($benefits)
                    <div class="ipts-benefits" role="list">
                        @foreach ($benefits as $benefit)
                            <article class="ipts-card" role="listitem">
                                <span class="ipts-card__icon {{ $benefit['icon'] ?? 'flaticon-8k' }}" aria-hidden="true"></span>
                                <h3>{{ $benefit['title'] ?? '' }}</h3>
                                <p>{{ $benefit['description'] ?? '' }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    @include('includes._best-packages')

    @if ($isDocumentEnglish)
        <section class="document-product-section document-product-subscription-steps {{ $isExactEnglishContent ? 'document-product-subscription-steps--exact-English' : '' }}" aria-labelledby="iptv-subscription-setup-title">
            <div class="auto-container">
                <header class="document-product-section__heading">
                    <span class="document-product-eyebrow">{{ __('document_ui.subscription.steps_eyebrow') }}</span>
                    <h2 id="iptv-subscription-setup-title">{{ $documentPage['steps']['heading'] }}</h2>
                </header>
                <ol class="document-product-process">
                    @foreach ($documentPage['steps']['items'] as $step)
                        <li>
                            <span class="document-product-process__number">{{ $isExactEnglishContent ? 'Step ' . $loop->iteration . ' |' : $loop->iteration }}</span>
                            <div>
                                <h3>{{ $step['title'] }}</h3>
                                <p>{{ $step['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <div class="document-product-inline-cta">
                    <p>{{ $documentPage['steps']['support'] }}</p>
                    <a class="document-product-button" href="{{ route('iptv-applications') }}">{{ $documentPage['steps']['cta'] }}</a>
                </div>
            </div>
        </section>
    @else
        <section class="ipts-setup {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
            aria-labelledby="iptv-subscription-setup-title">
            <div class="auto-container">
                <div class="ipts-setup__panel">
                    <div class="ipts-setup__intro">
                        <h2 id="iptv-subscription-setup-title" class="ipts-setup__title">{{ $page['setup_title'] ?? '' }}</h2>
                        <p class="ipts-setup__text">{{ $page['setup_text'] ?? '' }}</p>

                        @if (!empty($page['support_note']))
                            <div class="ipts-support">{!! $page['support_note'] !!}</div>
                        @endif

                        <div class="ipts-setup__cta">
                            <a href="{{ route('iptv-applications') }}" class="ipts-btn ipts-btn--primary">
                                {{ $page['apps_cta'] ?? __('messages.nav_iptv_apps') }}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </a>
                        </div>
                    </div>

                    @if ($setupPoints)
                        <ul class="ipts-checklist">
                            @foreach ($setupPoints as $point)
                                <li>
                                    <span class="tick" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                            stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    </span>
                                    <span>{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($isDocumentEnglish)
        <section class="document-product-section document-product-section--light" aria-labelledby="document-subscription-why-title">
            <div class="auto-container">
                <header class="document-product-section__heading">
                    <span class="document-product-eyebrow">{{ __('document_ui.subscription.why_eyebrow') }}</span>
                    <h2 id="document-subscription-why-title">{{ $documentPage['why']['heading'] }}</h2>
                </header>
                <div class="document-product-card-grid document-product-card-grid--four">
                    @foreach ($documentPage['why']['items'] as $item)
                        <article class="document-product-card">
                            <span class="document-product-card__number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="document-product-section" aria-labelledby="document-subscription-included-title">
            <div class="auto-container">
                <div class="document-product-split">
                    <header>
                        <span class="document-product-eyebrow">{{ __('document_ui.subscription.included_eyebrow') }}</span>
                        <h2 id="document-subscription-included-title">{{ $documentPage['included']['heading'] }}</h2>
                        <p>{{ $documentPage['included']['intro'] }}</p>
                    </header>
                    <ul class="document-product-check-grid">
                        @foreach ($documentPage['included']['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        <section class="document-product-section document-product-section--navy" aria-labelledby="document-subscription-devices-title">
            <div class="auto-container">
                <div class="document-product-split document-product-split--devices">
                    <div>
                        <span class="document-product-eyebrow document-product-eyebrow--inverse">{{ __('document_ui.shared.device_compatibility') }}</span>
                        <h2 id="document-subscription-devices-title">{{ $documentPage['devices']['heading'] }}</h2>
                        <p>{{ $documentPage['devices']['intro'] }}</p>
                        <ul class="document-product-check-grid">
                            @foreach ($documentPage['devices']['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <aside class="document-product-callout document-product-callout--inverse">
                        <p>{{ $documentPage['devices']['text'] }}</p>
                        <a class="document-product-button" href="{{ route('iptv-applications') }}">{{ $documentPage['devices']['cta'] }}</a>
                    </aside>
                </div>
            </div>
        </section>

        <section class="document-product-section document-product-comparison" aria-labelledby="document-subscription-comparison-title">
            <div class="auto-container">
                <header class="document-product-section__heading">
                    <span class="document-product-eyebrow">{{ __('document_ui.subscription.comparison_eyebrow') }}</span>
                    <h2 id="document-subscription-comparison-title">{{ $documentPage['comparison']['heading'] }}</h2>
                    <p>{{ $documentPage['comparison']['intro'] }}</p>
                </header>
                <dl class="document-product-comparison__list">
                    @foreach ($documentPage['comparison']['items'] as $item)
                        <div>
                            <dt>{{ $item['title'] }}</dt>
                            <dd>{{ $item['text'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>
    @else
        @include('includes._choose-us')
    @endif
    @include('includes._testimonials')

    @unless ($isMobile)
        <section class="clients-section ipts-channels" aria-label="{{ __('document_ui.subscription.brands_aria') }}">
            <div class="auto-container">
                <div id="iptv-subscription-channel-grid" class="ipts-channel-grid" role="list">
                    @foreach ($logos as $logo)
                        @php
                            $logoPath = is_array($logo) ? ($logo['image'] ?? '') : $logo;
                            $altText = is_array($logo) ? ($logo['alt'] ?? '') : '';
                            if (!$altText) {
                                $brandName = ucfirst(str_replace(['-', '_'], ' ', pathinfo($logoPath, PATHINFO_FILENAME)));
                                $altText = __('document_ui.subscription.client_logo_aria', ['name' => $brandName]);
                            }
                        @endphp
                        <div class="channel-showcase__card" role="listitem" aria-label="{{ $altText }}">
                            <div class="image-box">
                                <div class="wrapper-circle">
                                    <img src="{{ asset($logoPath) }}" alt="{{ $altText }}"
                                        width="100" height="100" loading="lazy" decoding="async">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endunless

    @if ($isDocumentEnglish)
        @include('includes._faq-section', [
            'faqItems' => $documentPage['faq']['items'],
            'faqTitle' => $documentPage['faq']['heading'],
        ])
    @else
        {{-- FAQ Section --}}
        @include('includes._faq-section')
    @endif

@stop

@section('script')
    <script id="iptv-subscription-native-shell">
        (function () {
            'use strict';

            function directChildByTag(parent, tagName) {
                if (!parent) return null;
                const expectedTag = tagName.toUpperCase();

                return Array.from(parent.children).find((child) => child.tagName === expectedTag) || null;
            }

            function initIptvSubscriptionScrollUi() {
                const header = document.querySelector('.main-header');
                const scrollButton = document.querySelector('.scroll-to-target');
                let scheduled = false;

                const update = () => {
                    const isPastHeader = window.scrollY >= 300;
                    if (header) header.classList.toggle('fixed-header', isPastHeader);
                    if (scrollButton) scrollButton.style.display = isPastHeader ? 'block' : 'none';
                    scheduled = false;
                };

                const scheduleUpdate = () => {
                    if (scheduled) return;
                    scheduled = true;
                    window.requestAnimationFrame(update);
                };

                window.addEventListener('scroll', scheduleUpdate, { passive: true });

                if (scrollButton) {
                    scrollButton.addEventListener('click', () => {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                }

                update();
            }

            function initIptvSubscriptionMobileMenu() {
                const source = document.querySelector('.main-header .main-menu .navigation');
                const target = document.querySelector('.mobile-menu .menu-outer');

                if (source && target && !target.querySelector('.navigation')) {
                    target.insertBefore(source.cloneNode(true), target.firstChild);
                }

                document.querySelectorAll('.mobile-menu .navigation li.dropdown').forEach((item) => {
                    const submenu = directChildByTag(item, 'ul');
                    if (!submenu || item.querySelector(':scope > .dropdown-btn')) return;

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'dropdown-btn';
                    button.setAttribute('aria-label', @json(__('document_ui.shared.menu_toggle')));
                    button.setAttribute('aria-expanded', 'false');
                    button.innerHTML = '<span class="fa fa-angle-down" aria-hidden="true"></span>';
                    item.appendChild(button);

                    const toggle = (event) => {
                        if (event) event.preventDefault();
                        const willOpen = submenu.style.display !== 'block';
                        submenu.style.display = willOpen ? 'block' : 'none';
                        button.classList.toggle('open', willOpen);
                        button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    };

                    button.addEventListener('click', toggle);

                    const link = directChildByTag(item, 'a');
                    if (link && (link.getAttribute('href') === '#' || link.getAttribute('href') === '')) {
                        link.addEventListener('click', toggle);
                    }
                });

                const openButton = document.querySelector('.mobile-nav-toggler');
                const backdrop = document.querySelector('.mobile-menu .menu-backdrop');
                const closeButton = document.querySelector('.mobile-menu .close-btn');
                const closeMenu = () => document.body.classList.remove('mobile-menu-visible');

                if (openButton) {
                    openButton.addEventListener('click', () => {
                        document.body.classList.add('mobile-menu-visible');
                    });
                }

                if (backdrop) backdrop.addEventListener('click', closeMenu);
                if (closeButton) closeButton.addEventListener('click', closeMenu);

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') closeMenu();
                });
            }

            function initIptvSubscriptionDropdowns() {
                const dropdowns = document.querySelectorAll('.main-header .main-menu .navigation > li.dropdown');

                const closeDropdowns = (except) => {
                    dropdowns.forEach((item) => {
                        if (item === except) return;
                        item.classList.remove('native-dropdown-open');
                        const submenu = directChildByTag(item, 'ul');
                        if (submenu) {
                            submenu.style.removeProperty('display');
                            submenu.style.removeProperty('transform');
                            submenu.style.removeProperty('opacity');
                            submenu.style.removeProperty('visibility');
                        }
                        const link = directChildByTag(item, 'a');
                        if (link) link.setAttribute('aria-expanded', 'false');
                    });
                };

                dropdowns.forEach((item) => {
                    const link = directChildByTag(item, 'a');
                    const submenu = directChildByTag(item, 'ul');
                    if (!link || !submenu || !['', '#'].includes(link.getAttribute('href') || '')) return;

                    link.addEventListener('click', (event) => {
                        event.preventDefault();
                        const willOpen = !item.classList.contains('native-dropdown-open');
                        closeDropdowns(item);
                        item.classList.toggle('native-dropdown-open', willOpen);
                        submenu.style.display = willOpen ? 'block' : '';
                        submenu.style.transform = willOpen ? 'scaleY(1)' : '';
                        submenu.style.opacity = willOpen ? '1' : '';
                        submenu.style.visibility = willOpen ? 'visible' : '';
                        link.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    });
                });

                document.addEventListener('click', (event) => {
                    if (!event.target.closest('.main-header .main-menu .navigation > li.dropdown')) {
                        closeDropdowns();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') closeDropdowns();
                });
            }

            function initIptvSubscriptionFaqs() {
                document.querySelectorAll('.accordion-box').forEach((accordion) => {
                    const buttons = accordion.querySelectorAll('.acc-btn');

                    buttons.forEach((button) => {
                        const item = button.closest('.accordion');
                        const content = button.nextElementSibling;
                        if (!item || !content) return;

                        const isInitiallyOpen = content.classList.contains('current');
                        button.tabIndex = 0;
                        button.setAttribute('role', 'button');
                        button.setAttribute('aria-expanded', isInitiallyOpen ? 'true' : 'false');
                        content.hidden = !isInitiallyOpen;

                        const openItem = () => {
                            if (button.classList.contains('active') && !content.hidden) return;

                            accordion.querySelectorAll('.accordion').forEach((otherItem) => {
                                const otherButton = otherItem.querySelector(':scope > .acc-btn');
                                const otherContent = otherButton ? otherButton.nextElementSibling : null;
                                otherItem.classList.remove('active-block');

                                if (otherButton) {
                                    otherButton.classList.remove('active');
                                    otherButton.setAttribute('aria-expanded', 'false');
                                }

                                if (otherContent) {
                                    otherContent.classList.remove('current');
                                    otherContent.hidden = true;
                                }
                            });

                            item.classList.add('active-block');
                            button.classList.add('active');
                            button.setAttribute('aria-expanded', 'true');
                            content.classList.add('current');
                            content.hidden = false;
                        };

                        button.addEventListener('click', openItem);
                        button.addEventListener('keydown', (event) => {
                            if (event.key !== 'Enter' && event.key !== ' ') return;
                            event.preventDefault();
                            openItem();
                        });
                    });
                });
            }

            function initIptvSubscriptionNativeShell() {
                initIptvSubscriptionScrollUi();
                initIptvSubscriptionMobileMenu();
                initIptvSubscriptionDropdowns();
                initIptvSubscriptionFaqs();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initIptvSubscriptionNativeShell, { once: true });
            } else {
                initIptvSubscriptionNativeShell();
            }
        })();
    </script>
@endsection
