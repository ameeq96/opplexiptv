@extends('layouts.default')
@section('title', __('messages.contact.title'))

@section('content')
    <!-- Page Title -->
    <x-page-title :title="__('messages.contact.heading')" :breadcrumbs="[
        ['url' => '/', 'label' => __('messages.contact.breadcrumb.home')],
        ['label' => __('messages.contact.breadcrumb.current')],
    ]" background="images/background/10.webp" :rtl="$isRtl"
        aria-label="Contact Page" />
    <!-- End Page Title -->

    <!-- Contact Page Section -->
    <section class="contact-page-section" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="auto-container">
            <div @class(['row', 'clearfix', $isRtl ? 'rtl-row' : ''])>

                <!-- Info Column -->
                <div class="info-column col-lg-4 col-md-12 col-sm-12" @style(['text-align: right' => $isRtl])>
                    <div class="inner-column">
                        <div class="title-box">
                            <h4>{{ __('messages.contact.details.title') }}</h4>
                        </div>

                        <div class="lower-box">
                            <ul @class(['info-list', $isRtl ? 'text-end' : 'text-start'])>
                                <li class="d-flex align-items-start">
                                    <span class="icon flaticon-map me-2"></span>
                                    <span>{{ __('messages.contact.details.location') }}</span>
                                </li>
                                <li class="d-flex align-items-start">
                                    <span class="icon flaticon-call me-2"></span>
                                    <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_contact')) }}"
                                        target="_blank" rel="noopener">
                                        {{ $isRtl ? '4913-093 (936) 1+' : __('messages.contact.details.phone') }}
                                    </a>
                                </li>
                                <li class="d-flex align-items-start">
                                    <span class="icon flaticon-email-1 me-2"></span>
                                    <a href="mailto:info@opplexiptv.com">info@opplexiptv.com</a>
                                </li>
                            </ul>

                            <div @class(['timing', $isRtl ? 'text-end' : 'text-start'])>
                                {{ __('messages.contact.details.hours') }}
                            </div>

                            <!-- Social Box -->
                            <ul @class([
                                'social-box',
                                'd-flex',
                                $isRtl ? 'justify-content-end flex-row-reverse' : 'justify-content-start',
                            ])>
                                <li class="facebook">
                                    <a href="https://www.facebook.com/profile.php?id=61565476366548"
                                        class="fa fa-facebook-f" target="_blank" rel="noopener"></a>
                                </li>
                                <li class="linkedin">
                                    <a href="https://www.linkedin.com/company/digitalize-store/" class="fa fa-linkedin"
                                        target="_blank" rel="noopener"></a>
                                </li>
                                <li class="instagram">
                                    <a href="https://www.instagram.com/oplextv/" class="fa fa-instagram" target="_blank"
                                        rel="noopener"></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Column -->
                <div class="map-column col-lg-8 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="contact-form-box" aria-labelledby="contact-form-title">
                            <div class="{{ $containerClass }}" @style(['text-align: right' => $isRtl])>
                                <h3 id="contact-form-title">{{ __('messages.contact.form.title') }}</h3>
                            </div>

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
                                                <div class="alert alert-success text-center" role="status">
                                                    {{ session('success') }}
                                                </div>
                                            </div>
                                        @endif
                                        @if (session('error'))
                                            <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                                <div class="alert alert-danger text-center" role="alert">
                                                    {{ session('error') }}
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Submit --}}
                                        <div class="col-lg-12 col-md-12 col-sm-12 form-group text-center">
                                            <button class="theme-btn btn-style-four" type="submit" name="submit-form">
                                                <span class="txt">{{ __('messages.contact.form.submit') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div><!-- contact-form -->
                        </div><!-- contact-form-box -->
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- End Contact Page Section -->
@stop
