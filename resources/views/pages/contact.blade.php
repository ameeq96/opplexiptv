@extends('layouts.default')
@section('title', __('messages.contact.title'))

@push('schema')
    {!! jsonld(seo()->contactPage(
        __('messages.contact.heading'),
        'Contact Opplex IPTV for free trial, setup help, reseller information and 24/7 support.',
        route('contact'),
    )) !!}
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}?v={{ @filemtime(public_path('css/contact.css')) ?: 1 }}">
@endpush

@section('content')
    <!-- Page Title -->
    <x-page-title :title="__('messages.contact.heading')" :breadcrumbs="[
        ['url' => '/', 'label' => __('messages.contact.breadcrumb.home')],
        ['label' => __('messages.contact.breadcrumb.current')],
    ]" background="images/background/10.webp" :rtl="$isRtl"
        aria-label="Contact Page" />
    <!-- End Page Title -->

    <!-- Contact Page Section -->
    <section class="ctx {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="auto-container">

            <div class="ctx__head">
                <div class="ctx__bar" aria-hidden="true"></div>
                <h1 class="ctx__title">{{ __('messages.contact.heading') }}</h1>
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

    {{-- FAQ Section --}}
    @include('includes._faq-section')
@stop
