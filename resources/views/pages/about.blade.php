@extends('layouts.default')

@php
    $isDocumentEnglish = app()->getLocale() === 'en';
    $documentAbout = $isDocumentEnglish ? __('document_support.about') : [];
@endphp

@section('title', $isDocumentEnglish ? $documentAbout['page_title'] : __('messages.about.title'))

@push('schema')
    {!! jsonld(seo()->aboutPage(
        $isDocumentEnglish ? $documentAbout['hero']['heading'] : __('messages.about.title'),
        $isDocumentEnglish
            ? $documentAbout['hero']['paragraphs'][1]
            : 'Learn about Opplex IPTV — a premium IPTV provider offering 4K live streaming, 12,000+ channels, a free trial and 24/7 support worldwide.',
        route('about'),
    )) !!}
@endpush

@push('styles')
    @php
        $aboutPageCss = $isDocumentEnglish
            ? false
            : @file_get_contents(public_path('css/about.css'));
        $aboutDocumentCss = $isDocumentEnglish
            ? @file_get_contents(public_path('css/document-support.css'))
            : false;
    @endphp
    @if ($isDocumentEnglish)
        @if ($aboutDocumentCss !== false)
            <style id="about-document-styles">{!! $aboutDocumentCss !!}</style>
        @else
            <link rel="stylesheet" href="{{ asset('css/document-support.css') }}?v={{ @filemtime(public_path('css/document-support.css')) ?: 1 }}">
        @endif
    @elseif ($aboutPageCss !== false)
        <style id="about-page-styles">{!! $aboutPageCss !!}</style>
    @else
        <link rel="stylesheet" href="{{ asset('css/about.css') }}?v={{ @filemtime(public_path('css/about.css')) ?: 1 }}">
    @endif
@endpush

