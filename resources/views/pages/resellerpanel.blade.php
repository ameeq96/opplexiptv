@extends('layouts.default')
@section('title', app()->getLocale() === 'en' ? __('document_commerce.reseller.page_title') : __('messages.reseller.panel.title'))

@push('schema')
    {!! jsonld(seo()->service(
        'IPTV Reseller Panel',
        'Become an IPTV reseller with Opplex IPTV: panel access, credit-based plans, instant activation and 24/7 support across Europe and the USA.',
        route('reseller-panel'),
        seo()->packageOffers('reseller'),
        'IPTV Reseller Service',
    )) !!}
@endpush

@push('styles')
    @php
        $isDocumentEnglish = app()->getLocale() === 'en';
        $resellerPageCss = $isDocumentEnglish
            ? false
            : @file_get_contents(public_path('css/about.css'));
        $resellerDocumentCss = $isDocumentEnglish
            ? @file_get_contents(public_path('css/document-commerce.css'))
            : false;
    @endphp
    @if (app()->getLocale() === 'en')
        @if ($resellerDocumentCss !== false)
            <style id="reseller-panel-document-styles">{!! $resellerDocumentCss !!}</style>
        @else
            <link rel="stylesheet" href="{{ asset('css/document-commerce.css') }}?v={{ @filemtime(public_path('css/document-commerce.css')) ?: 1 }}">
        @endif
    @elseif ($resellerPageCss !== false)
        <style id="reseller-panel-page-styles">{!! $resellerPageCss !!}</style>
    @else
        <link rel="stylesheet" href="{{ asset('css/about.css') }}?v={{ @filemtime(public_path('css/about.css')) ?: 1 }}">
    @endif
@endpush

