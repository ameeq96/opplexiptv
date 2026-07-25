@extends('layouts.default')

@php
    $isDocumentEnglish = true;
    $documentContact = __('document_support.contact');
    $phoneCountries = [
        'PK' => ['dialCode' => '+92', 'minDigits' => 10, 'maxDigits' => 10],
        'CA / US' => ['dialCode' => '+1', 'minDigits' => 10, 'maxDigits' => 10],
        'GB' => ['dialCode' => '+44', 'minDigits' => 10, 'maxDigits' => 10],
        'AE' => ['dialCode' => '+971', 'minDigits' => 9, 'maxDigits' => 9],
        'SA' => ['dialCode' => '+966', 'minDigits' => 9, 'maxDigits' => 9],
        'IN' => ['dialCode' => '+91', 'minDigits' => 10, 'maxDigits' => 10],
        'DE' => ['dialCode' => '+49', 'minDigits' => 9, 'maxDigits' => 13],
        'FR' => ['dialCode' => '+33', 'minDigits' => 9, 'maxDigits' => 9],
        'ES' => ['dialCode' => '+34', 'minDigits' => 9, 'maxDigits' => 9],
        'IT' => ['dialCode' => '+39', 'minDigits' => 10, 'maxDigits' => 13],
        'NL' => ['dialCode' => '+31', 'minDigits' => 9, 'maxDigits' => 9],
        'PT' => ['dialCode' => '+351', 'minDigits' => 9, 'maxDigits' => 9],
        'RU' => ['dialCode' => '+7', 'minDigits' => 10, 'maxDigits' => 10],
        'TR' => ['dialCode' => '+90', 'minDigits' => 10, 'maxDigits' => 10],
        'AU' => ['dialCode' => '+61', 'minDigits' => 9, 'maxDigits' => 9],
        'NZ' => ['dialCode' => '+64', 'minDigits' => 8, 'maxDigits' => 10],
        'ZA' => ['dialCode' => '+27', 'minDigits' => 9, 'maxDigits' => 9],
        'NG' => ['dialCode' => '+234', 'minDigits' => 10, 'maxDigits' => 11],
        'BD' => ['dialCode' => '+880', 'minDigits' => 10, 'maxDigits' => 10],
        'LK' => ['dialCode' => '+94', 'minDigits' => 9, 'maxDigits' => 9],
        'OTHER' => ['dialCode' => '', 'minDigits' => 7, 'maxDigits' => 15],
    ];
@endphp

@section('title', $isDocumentEnglish ? $documentContact['hero']['heading'] : __('messages.contact.title'))

@push('schema')
    {!! jsonld(seo()->contactPage(
        $isDocumentEnglish ? $documentContact['hero']['heading'] : __('messages.contact.heading'),
        $isDocumentEnglish
            ? $documentContact['hero']['text']
            : 'Contact Opplex IPTV for free trial, setup help, reseller information and 24/7 support.',
        route('contact'),
    )) !!}
@endpush

@push('styles')
    @php
        $contactPageCss = @file_get_contents(public_path('css/contact.css'));
        $contactDocumentCss = $isDocumentEnglish
            ? @file_get_contents(public_path('css/document-support.css'))
            : false;
    @endphp
    @if ($contactPageCss !== false)
        <style id="contact-page-styles">{!! $contactPageCss !!}</style>
    @else
        <link rel="stylesheet" href="{{ asset('css/contact.css') }}?v={{ @filemtime(public_path('css/contact.css')) ?: 1 }}">
    @endif
    @if ($isDocumentEnglish)
        @if ($contactDocumentCss !== false)
            <style id="contact-document-styles">{!! $contactDocumentCss !!}</style>
        @else
            <link rel="stylesheet" href="{{ asset('css/document-support.css') }}?v={{ @filemtime(public_path('css/document-support.css')) ?: 1 }}">
        @endif
    @endif
@endpush