@section('content')
    <x-page-title
        :title="$isDocumentEnglish ? $documentAbout['page_title'] : __('messages.about.title_short')"
        :breadcrumbs="$isDocumentEnglish
            ? [['url' => route('home'), 'label' => 'Home'], ['label' => $documentAbout['page_title']]]
            : [['url' => '/', 'label' => __('messages.nav.home')], ['label' => __('messages.nav.about_us')]]"
        background="images/background/7.webp"
        :rtl="$isRtl"
        aria-label="About Us Page" />

    @if ($isDocumentEnglish)
        <main class="document-support document-support--about">
            <section class="document-support__hero" aria-labelledby="document-about-title">
                <div class="auto-container document-support__hero-inner">
                    <span class="document-support__eyebrow">{{ $documentAbout['hero']['eyebrow'] }}</span>
                    <h1 id="document-about-title">{{ $documentAbout['hero']['heading'] }}</h1>
                    <div class="document-support__hero-copy">
                        @foreach ($documentAbout['hero']['paragraphs'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                    <div class="document-support__actions">
                        <a class="document-support__button" href="{{ route('packages', ['direct' => 1]) }}">
                            {{ $documentAbout['hero']['plans_cta'] }}
                        </a>
                        <a class="document-support__button document-support__button--outline"
                            href="https://wa.me/16393903194?text={{ urlencode(__('document_support.whatsapp_messages.trial')) }}"
                            target="_blank" rel="noopener">
                            {{ $documentAbout['hero']['trial_cta'] }}
                        </a>
                    </div>
                </div>
            </section>

            <section class="document-support__stats" aria-label="Opplex IPTV service statistics">
                <div class="auto-container document-support__stats-grid">
                    @foreach ($documentAbout['stats'] as $stat)
                        <article class="document-support__stat">
                            <span class="document-support__stat-icon {{ $stat['icon'] }}" aria-hidden="true"></span>
                            <strong>{{ $stat['value'] }}</strong>
                            <span>{{ $stat['label'] }}</span>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="document-support__section" aria-labelledby="document-about-story-title">
                <div class="auto-container document-support__split">
                    <div class="document-support__copy">
                        <span class="document-support__eyebrow">Our Story</span>
                        <h2 id="document-about-story-title">{{ $documentAbout['story']['heading'] }}</h2>
                        @foreach ($documentAbout['story']['paragraphs'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                    <div class="document-support__network-art">
                        <img class="document-support__network-main"
                            src="{{ asset('images/resource/network-4.webp') }}"
                            alt="{{ $documentAbout['story']['image_alt'] }}"
                            loading="lazy" decoding="async">
                        <img class="document-support__network-screen"
                            src="{{ asset('images/resource/network-5.webp') }}"
                            alt=""
                            aria-hidden="true"
                            loading="lazy" decoding="async">
                    </div>
                </div>
            </section>

            <section class="document-support__section document-support__section--tint" aria-labelledby="document-about-values-title">
                <div class="auto-container">
                    <div class="document-support__section-heading">
                        <span class="document-support__eyebrow">What We Stand For</span>
                        <h2 id="document-about-values-title">{{ $documentAbout['values']['heading'] }}</h2>
                    </div>
                    <div class="document-support__card-grid document-support__card-grid--three">
                        @foreach ($documentAbout['values']['items'] as $item)
                            <article class="document-support__card">
                                <span class="document-support__card-icon {{ $item['icon'] }}" aria-hidden="true"></span>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['text'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="document-support__section" aria-labelledby="document-about-regions-title">
                <div class="auto-container">
                    <div class="document-support__section-heading">
                        <span class="document-support__eyebrow">Where We Serve</span>
                        <h2 id="document-about-regions-title">{{ $documentAbout['regions']['heading'] }}</h2>
                        <p>{{ $documentAbout['regions']['intro'] }}</p>
                    </div>
                    <ul class="document-support__region-grid">
                        @foreach ($documentAbout['regions']['items'] as $region)
                            <li>
                                <span class="fa fa-map-marker" aria-hidden="true"></span>
                                <span>{{ $region }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="document-support__outro">{{ $documentAbout['regions']['outro'] }}</p>
                </div>
            </section>

            <section class="document-support__section document-support__section--dark" aria-labelledby="document-about-team-title">
                <div class="auto-container document-support__narrow">
                    <span class="document-support__eyebrow">The Team</span>
                    <h2 id="document-about-team-title">{{ $documentAbout['team']['heading'] }}</h2>
                    @foreach ($documentAbout['team']['paragraphs'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            </section>

            <section class="document-support__section" aria-labelledby="document-about-timeline-title">
                <div class="auto-container">
                    <div class="document-support__section-heading">
                        <span class="document-support__eyebrow">Milestones</span>
                        <h2 id="document-about-timeline-title">{{ $documentAbout['timeline']['heading'] }}</h2>
                    </div>
                    <ol class="document-support__timeline">
                        @foreach ($documentAbout['timeline']['items'] as $milestone)
                            <li>
                                <time datetime="{{ $milestone['year'] }}">{{ $milestone['year'] }}</time>
                                <p>{{ $milestone['text'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            @include('includes._faq-section', [
                'faqItems' => $documentAbout['faq']['items'],
                'faqTitle' => $documentAbout['faq']['heading'],
            ])
        </main>
    @else
        @include('includes._we-provide-unlimited')

        <section class="abtx {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" aria-labelledby="choose-us-title">
            <div class="auto-container">
                <div class="abtx__head">
                    <div class="abtx__titles">
                        <div class="abtx__bar" aria-hidden="true"></div>
                        <h1 id="choose-us-title" class="abtx__title">{!! __('messages.choose_us.title') !!}</h1>
                    </div>

                    <a href="{{ route('packages', ['direct' => 1]) }}" class="abtx__cta">
                        {{ __('messages.choose_us.button') }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>

                <div class="abtx-grid" role="list">
                    @foreach ($features as $feature)
                        <a class="abtx-card" role="listitem" href="{{ $feature['link'] }}"
                            aria-label="{{ __('Read more about :title', ['title' => $feature['title']]) }}">
                            <span class="abtx-card__icon {{ $feature['icon'] }}" aria-hidden="true"></span>
                            <h3 class="abtx-card__title">{{ $feature['title'] }}</h3>
                            <p class="abtx-card__text">{{ $feature['description'] }}</p>
                            <span class="abtx-card__arrow" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                    stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        @include('includes._testimonials')

        @unless ($isMobile)
            @include('includes._channels-carousel', ['useNativeCarousel' => true])
        @endunless

        @include('includes._faq-section')
        @include('includes._check-trail')
    @endif
@stop

@section('script')
    <script id="about-native-shell">
        (function () {
            'use strict';

            function directChildByTag(parent, tagName) {
                if (!parent) return null;
                const expectedTag = tagName.toUpperCase();

                return Array.from(parent.children).find((child) => child.tagName === expectedTag) || null;
            }

            function initAboutScrollUi() {
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

            function initAboutMobileMenu() {
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

            function initAboutDropdowns() {
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

            function initAboutFaqs() {
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

            function initAboutNativeShell() {
                initAboutScrollUi();
                initAboutMobileMenu();
                initAboutDropdowns();
                initAboutFaqs();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAboutNativeShell, { once: true });
            } else {
                initAboutNativeShell();
            }
        })();
    </script>
@endsection