@section('content')
    @if (app()->getLocale() === 'en')
        @php
            $page = __('document_commerce.reseller');
            $resellerWhatsAppUrl = 'https://wa.me/16393903194?text=' . urlencode($page['hero']['whatsapp_message']);
        @endphp

        <x-page-title
            :title="$page['page_title']"
            :breadcrumbs="[
                ['url' => route('home'), 'label' => __('messages.nav_home'), 'aria' => 'Go to Home'],
                ['label' => $page['page_title']],
            ]"
            background="images/background/7.webp"
            :rtl="$isRtl"
            aria-label="Opplex IPTV reseller panel page"
        />

        <main class="doc-commerce doc-commerce--reseller">
            <section class="dc-hero dc-hero--reseller" aria-labelledby="reseller-document-title">
                <div class="auto-container">
                    <div class="dc-hero__content">
                        <span class="dc-eyebrow">{{ $page['hero']['eyebrow'] }}</span>
                        <h1 id="reseller-document-title" class="dc-title">{{ $page['hero']['heading'] }}</h1>
                        <div class="dc-prose dc-prose--lead">
                            @foreach ($page['hero']['paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                        <div class="dc-actions">
                            <a class="dc-button dc-button--primary" href="#pricing-section">
                                {{ $page['hero']['primary_cta'] }}
                            </a>
                            <a class="dc-button dc-button--secondary" href="{{ $resellerWhatsAppUrl }}"
                                target="_blank" rel="noopener">
                                {{ $page['hero']['secondary_cta'] }}
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="dc-section dc-section--soft" aria-labelledby="reseller-credits-title">
                <div class="auto-container">
                    <div class="dc-section__header">
                        <span class="dc-eyebrow">How Credits Work</span>
                        <h2 id="reseller-credits-title">{{ $page['credits']['heading'] }}</h2>
                    </div>
                    <div class="dc-prose dc-prose--centered">
                        @foreach ($page['credits']['paragraphs'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                    <p class="dc-conversion-label">{{ $page['credits']['label'] }}</p>
                    <dl class="dc-conversion-grid">
                        @foreach ($page['credits']['items'] as $item)
                            <div class="dc-conversion-card">
                                <dt>{{ $item['value'] }}</dt>
                                <dd><span aria-hidden="true">=</span> {{ $item['label'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <p class="dc-section-note">{{ $page['credits']['note'] }}</p>
                </div>
            </section>

            @include('includes._best-packages', ['initialMode' => 'reseller'])

            <section class="dc-section" aria-labelledby="reseller-account-title">
                <div class="auto-container">
                    <div class="dc-section__header">
                        <span class="dc-eyebrow">Your Reseller Account</span>
                        <h2 id="reseller-account-title">{{ $page['account']['heading'] }}</h2>
                    </div>
                    <div class="dc-content-grid dc-content-grid--three" role="list">
                        @foreach ($page['account']['items'] as $item)
                            <article class="dc-content-card" role="listitem">
                                <span class="dc-content-card__check fa fa-check" aria-hidden="true"></span>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            @include('includes._we-provide-unlimited')

            <section class="dc-section dc-section--soft" aria-labelledby="reseller-audience-title">
                <div class="auto-container">
                    <div class="dc-section__header">
                        <span class="dc-eyebrow">Who Should Resell</span>
                        <h2 id="reseller-audience-title">{{ $page['audience']['heading'] }}</h2>
                        <p>{{ $page['audience']['intro'] }}</p>
                    </div>
                    <div class="dc-content-grid dc-content-grid--four" role="list">
                        @foreach ($page['audience']['items'] as $item)
                            <article class="dc-content-card dc-content-card--numbered" role="listitem">
                                <span class="dc-content-card__number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            @include('includes._testimonials')

            @unless ($isMobile)
                @include('includes._channels-carousel', ['useNativeCarousel' => true])
            @endunless

            @include('includes._faq-section', [
                'faqItems' => $page['faq']['items'],
                'faqTitle' => $page['faq']['heading'],
            ])
        </main>
    @else
        <x-page-title
            :title="__('messages.reseller.panel.title')"
            :breadcrumbs="[
                ['url' => '/', 'label' => __('messages.app.breadcrumb.home'), 'aria' => 'Go to Home'],
                ['label' => __('messages.reseller.panel.title')],
            ]"
            background="images/background/7.webp"
            :rtl="$isRtl"
            aria-label="Opplex IPTV Reseller Panel Page Title"
        />

        {{-- Pricing --}}
        @include('includes._best-packages')

        {{-- We provide unlimited --}}
        @include('includes._we-provide-unlimited')

        {{-- Why choose us / services --}}
        <section class="abtx {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
                 aria-label="Why Choose Opplex IPTV - HD Streaming, Flexible Subscriptions, Easy Setup, Reliable Service">
            <div class="auto-container">
                <div class="abtx__head">
                    <div class="abtx__titles">
                        <div class="abtx__bar" aria-hidden="true"></div>
                        <h1 class="abtx__title">{{ __('messages.reasons.title') }}</h1>
                    </div>

                    <a href="{{ route('packages', ['direct' => 1]) }}" class="abtx__cta" aria-label="{{ __('messages.view.services') }}">
                        {{ __('messages.view.services') }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>

                <div class="abtx-grid" role="list">
                    @foreach ($seoServices as $service)
                        <a class="abtx-card" role="listitem" href="{{ route('packages', ['direct' => 1]) }}"
                            aria-label="{{ $service['title'] ?? '' }}">
                            <span @class(['abtx-card__icon', $service['icon'] ?? '']) aria-hidden="true"></span>
                            <h3 class="abtx-card__title">{{ $service['title'] ?? '' }}</h3>
                            <p class="abtx-card__text">{{ $service['description'] ?? '' }}</p>
                            <span class="abtx-card__arrow" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                    stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Testimonials --}}
        @include('includes._testimonials')

        {{-- Channels (desktop only) --}}
        @unless ($isMobile)
            @include('includes._channels-carousel', ['useNativeCarousel' => true])
        @endunless


        {{-- FAQ Section --}}
        @include('includes._faq-section')
    @endif
@stop

@section('script')
    <script id="reseller-panel-native-shell">
        (function () {
            'use strict';

            function directChildByTag(parent, tagName) {
                if (!parent) return null;
                const expectedTag = tagName.toUpperCase();

                return Array.from(parent.children).find((child) => child.tagName === expectedTag) || null;
            }

            function initResellerPanelScrollUi() {
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
                        window.scrollTo(0, 0);
                    });
                }

                scheduleUpdate();
            }

            function initResellerPanelMobileMenu() {
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
                    button.setAttribute('aria-label', 'Toggle submenu');
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

            function initResellerPanelDropdowns() {
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

            function initResellerPanelFaqs() {
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

            function initResellerPanelNativeShell() {
                initResellerPanelScrollUi();
                initResellerPanelMobileMenu();
                initResellerPanelDropdowns();
                initResellerPanelFaqs();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initResellerPanelNativeShell, { once: true });
            } else {
                initResellerPanelNativeShell();
            }
        })();
    </script>
@endsection