@section('content')
    <!-- Page Title -->
    <x-page-title
        :title="$isDocumentEnglish ? $documentContact['page_title'] : __('messages.contact.heading')"
        :breadcrumbs="$isDocumentEnglish
            ? [['url' => route('home'), 'label' => __('document_ui.shared.home')], ['label' => $documentContact['page_title']]]
            : [
                ['url' => '/', 'label' => __('messages.contact.breadcrumb.home')],
                ['label' => __('messages.contact.breadcrumb.current')],
            ]"
        background="images/background/10.webp" :rtl="$isRtl"
        aria-label="{{ __('document_ui.contact.page_aria') }}" />
    <!-- End Page Title -->

    @if ($isDocumentEnglish)
        <div class="document-support document-support--contact">
            <section class="document-support__hero document-support__hero--compact" aria-labelledby="document-contact-title">
                <div class="auto-container document-support__hero-inner">
                    <span class="document-support__eyebrow">{{ $documentContact['hero']['eyebrow'] }}</span>
                    <h1 id="document-contact-title">{{ $documentContact['hero']['heading'] }}</h1>
                    <p>{{ $documentContact['hero']['text'] }}</p>
                </div>
            </section>

            <section class="document-support__section document-support__section--tint" aria-labelledby="document-contact-channels-title">
                <div class="auto-container">
                    <div class="document-support__section-heading">
                        <span class="document-support__eyebrow">{{ __('document_ui.contact.channels_eyebrow') }}</span>
                        <h2 id="document-contact-channels-title">{{ $documentContact['channels']['heading'] }}</h2>
                    </div>
                    <div class="document-support__card-grid document-support__card-grid--three">
                        @foreach ($documentContact['channels']['items'] as $channel)
                            @php
                                $channelUrl = match ($channel['type']) {
                                    'whatsapp' => 'https://wa.me/16393903194?text=' . urlencode(__('document_support.whatsapp_messages.support')),
                                    'email' => 'mailto:info@opplexiptv.com',
                                    default => '#contact-form',
                                };
                                $opensNewTab = $channel['type'] === 'whatsapp';
                            @endphp
                            <article class="document-support__card document-support__contact-card">
                                <span class="document-support__card-icon {{ $channel['icon'] }}" aria-hidden="true"></span>
                                <h3>{{ $channel['title'] }}</h3>
                                <p>{{ $channel['text'] }}</p>
                                <a href="{{ $channelUrl }}" @if ($opensNewTab) target="_blank" rel="noopener" @endif>
                                    {{ $channel['cta'] }}
                                    <span class="fa fa-arrow-right" aria-hidden="true"></span>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    @endif

    <!-- Contact Page Section -->
    <section class="ctx {{ $isRtl ? 'rtl' : '' }}" id="contact-details-form" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="auto-container">

            <div class="ctx__head">
                <div class="ctx__bar" aria-hidden="true"></div>
                @if ($isDocumentEnglish)
                    <h2 class="ctx__title">{{ __('messages.contact.heading') }}</h2>
                @else
                    <h1 class="ctx__title">{{ __('messages.contact.heading') }}</h1>
                @endif
            </div>

            <div class="ctx-grid">

                <!-- Info panel -->
                <aside class="ctx-info">
                    <h2 class="ctx-info__title">{{ __('messages.contact.details.title') }}</h2>

                    <ul class="ctx-methods">
                        <li class="ctx-method">
                            <span class="ctx-method__icon icon flaticon-map" aria-hidden="true"></span>
                            <span class="ctx-method__val">{{ __('messages.contact.details.location') }}</span>
                        </li>
                        <li class="ctx-method">
                            <span class="ctx-method__icon icon flaticon-call" aria-hidden="true"></span>
                            <span class="ctx-method__val">
                                <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_contact')) }}"
                                    target="_blank" rel="noopener">
                                    <bdi>{{ __('messages.contact.details.phone') }}</bdi>
                                </a>
                            </span>
                        </li>
                        <li class="ctx-method">
                            <span class="ctx-method__icon icon flaticon-email-1" aria-hidden="true"></span>
                            <span class="ctx-method__val"><a href="mailto:info@opplexiptv.com">info@opplexiptv.com</a></span>
                        </li>
                    </ul>

                    <div class="ctx-hours">{{ __('messages.contact.details.hours') }}</div>

                    <ul class="ctx-social">
                        <li><a href="https://www.facebook.com/profile.php?id=61565476366548"
                                class="fa fa-facebook-f" target="_blank" rel="noopener"
                                aria-label="Facebook" title="Facebook"></a></li>
                        <li><a href="https://www.linkedin.com/company/digitalize-store/" class="fa fa-linkedin"
                                target="_blank" rel="noopener" aria-label="LinkedIn" title="LinkedIn"></a></li>
                        <li><a href="https://www.instagram.com/oplextv/" class="fa fa-instagram" target="_blank"
                                rel="noopener" aria-label="Instagram" title="Instagram"></a></li>
                    </ul>
                </aside>

                <!-- Form card -->
                <div class="ctx-form" aria-labelledby="contact-form-title">
                    <h3 id="contact-form-title" class="ctx-form__title">{{ __('messages.contact.form.title') }}</h3>

                    <div class="contact-form">
                                <form method="POST" action="{{ route('contact.send') }}" id="contact-form">
                                    @csrf
                                    <div class="row clearfix">
                                        {{-- Name --}}
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="text" name="username" value="{{ old('username') }}"
                                                placeholder="{{ __('messages.contact.form.name') }}" required
                                                @class([$isRtl ? 'text-end' : ''])
                                                aria-invalid="@error('username') true @else false @enderror"
                                                aria-describedby="@error('username') username-error @enderror">
                                            @error('username')
                                                <small id="username-error"
                                                    class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Email --}}
                                        <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                            <input type="email" name="email" value="{{ old('email') }}"
                                                placeholder="{{ __('messages.contact.form.email') }}" required
                                                @class([$isRtl ? 'text-end' : ''])
                                                aria-invalid="@error('email') true @else false @enderror"
                                                aria-describedby="@error('email') email-error @enderror">
                                            @error('email')
                                                <small id="email-error" class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Phone --}}
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <div class="ctx-phone">
                                                <label class="sr-only" for="phone-country-code">{{ __('document_ui.contact.country_code') }}</label>
                                                <select id="phone-country-code" class="ctx-phone__country"
                                                    aria-label="{{ __('document_ui.contact.country_code') }}" dir="ltr">
                                                    @foreach ($phoneCountries as $country => $phoneCountry)
                                                        <option value="{{ $phoneCountry['dialCode'] }}"
                                                            data-min-digits="{{ $phoneCountry['minDigits'] }}"
                                                            data-max-digits="{{ $phoneCountry['maxDigits'] }}"
                                                            @if ($country === 'IT') data-preserve-leading-zero="true" @endif
                                                            @selected($country === 'PK')>
                                                            {{ $country === 'OTHER' ? __('document_ui.contact.other_country') : $country }} {{ $phoneCountry['dialCode'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                                                    placeholder="{{ __('messages.contact.form.phone') }}"
                                                    class="@if ($isRtl) text-end @endif form-control"
                                                    inputmode="tel" autocomplete="tel" dir="ltr"
                                                    pattern="[+0-9() .-]{7,25}" minlength="7" maxlength="25"
                                                    aria-invalid="@error('phone') true @else false @enderror"
                                                    aria-describedby="phone-format-hint phone-client-error @error('phone') phone-error @enderror"
                                                    required>
                                            </div>
                                            <small id="phone-format-hint" class="ctx-phone__hint">
                                                {{ __('document_ui.contact.other_country_hint') }}
                                            </small>
                                            <small id="phone-client-error" class="text-danger d-none"></small>
                                            @error('phone')
                                                <small id="phone-error" class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Captcha --}}
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <input type="text" name="captcha"
                                                placeholder="{{ __('messages.contact.form.captcha', ['num1' => $num1, 'num2' => $num2]) }}"
                                                required @class([$isRtl ? 'text-end' : ''])
                                                aria-invalid="@error('captcha') true @else false @enderror"
                                                aria-describedby="@error('captcha') captcha-error @enderror">
                                            @error('captcha')
                                                <small id="captcha-error"
                                                    class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Message --}}
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <textarea class="darma @if ($isRtl) text-end @endif" name="message"
                                                placeholder="{{ __('messages.contact.form.message') }}" required
                                                aria-invalid="@error('message') true @else false @enderror"
                                                aria-describedby="@error('message') message-error @enderror">{{ old('message') }}</textarea>
                                            @error('message')
                                                <small id="message-error"
                                                    class="text-danger d-block">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        {{-- Flash messages --}}
                                        @if (session('success'))
                                            <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                                <div class="alert alert-success" role="status">
                                                    {{ session('success') }}
                                                </div>
                                            </div>
                                        @endif
                                        @if (session('error'))
                                            <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                                <div class="alert alert-danger" role="alert">
                                                    {{ session('error') }}
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Submit --}}
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                            <button class="ctx-submit" type="submit" name="submit-form">
                                                {{ __('messages.contact.form.submit') }}
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div><!-- contact-form -->
                        </div><!-- ctx-form -->

                    </div><!-- ctx-grid -->
        </div><!-- auto-container -->
    </section>
    <!-- End Contact Page Section -->

    @if ($isDocumentEnglish)
        <div class="document-support document-support--contact">
            <section class="document-support__section document-support__section--tint" aria-labelledby="document-contact-reasons-title">
                <div class="auto-container">
                    <div class="document-support__section-heading">
                        <span class="document-support__eyebrow">{{ __('document_ui.contact.reasons_eyebrow') }}</span>
                        <h2 id="document-contact-reasons-title">{{ $documentContact['reasons']['heading'] }}</h2>
                    </div>
                    <div class="document-support__reason-grid">
                        @foreach ($documentContact['reasons']['items'] as $reason)
                            <article class="document-support__reason">
                                <span class="document-support__reason-icon {{ $reason['icon'] }}" aria-hidden="true"></span>
                                <div>
                                    <h3>{{ $reason['title'] }}</h3>
                                    <p>{{ $reason['text'] }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="document-support__section document-support__section--dark" aria-labelledby="document-contact-response-title">
                <div class="auto-container">
                    <div class="document-support__section-heading">
                        <h2 id="document-contact-response-title">{{ $documentContact['response']['heading'] }}</h2>
                    </div>
                    <div class="document-support__response-grid">
                        @foreach ($documentContact['response']['items'] as $response)
                            <article>
                                <strong>{{ $response['value'] }}</strong>
                                <span>{{ $response['label'] }}</span>
                            </article>
                        @endforeach
                    </div>
                    <p class="document-support__response-note">{{ $documentContact['response']['text'] }}</p>
                </div>
            </section>

            @include('includes._faq-section', [
                'faqItems' => $documentContact['faq']['items'],
                'faqTitle' => $documentContact['faq']['heading'],
            ])

            <section class="document-support__cta" aria-labelledby="document-contact-cta-title">
                <div class="auto-container document-support__cta-inner">
                    <div>
                        <h2 id="document-contact-cta-title">{{ $documentContact['cta']['heading'] }}</h2>
                        <p>{{ $documentContact['cta']['text'] }}</p>
                    </div>
                    <div class="document-support__actions">
                        <a class="document-support__button document-support__button--light"
                            href="https://wa.me/16393903194?text={{ urlencode(__('document_support.whatsapp_messages.support')) }}"
                            target="_blank" rel="noopener">
                            {{ $documentContact['cta']['whatsapp'] }}
                        </a>
                        <a class="document-support__button document-support__button--outline-light" href="mailto:info@opplexiptv.com">
                            {{ $documentContact['cta']['email'] }}
                        </a>
                    </div>
                </div>
            </section>
        </div>
    @else
        {{-- FAQ Section --}}
        @include('includes._faq-section')
    @endif
@stop

@section('script')
    <script id="contact-native-shell">
        (function () {
            'use strict';

            function directChildByTag(parent, tagName) {
                if (!parent) return null;
                const expectedTag = tagName.toUpperCase();

                return Array.from(parent.children).find((child) => child.tagName === expectedTag) || null;
            }

            function initContactScrollUi() {
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

            function initContactMobileMenu() {
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

            function initContactDropdowns() {
                const dropdowns = document.querySelectorAll('.main-header .main-menu .navigation > li.dropdown');

                const closeDropdowns = (except) => {
                    dropdowns.forEach((item) => {
                        if (item === except) return;
                        item.classList.remove('native-dropdown-open');
                        const submenu = directChildByTag(item, 'ul');
                        if (submenu) {
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

            function initContactFaqs() {
                document.querySelectorAll('.accordion-box').forEach((accordion) => {
                    const buttons = accordion.querySelectorAll('.acc-btn');

                    buttons.forEach((button) => {
                        const item = button.closest('.accordion');
                        const content = button.nextElementSibling;
                        if (!item || !content) return;

                        button.tabIndex = 0;
                        content.hidden = !content.classList.contains('current');

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

            function initContactPhone() {
                const form = document.getElementById('contact-form');
                const input = document.getElementById('phone');
                const country = document.getElementById('phone-country-code');
                const error = document.getElementById('phone-client-error');
                const invalidMessage = @json(__('document_ui.contact.invalid_phone'));

                if (!form || !input || !country || !error) return;

                const dialOptions = Array.from(country.options)
                    .filter((option) => option.value)
                    .sort((left, right) => {
                        return right.value.replace(/\D/g, '').length - left.value.replace(/\D/g, '').length;
                    });

                const setError = (message) => {
                    input.setCustomValidity(message);
                    input.setAttribute('aria-invalid', message ? 'true' : 'false');
                    error.textContent = message;
                    error.classList.toggle('d-none', !message);
                };

                const normalizeContactPhone = () => {
                    const rawValue = input.value.trim();
                    if (!rawValue) return '';

                    if (rawValue.startsWith('+')) {
                        return `+${rawValue.replace(/\D/g, '')}`;
                    }

                    if (rawValue.startsWith('00')) {
                        return `+${rawValue.slice(2).replace(/\D/g, '')}`;
                    }

                    const dialCode = country.value.replace(/\D/g, '');
                    const selectedCountry = country.options[country.selectedIndex];
                    const nationalDigits = rawValue.replace(/\D/g, '');
                    const nationalNumber = selectedCountry?.dataset.preserveLeadingZero === 'true'
                        ? nationalDigits
                        : nationalDigits.replace(/^0+/, '');

                    return dialCode && nationalNumber ? `+${dialCode}${nationalNumber}` : '';
                };

                const findDialOption = (normalized) => {
                    const digits = normalized.replace(/\D/g, '');

                    return dialOptions.find((option) => {
                        const dialCode = option.value.replace(/\D/g, '');
                        return digits.startsWith(dialCode) && digits.length > dialCode.length;
                    }) || null;
                };

                const hasValidCountryLength = (normalized) => {
                    const rawValue = input.value.trim();
                    const isInternational = rawValue.startsWith('+') || rawValue.startsWith('00');
                    const selectedCountry = isInternational
                        ? findDialOption(normalized)
                        : country.options[country.selectedIndex];

                    if (!selectedCountry) return true;

                    const dialCode = selectedCountry.value.replace(/\D/g, '');
                    if (!dialCode) return false;

                    const normalizedDigits = normalized.replace(/\D/g, '');
                    const nationalLength = normalizedDigits.slice(dialCode.length).length;
                    const minDigits = Number.parseInt(selectedCountry.dataset.minDigits, 10);
                    const maxDigits = Number.parseInt(selectedCountry.dataset.maxDigits, 10);

                    return nationalLength >= minDigits && nationalLength <= maxDigits;
                };

                const validateContactPhone = () => {
                    if (!input.value.trim()) {
                        setError('');
                        return '';
                    }

                    const normalized = normalizeContactPhone();
                    if (!/^\+[1-9]\d{6,14}$/.test(normalized) || !hasValidCountryLength(normalized)) {
                        setError(invalidMessage);
                        return '';
                    }

                    setError('');
                    return normalized;
                };

                const restoreInternationalValue = () => {
                    const rawValue = input.value.trim();
                    if (!rawValue.startsWith('+')) return;

                    const digits = rawValue.replace(/\D/g, '');
                    const match = findDialOption(rawValue);

                    if (!match) {
                        country.value = '';
                        return;
                    }

                    const dialCode = match.value.replace(/\D/g, '');
                    country.value = match.value;
                    input.value = digits.slice(dialCode.length);
                };

                restoreInternationalValue();

                input.addEventListener('input', () => setError(''));
                country.addEventListener('change', () => setError(''));
                input.addEventListener('blur', validateContactPhone);

                form.addEventListener('submit', (event) => {
                    const normalized = validateContactPhone();
                    if (!normalized) {
                        event.preventDefault();
                        input.reportValidity();
                        input.focus();
                        return;
                    }

                    input.value = normalized;
                });
            }

            function initContactNativeShell() {
                initContactScrollUi();
                initContactMobileMenu();
                initContactDropdowns();
                initContactFaqs();
                initContactPhone();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initContactNativeShell, { once: true });
            } else {
                initContactNativeShell();
            }
        })();
    </script>
@endsection
