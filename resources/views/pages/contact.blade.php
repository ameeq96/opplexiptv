@extends('layouts.default')

@php
    $isDocumentEnglish = app()->getLocale() === 'en';
    $documentContact = $isDocumentEnglish ? __('document_support.contact') : [];
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
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}?v={{ @filemtime(public_path('css/contact.css')) ?: 1 }}">
    @if ($isDocumentEnglish)
        <link rel="stylesheet" href="{{ asset('css/document-support.css') }}?v={{ @filemtime(public_path('css/document-support.css')) ?: 1 }}">
    @endif
@endpush

@section('content')
    <!-- Page Title -->
    <x-page-title
        :title="$isDocumentEnglish ? $documentContact['page_title'] : __('messages.contact.heading')"
        :breadcrumbs="$isDocumentEnglish
            ? [['url' => route('home'), 'label' => 'Home'], ['label' => $documentContact['page_title']]]
            : [
                ['url' => '/', 'label' => __('messages.contact.breadcrumb.home')],
                ['label' => __('messages.contact.breadcrumb.current')],
            ]"
        background="images/background/10.webp" :rtl="$isRtl"
        aria-label="Contact Page" />
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
                        <span class="document-support__eyebrow">Contact Channels</span>
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
                                    {{ $isRtl ? '4913-093 (936) 1+' : __('messages.contact.details.phone') }}
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
                                <form method="POST" action="{{ route('contact.send') }}" id="contact-form" novalidate>
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
                                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                                                placeholder="{{ __('messages.contact.form.phone') }}"
                                                class="@if ($isRtl) text-end @endif form-control"
                                                inputmode="tel" dir="ltr"
                                                aria-invalid="@error('phone') true @else false @enderror"
                                                aria-describedby="@error('phone') phone-error @enderror" required>
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
                        <span class="document-support__eyebrow">Common Contact Reasons</span>
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
